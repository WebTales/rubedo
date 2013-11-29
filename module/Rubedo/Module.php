<?php
/**
 * Rubedo -- ECM solution Copyright (c) 2013, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Session\SaveHandler\MongoDB;
use Zend\Session\SaveHandler\MongoDBOptions;
use Rubedo\Services\Manager;
use Rubedo\Services\Events;
use Rubedo\Collection\AbstractLocalizableCollection;
use Rubedo\Exceptions\JsonExceptionStrategy;
use Rubedo\Exceptions\Access as AccessException;
use Rubedo\Collection\SessionData;
use Rubedo\Router\Url;
use Rubedo\Security\HtmlCleaner;
use Zend\EventManager\EventManager;
use Rubedo\Cache\MongoCache;
use Rubedo\Collection\AbstractCollection;
use Rubedo\Collection\WorkflowAbstractCollection;
use Rubedo\User\Authentication;
use Rubedo\Services\Cache;
use Zend\Http\Response;

/**
 * Loading class for the Rubedo Main module
 *
 * Handle initialization, configuration and events
 * 
 * @author jbourdin
 *        
 */
class Module
{

    /**
     * Do we use cache for web page
     * 
     * @var boolean
     */
    protected static $cachePageIsActive = true;

    /**
     * Did the cache for current page had been found
     * 
     * @var unknown
     */
    protected $pageHit = false;

    /**
     * Initialize Services, session, listeners for Rubedo
     *
     * @param MvcEvent $e            
     */
    public function onBootstrap(MvcEvent $e)
    {
        // register serviceLocator for global access by Rubedo
        Manager::setServiceLocator($e->getApplication()->getServiceManager());
        
        // register eventManager for global access by Rubedo
        $eventManager = $e->getApplication()->getEventManager();
        Events::setEventManager($eventManager);
        
        $message = 'Running Rubedo for ' . $e->getRequest()->getUri();
        Manager::getService('Logger')->info($message);
        
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $application = $e->getApplication();
        $config = $application->getConfig();
        
        SessionData::setSessionName($config['session']['name']);
        
        $this->initializeSession($e);
        
        Interfaces\config::initInterfaces();
        
        // define all the events that should be handled
        $this->setListeners($eventManager);
        
        // Config json enabled exceptionStrategy
        $exceptionStrategy = new JsonExceptionStrategy();
        
        $displayExceptions = $config['view_manager']['display_exceptions'];
        
        $exceptionStrategy->setDisplayExceptions($displayExceptions);
        $exceptionStrategy->attach($application->getEventManager());
    }

    /**
     * Define needed event listeners
     *
     * @param EventManager $eventManager            
     */
    public function setListeners(EventManager $eventManager)
    {
        // verify session and access right when dispatching
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array(
            $this,
            'preRouting'
        ));
        
        // add page cache (GET only, based onUser)
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array(
            $this,
            'preDispatch'
        ), 100);
        
        $eventManager->attach(MvcEvent::EVENT_FINISH, array(
            $this,
            'postDispatch'
        ), - 100);
        
        // handle URL caching
        $urlCacheService = Manager::getService('UrlCache');
        $eventManager->attach(Url::URL_TO_PAGE_READ_CACHE_PRE, array(
            $urlCacheService,
            'urlToPageReadCacheEvent'
        ), - 100);
        
        $urlCacheService = Manager::getService('UrlCache');
        $eventManager->attach(Url::PAGE_TO_URL_READ_CACHE_PRE, array(
            $urlCacheService,
            'PageToUrlReadCacheEvent'
        ), - 100);
        
        $eventManager->attach(array(
            Url::URL_TO_PAGE_READ_CACHE_POST,
            Url::PAGE_TO_URL_READ_CACHE_POST
        ), array(
            $urlCacheService,
            'urlToPageWriteCacheEvent'
        ), 100);
        
        // add some cache on HtmlCleaner method
        $eventManager->attach(HtmlCleaner::PRE_HTMLCLEANER, array(
            'Rubedo\Services\Cache',
            'getFromCache'
        ), 100);
        
        $eventManager->attach(HtmlCleaner::POST_HTMLCLEANER, array(
            'Rubedo\Services\Cache',
            'setToCache'
        ), 100);
        
        // log hit & miss on Rubedo cache
        $eventManager->attach(array(
            MongoCache::CACHE_HIT,
            MongoCache::CACHE_MISS
        ), array(
            'Rubedo\Services\Cache',
            'logCacheHit'
        ), 1);
        
        // log Rubedo writing on MongoDB collections
        $eventManager->attach(array(
            AbstractCollection::POST_CREATE_COLLECTION,
            AbstractCollection::POST_UPDATE_COLLECTION,
            AbstractCollection::POST_DELETE_COLLECTION,
            WorkflowAbstractCollection::POST_PUBLISH_COLLECTION
        ), array(
            Manager::getService('ApplicationLogger'),
            'logCollectionEvent'
        ), 1);

        //@todo: fix issue with login
