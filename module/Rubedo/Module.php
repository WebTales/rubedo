<?php
/**
 * Rubedo -- ECM solution Copyright (c) 2014, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo;

use Rubedo\Cache\MongoCache;
use Rubedo\Collection\AbstractCollection;
use Rubedo\Collection\AbstractLocalizableCollection;
use Rubedo\Collection\SessionData;
use Rubedo\Collection\WorkflowAbstractCollection;
use Rubedo\Exceptions\Access as AccessException;
use Rubedo\Exceptions\JsonExceptionStrategy;
use Rubedo\Mongo\DataAccess;
use Rubedo\Router\Url;
use Rubedo\Security\HtmlCleaner;
use Rubedo\Services\Cache;
use Rubedo\Services\Events;
use Rubedo\Services\Manager;
use Rubedo\User\Authentication\AuthenticationService as Authentication;
use Zend\EventManager\EventManager;
use Zend\Http\Response;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Zend\Session\SaveHandler\MongoDB;
use Zend\Session\SaveHandler\MongoDBOptions;
use Zend\Session\SessionManager;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\Request as ConsoleRequest;

/**
 * Loading class for the Rubedo Main module
 *
 * Handle initialization, configuration and events
 *
 * @author jbourdin
 *
 */
class Module implements ConsoleUsageProviderInterface, ConsoleBannerProviderInterface
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

    public function getConsoleBanner(Console $console)
    {
        return 'Rubedo 3.2';
    }

    public function getConsoleUsage(Console $console)
    {
        return [
            'cache clear [config|files|mongo|url|api]' => 'Clear all or specific application caches',
            ["config", "Clear configuration files in cache"],
            ["files", "Clear cached files (*.js, *.css, etc.)"],
            ["mongo", "Clear mongo objects in cache"],
            ["url", "Clear URL in cache"],
            ["api", "Clear API's requests in cache"],
            'cache count' => 'Returns the number of cached elements',
            'index [<type>]' => 'Index all items or specific index in ElasticSearch',
            ["type", "Name of the index"],
            'config setdb --server= --port= --db= [--replicaSetName=] [--adminLogin=] [--adminPassword=] [--login=] [--password=]'=>"Configure instance database connection",
            'config setes --host= --port= --contentIndex= --damIndex= --userIndex='=>"Configure instance elasticsearch connection",
            'config setlang <lang>'=>"Configure instance default language",
            'config reset'=>"Reset intance config",
            'config initdb'=>"Do DB init",
            'config setfinished'=>"Set install status to finished",
            'config setadmin --name=  --email= --login= --password='=>"Create admin user",
            'config setdefault'=>"Set default php settings",
            'config setdb --server= --port= --db= [--replicaSetName=] [--timeout=] [--adminLogin=] [--adminPassword=] [--login=] [--password=] [--readPreference=]'=>"Set mongodb config",
            'config createsite --domain= --lang= [--theme=]'=>"Create website",
            'config getfull'=>"Get full config as JSON",
            'config setfull --conf='=>"Set full config from JSON",
            'config getdb'=>"get the mongo conf",
            'config getes'=>"get the elasticsearch conf",
            'config getweb'=>"get the web cluster conf",
            'config getmail'=>"get mail conf",
            'config saveconfigtodb'=>"save local config to db",
            'config restoreconfigfromdb'=>"restore local config from db",
            'config setmail --server= --port= [--ssl] --username= --password='=>"Configure the mailer",
            'config getrubedoconfig'=>"get rubedo_config",
            'config setrubedoconfig [--minify] [--cachePage] [--apiCache] [--useCdn] [--extDebug] [--addECommerce] [--activateMagic] [--defaultBackofficeHost=] [--isBackofficeSSL] [--enableEmailNotification] [--fromEmailNotification=]'=>"Configure rubedo_config",
        ];
    }

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

        if (method_exists($e->getRequest(), 'getUri')) {
            $message = 'Running Rubedo for ' . $e->getRequest()->getUri();
        } else {
            $message = 'Running Rubedo from CLI';
        }
        Manager::getService('Logger')->info($message);

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $application = $e->getApplication();
        $config = $application->getConfig();


        if (isset($config['rubedo_config']['cachePage']))
            self::$cachePageIsActive = filter_var($config['rubedo_config']['cachePage'], FILTER_VALIDATE_BOOLEAN);
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

        // handle URL caching
        $urlCacheService = Manager::getService('UrlCache');
        $eventManager->attach(Url::URL_TO_PAGE_READ_CACHE_PRE, array(
            $urlCacheService,
            'urlToPageReadCacheEvent'
        ), -100);

        $urlCacheService = Manager::getService('UrlCache');
        $eventManager->attach(Url::PAGE_TO_URL_READ_CACHE_PRE, array(
            $urlCacheService,
            'pageToUrlReadCacheEvent'
        ), -100);

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

        $eventManager->attach(array(
            DataAccess::POST_COMMAND,
            DataAccess::POST_EXECUTE,
        ), array(
            Manager::getService('ApplicationLogger'),
            'logDataAccessEvent'
        ), 1);

        $wasFiltered = AbstractCollection::disableUserFilter();
        $contentsService = Manager::getService('Contents');
        AbstractCollection::disableUserFilter($wasFiltered);

        $eventManager->attach(WorkflowAbstractCollection::POST_PUBLISH_COLLECTION, array(
            $contentsService,
            'indexPublishEvent'
        ));

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
        $eventManager->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function ($e) {
            list($vendor, $namespace) = explode('\\', get_class($e->getTarget()));
            //$config = $e->getApplication()->getServiceManager()->get('config');
            if ($vendor == 'ZF' && $namespace == 'Apigility') {
                $e->getTarget()->layout('layout/layout_apigility');
            }
        }, 100);
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
        $isConsole=$event->getRequest() instanceof ConsoleRequest;
        if (!$isConsole&&(!isset($config['installed']) || ((!isset($config['installed']['status']) || $config['installed']['status'] !== 'finished') && ($controller !== 'Rubedo\Install\Controller\Index')))&&!($controller == 'RubedoApi\Frontoffice\Controller\Api'&&strpos($_SERVER['REQUEST_URI'],'reloadconfig')!==false)) {


            $routeMatches = $event->getRouteMatch();
            $routeMatches->setParam('controller', 'Rubedo\Install\Controller\Index');
            $routeMatches->setParam('action', 'index');
        }

        $sessionDuration = $config['session']['remember_me_seconds'] ?: 3600;
        // init the session params and session itself
        $this->startSession($sessionDuration);

        // @todo forward if no language initialized

        // check access
        if ($controller && strpos($controller, 'RubedoApi') === false) {
            list ($applicationName, $moduleName, $constant, $controllerName) = explode('\\', $controller);
            unset($applicationName, $constant);
            $controllerName = strtolower($controllerName);
            $moduleName = strtolower($moduleName);
            $ressourceName = 'execute.controller.' . $controllerName . '.' . $action . '.' . $moduleName;
            if ($moduleName == 'install' || $moduleName == 'frontoffice' || $moduleName == 'console') {
                $hasAccess = true;
            } else {
                $aclService = Manager::getService('Acl');
                $hasAccess = $aclService->hasAccess($ressourceName);
            }

            if (!$hasAccess) {
                $this->toDeadEnd($event, new AccessException('Can\'t access %1$s', "Exception30", $ressourceName));
            }

            // check BO Token
            $isBackoffice = strpos($controller, 'Rubedo\\Backoffice\\Controller') === 0;
            $doNotCheckTokenControllers = array(
                'Rubedo\\Backoffice\\Controller\\Acl',
                'Rubedo\\Backoffice\\Controller\\XhrAuthentication',
                'Rubedo\\Backoffice\\Controller\\Logout'
            );
            if ($isBackoffice && $event->getRequest()->isPost() && !in_array($controller, $doNotCheckTokenControllers)) {
                $user = Manager::getService('Session')->get('user');
                $token = $event->getRequest()->getPost('token');
                if (!isset($token)) {
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
                    if (!$workingLanguage) {
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

        try {
            $adapter = Manager::getService('MongoDataAccess')->getAdapter();

            $options = new MongoDBOptions(array(
                'database' => Mongo\DataAccess::getDefaultDb(),
                'collection' => 'sessions'
            ));

            if ($e->getRequest()->getRequestUri() === "/api/v1/auth/oauth2/generate" || $e->getRequest()->getRequestUri() === "/backoffice/") {
                $options->setSaveOptions(array("w" => "majority"));
            }

            $saveHandler = new MongoDB($adapter, $options);

            $this->sessionManager = new SessionManager($sessionConfig);
            $connections = $adapter->getConnections();
            if (!empty($connections)) {
                $this->sessionManager->setSaveHandler($saveHandler);
            }

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
    protected function startSession($duration)
    {
        if (isset($_POST['remember-me']) && filter_var($_POST['remember-me'], FILTER_VALIDATE_BOOLEAN)) {
            $this->sessionManager->rememberMe($duration);
        }
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
    protected function toDeadEnd(MvcEvent $event, \Exception $exception)
    {
        $routeMatches = $event->getRouteMatch();
        $routeMatches->setParam('controller', 'Rubedo\\Frontoffice\\Controller\\Error');
        $routeMatches->setParam('action', 'index');
        if (method_exists($event->getRequest(), 'getQuery')) {
            $event->getRequest()
                ->getQuery()
                ->set('exception', $exception);
        }
    }
}
