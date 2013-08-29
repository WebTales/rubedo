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
use Zend\Json\Json;
use Rubedo\Services\Manager;
use Rubedo\Services\Events;
use Rubedo\Elastic\DataAbstract;
use Rubedo\Collection\AbstractLocalizableCollection;
use Rubedo\Exceptions\JsonExceptionStrategy;
use Rubedo\Exceptions\Access as AccessException;
use Rubedo\Collection\SessionData;

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
        
        $this->initMongodb($config);
        $this->initElastic($config);
        $this->initLocalization($config);
        $this->initExtjs($config);
        $this->initSwiftMail($config);
        $this->initSites($config);
        $this->initSettings($config);
        SessionData::setSessionName($config['session']['name']);
        
        Interfaces\config::initInterfaces();
        
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array(
            $this,
            'preDispatch'
        ));
        
        // Config json enabled exceptionStrategy
        $exceptionStrategy = new JsonExceptionStrategy();
        
        // @todo import config
        $displayExceptions = true;
        
        $exceptionStrategy->setDisplayExceptions($displayExceptions);
        $exceptionStrategy->attach($application->getEventManager());
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
        if ($controller != 'Rubedo\\Backoffice\\Controller\\XhrAuthentication' || $action != 'is-session-expiring') {
            $this->initializeSession($event);
            
            list ($applicationName, $moduleName, $constant, $controllerName) = explode('\\', $controller);
            $controllerName = strtolower($controllerName);
            $moduleName = strtolower($moduleName);
            
            // check access
            $ressourceName = 'execute.controller.' . $controllerName . '.' . $action . '.' . $moduleName;
            if ($moduleName == 'install') {
                $hasAccess = true;
            } elseif (($moduleName == 'frontoffice' || ! isset($moduleName)) && (($action == 'index' && $controller == 'index') || ($action == 'error' && $controller == 'error') || ($action == 'index' && $controller == 'image') || ($action == 'index' && $controller == 'dam'))) {
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

    protected function initElastic($options)
    {
        if (isset($options)) {
            DataAbstract::setOptions($options['elastic']);
        }
        $indexContentOptionsJson = file_get_contents(APPLICATION_PATH . '/config/elastica.json');
        $indexContentOptions = Json::decode($indexContentOptionsJson, Json::TYPE_ARRAY);
        DataAbstract::setContentIndexOption($indexContentOptions);
        DataAbstract::setDamIndexOption($indexContentOptions);
    }

    protected function initMongodb($config)
    {
        $options = $config['datastream'];
        if (isset($options)) {
            $connectionString = 'mongodb://';
            if (! empty($options['mongo']['login'])) {
                $connectionString .= $options['mongo']['login'];
                $connectionString .= ':' . $options['mongo']['password'] . '@';
            }
            $connectionString .= $options['mongo']['server'];
            if (isset($options['mongo']['port'])) {
                $connectionString .= ':' . $options['mongo']['port'];
            }
            Mongo\DataAccess::setDefaultMongo($connectionString);
            
            Mongo\DataAccess::setDefaultDb($options['mongo']['db']);
        }
    }

    /**
     * Load services parameter from application.ini to the service manager
     */
    protected function initSites($config)
    {
        $options = $config['site'];
        if (isset($options['override'])) {
            \Rubedo\Collection\Sites::setOverride($options['override']);
        }
    }

    protected function initExtjs($config)
    {
        $options = $config['backoffice']['extjs'];
        \Rubedo\Backoffice\ExtConfig::setConfig($options);
    }

    protected function initSwiftMail($config)
    {
        if (isset($config['swiftmail'])) {
            \Rubedo\Mail\Mailer::setOptions($config['swiftmail']);
        }
    }

    protected function initLocalization($config)
    {
        $options = $config['localisationfiles'];
        if (isset($options)) {
            
            \Rubedo\Internationalization\Translate::setLocalizationJsonArray($options);
        }
    }

    protected function initSettings($config)
    {
        $options = $config['phpSettings'];
        if (isset($options['enableEmailNotification'])) {
            \Rubedo\Mail\Notification::setSendNotification(true);
            \Rubedo\Mail\Notification::setOptions('defaultBackofficeHost', isset($options['defaultBackofficeHost']) ? $options['defaultBackofficeHost'] : null);
            \Rubedo\Mail\Notification::setOptions('isBackofficeSSL', isset($options['isBackofficeSSL']) ? $options['isBackofficeSSL'] : false);
            \Rubedo\Mail\Notification::setOptions('fromEmailNotification', isset($options['fromEmailNotification']) ? $options['fromEmailNotification'] : null);
        }
    }

    protected function toDeadEnd(MvcEvent $event, \Exception $exception)
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