//        $eventManager->attach(WorkflowAbstractCollection::POST_PUBLISH_COLLECTION, array(
//           Manager::getService('Contents'),
//            'indexPublishEvent'
//        ));

        // log authentication attemps
        $eventManager->attach(array(
            Authentication::FAIL,
            Authentication::SUCCESS
        ), array(
            Manager::getService('ApplicationLogger'),
            'logAuthenticationEvent'
        ), 1);

        $eventManager->attach(array(
            Authentication::FAIL
        ), array(
            Manager::getService('SecurityLogger'),
            'logAuthenticationEvent'
        ), 10);
    }

    /**
     * Return main module configuration
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Standard autoloader configuration
     * 
     * @return multitype:multitype:multitype:string
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }

    /**
     * Set context before dispatch
     *
     * Session, User, Rights, Language
     *
     * @param MvcEvent $event            
     * @throws \Rubedo\Exceptions\Access
     */
    public function preRouting(MvcEvent $event)
    {
        $controller = $event->getRouteMatch()->getParam('controller');
        $action = $event->getRouteMatch()->getParam('action');
        
        $router = $event->getRouter();
        $matches = $event->getRouteMatch();
        
        // store this route in URL service
        Url::setRouter($router);
        Url::setRouteName($matches->getMatchedRouteName());
        
        // prevent normal session if checking for session remaining lifetime
        if ($controller == 'Rubedo\\Backoffice\\Controller\\XhrAuthentication' && $action == 'is-session-expiring') {
            return;
        }
        
        $config = $event->getApplication()
            ->getServiceManager()
            ->get('Config');
        
        if (! isset($config['installed']) || ($config['installed']['status'] !== 'finished' && $controller !== 'Rubedo\Install\Controller\Index')) {
            $routeMatches = $event->getRouteMatch();
            $routeMatches->setParam('controller', 'Rubedo\Install\Controller\Index');
            $routeMatches->setParam('action', 'index');
        }
        
        // init the session params and session itself
        $this->startSession();
        
        // @todo forward if no language initialized
        
        // check access
        
        if ($controller) {
            
            list ($applicationName, $moduleName, $constant, $controllerName) = explode('\\', $controller);
            $controllerName = strtolower($controllerName);
            $moduleName = strtolower($moduleName);
            $ressourceName = 'execute.controller.' . $controllerName . '.' . $action . '.' . $moduleName;
            if ($moduleName == 'install' || $moduleName == 'frontoffice') {
                $hasAccess = true;
            } else {
                $aclService = Manager::getService('Acl');
                $hasAccess = $aclService->hasAccess($ressourceName);
            }
            
            if (! $hasAccess) {
                $this->toDeadEnd($event, new AccessException('Can\'t access %1$s', "Exception30", $ressourceName));
            }
            
            // check BO Token
            $isBackoffice = strpos($controller, 'Rubedo\\Backoffice\\Controller') === 0;
            $doNotCheckTokenControllers = array(
                'Rubedo\\Backoffice\\Controller\\Acl',
                'Rubedo\\Backoffice\\Controller\\XhrAuthentication',
                'Rubedo\\Backoffice\\Controller\\Logout'
            );
            if ($isBackoffice && $event->getRequest()->isPost() && ! in_array($controller, $doNotCheckTokenControllers)) {
                $user = Manager::getService('Session')->get('user');
                $token = $event->getRequest()->getPost('token');
                if (! isset($token)) {
                    $token = $event->getRequest()->getQuery('token');
                }
                
                if ($token !== $user['token']) {
                    $this->toDeadEnd($event, new AccessException("The token given in the request doesn't match with the token in session", "Exception6"));
                }
            }
            
            if ($isBackoffice) {
                // initialize localization for collections
                $serviceLanguages = Manager::getService('Languages');
                if ($serviceLanguages->isActivated()) {
                    $workingLanguage = $event->getRequest()->getPost('workingLanguage', false);
                    if (! $workingLanguage) {
                        $workingLanguage = $event->getRequest()->getQuery('workingLanguage', null);
                    }
                    if ($workingLanguage && $serviceLanguages->isActive($workingLanguage)) {
                        AbstractLocalizableCollection::setWorkingLocale($workingLanguage);
                    } else {
                        AbstractLocalizableCollection::setWorkingLocale($serviceLanguages->getDefaultLanguage());
                    }
                }
            }
        }
    }

    /**
     * Log dispatching and check for cached page
     *
     * @param MvcEvent $event            
     * @return void Response
     */
    public function preDispatch(MvcEvent $event)
    {
        if ($event->getRouteMatch()) {
            $controller = $event->getRouteMatch()->getParam('controller');
        }
        $message = 'routing to ' . $controller;
        Manager::getService('Logger')->debug($message);
        if (self::$cachePageIsActive && $controller == 'Rubedo\Frontoffice\Controller\Index' && $event->getRequest()->isGet()) {
            $cache = Cache::getCache();
            $uri = $event->getRequest()->getUri();
            $key = 'page_response_' . md5($uri->getHost() . $uri->getPath() . $uri->getQuery());
            $user = Manager::getService('CurrentUser')->getCurrentUser();
            if ($user) {
                return;
            }
            $loaded = false;
            $content = $cache->getItem($key, $loaded);
            if ($loaded) {
                $this->pageHit = true;
                $event->stopPropagation();
                $response = $event->getResponse();
                $response->setContent($content);
                return $response;
            }
        }
    }

    /**
     * Add post dispatching logging and cache writing
     *
     * @param MvcEvent $event            
     */
    public function postDispatch(MvcEvent $event)
    {
        if ($event->getRouteMatch()) {
            $controller = $event->getRouteMatch()->getParam('controller');
        } else {
            return;
        }
        
        if (self::$cachePageIsActive && $controller == 'Rubedo\Frontoffice\Controller\Index' && $event->getRequest()->isGet()) {
            
            if ($this->pageHit) {
                $message = 'returning cache for ' . $controller;
                Manager::getService('Logger')->info($message);
                return;
            }
            $cache = Cache::getCache();
            
            $maxLifeTime = Manager::getService('PageContent')->getMaxLifeTime();
            if ($maxLifeTime >= 0) {
                if ($maxLifeTime > 0) {
                    $cache->setOptions(array(
                        'ttl' => $maxLifeTime
                    ));
                }
                
                $response = $event->getResponse();
                
                if ($response->isOk()) {
                    $uri = $event->getRequest()->getUri();
                    $key = 'page_response_' . md5($uri->getHost() . $uri->getPath() . $uri->getQuery());
                    $user = Manager::getService('CurrentUser')->getCurrentUser();
                    if ($user) {
                        return;
                    }
                    $cache->setItem($key, $response->getContent());
                }
            }
        }
        $message = 'finished rendering ' . $controller;
        Manager::getService('Logger')->info($message);
    }

    /**
     * If correct context, initialize user session
     *
     * @param MvcEvent $e            
     */
    protected function initializeSession(MvcEvent $e)
    {
        $config = $e->getApplication()
            ->getServiceManager()
            ->get('Config');
        
        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($config['session']);
        $this->sessionName = $config['session']['name'];
        
        $mongoInfos = Mongo\DataAccess::getDefaultMongo();
        try {
            $adapter = Manager::getService('MongoDataAccess')->getAdapter($mongoInfos);
            $dbName = Mongo\DataAccess::getDefaultDb();
            
            $options = new MongoDBOptions(array(
                'database' => $dbName,
                'collection' => 'sessions'
            ));
            
            $saveHandler = new MongoDB($adapter, $options);
            
            $this->sessionManager = new SessionManager($sessionConfig);
            $this->sessionManager->setSaveHandler($saveHandler);
            
            Container::setDefaultManager($this->sessionManager);
        } catch (\MongoConnectionException $e) {
            static::$cachePageIsActive = false;
        }
    }
    
    /**
     * Actually start the session
     * 
     * Not called in special cases, even if initialize session was called
     */
    protected function startSession ()
    {
        if (isset($_COOKIE[$this->sessionName])) {
            $this->sessionManager->start();
        }
    }

    /**
     * Dispatch to a special controller to return dispatching exceptions the same way as later exceptions
     * 
     * @param MvcEvent $event
     * @param \Exception $exception
     */
    protected function toDeadEnd (MvcEvent $event,\Exception $exception)
    {
        $routeMatches = $event->getRouteMatch();
        $routeMatches->setParam('controller', 'Rubedo\\Frontoffice\\Controller\\Error');
        $routeMatches->setParam('action', 'index');
        $event->getRequest()
            ->getQuery()
            ->set('exception', $exception);
        return;
    }
}
