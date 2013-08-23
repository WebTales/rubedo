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
use Zend\Json\Json;
use Rubedo\Services\Manager;
use Rubedo\Elastic\DataAbstract;
use Rubedo\Collection\AbstractLocalizableCollection;

class Module
{

    public function onBootstrap (MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $application = $e->getApplication();
        $config = $application->getConfig();
        
        $this->initMongodb($config);
        $this->initElastic($config);
        
        Interfaces\config::initInterfaces();
        
        Services\Manager::setServiceLocator($e->getApplication()->getServiceManager());
        
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array(
            $this,
            'preDispatch'
        ));
    }

    public function getConfig ()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig ()
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
    public function preDispatch (MvcEvent $event)
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
                throw new \Rubedo\Exceptions\Access('Can\'t access %1$s', "Exception30", $ressourceName);
            }
            
            // check BO Token
            $isBackoffice = strpos($controller, 'Rubedo\\Backoffice\\Controller') === 0;
            $doNotCheckTokenControllers = array(
                'Rubedo\\Backoffice\\Controller\\Acl',
                'Rubedo\\Backoffice\\Controller\\XhrAuthentication'
            );
            if ($isBackoffice && $event->getRequest()->isPost() && ! in_array($controller, $doNotCheckTokenControllers)) {
                $user = Manager::getService('Session')->get('user');
                $token = $event->getRequest()->getPost('token');
                
                if ($token !== $user['token']) {
                    throw new \Rubedo\Exceptions\Access("The token given in the request doesn't match with the token in session", "Exception6");
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

    public function initializeSession (MvcEvent $e)
    {
        $config = $e->getApplication()
            ->getServiceManager()
            ->get('Config');
        
        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($config['session']);
        
        $sessionManager = new SessionManager($sessionConfig);
        $sessionManager->start();
        // $sessionManager->regenerateId(false);
        
        Container::setDefaultManager($sessionManager);
    }

    protected function initElastic ($options)
    {
        if (isset($options)) {
            DataAbstract::setOptions($options['elastic']);
        }
        $indexContentOptionsJson = file_get_contents(APPLICATION_PATH . '/config/elastica.json');
        $indexContentOptions = Json::decode($indexContentOptionsJson, Json::TYPE_ARRAY);
        DataAbstract::setContentIndexOption($indexContentOptions);
        DataAbstract::setDamIndexOption($indexContentOptions);
    }

    protected function initMongodb ($config)
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
}
