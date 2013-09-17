<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
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

class Module
{

    public function onBootstrap(MvcEvent $e)
    {
        // register serviceLocator for global access by Rubedo
        Manager::setServiceLocator($e->getApplication()->getServiceManager());
        
        // register eventManager for global access by Rubedo
        $eventManager = $e->getApplication()->getEventManager();
        Events::setEventManager($eventManager);
        
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $application = $e->getApplication();
        $config = $application->getConfig();
        
        SessionData::setSessionName($config['session']['name']);
        
        Interfaces\config::initInterfaces();
        
        //define all the events that should be handled
        $this->setListeners($eventManager);
        
        // Config json enabled exceptionStrategy
        $exceptionStrategy = new JsonExceptionStrategy();
        
        $displayExceptions = $config['view_manager']['display_exceptions'];
        
        $exceptionStrategy->setDisplayExceptions($displayExceptions);
        $exceptionStrategy->attach($application->getEventManager());
    }

    public function setListeners(EventManager $eventManager)
    {
        //verify session and access right when dispatching
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array(
            $this,
            'preDispatch'
        ));
        
        //add some cache on HtmlCleaner method
        $eventManager->attach(HtmlCleaner::PRE_HTMLCLEANER, array(
            'Rubedo\Services\Cache',
            'getFromCache'
        ), 100);
        
        $eventManager->attach(HtmlCleaner::POST_HTMLCLEANER, array(
            'Rubedo\Services\Cache',
            'setToCache'
        ), 100);
        
        //log hit & miss on Rubedo cache
        $eventManager->attach(array(MongoCache::CACHE_HIT,MongoCache::CACHE_MISS),array('Rubedo\Services\Cache','logCacheHit'),1);
        
        //log Rubedo writing on MongoDB collections
        $eventManager->attach(array(AbstractCollection::POST_CREATE_COLLECTION,AbstractCollection::POST_UPDATE_COLLECTION,AbstractCollection::POST_DELETE_COLLECTION,WorkflowAbstractCollection::POST_PUBLISH_COLLECTION),array(Manager::getService('ApplicationLogger'),'logCollectionEvent'),1);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

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
    public function preDispatch(MvcEvent $event)
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
        
        // init the session params and session itself
        $this->initializeSession($event);
        
        // @todo forward if not installed
        
        // @todo forward if no language initialized
        
        // check access
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

    protected function initializeSession(MvcEvent $e)
    {
        $config = $e->getApplication()
            ->getServiceManager()
            ->get('Config');
        
        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($config['session']);
        
        $mongoInfos = Mongo\DataAccess::getDefaultMongo();
        $adapter = Manager::getService('MongoDataAccess')->getAdapter($mongoInfos);
        $dbName = Mongo\DataAccess::getDefaultDb();
        
        $options = new MongoDBOptions(array(
            'database' => $dbName,
            'collection' => 'sessions'
        ));
        
        $saveHandler = new MongoDB($adapter, $options);
        
        $sessionManager = new SessionManager($sessionConfig);
        $sessionManager->setSaveHandler($saveHandler);
        
        $sessionManager->start();
        
        Container::setDefaultManager($sessionManager);
    }

    protected function toDeadEnd(MvcEvent $event,\Exception $exception)
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
