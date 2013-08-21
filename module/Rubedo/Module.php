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
use Rubedo\Services\Manager;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $application = $e->getApplication();
        $config = $application->getConfig();
             
        
        $options = $config['datastream'];
        if (isset($options)) {
            $connectionString = 'mongodb://';
            if (! empty($options['mongo']['login'])) {
                $connectionString .= $options['mongo']['login'];
                $connectionString .= ':' . $options['mongo']['password'] . '@';
            }
            $connectionString .= $options['mongo']['server'];
            if(isset($options['mongo']['port'])){
                $connectionString .= ':'.$options['mongo']['port'];
            }
            Mongo\DataAccess::setDefaultMongo($connectionString);
        
            Mongo\DataAccess::setDefaultDb($options['mongo']['db']);
        }
        
        Interfaces\config::initInterfaces();
        
        Services\Manager::setServiceLocator($e->getApplication()->getServiceManager());
        
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'authPreDispatch'),1);
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
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    /**
     * Authenticate user or redirect to log in
     */
    public function authPreDispatch($event) {
        $controller = $event->getRouteMatch()->getParam('controller');
        $action = $event->getRouteMatch()->getParam('action');
        if($controller != 'Rubedo\\Backoffice\\Controller\\XhrAuthentication' || $action != 'is-session-expiring'){
                $this->initializeSession($event);
        }
        
        
//         $userService = $event->getApplication()->getServiceManager()->get('CurrentUser');
//         $whiteListController = array(
//             'MxAccueil\\Controller\\Index',
//             'MxAccueil\\Controller\\Login'
//         );
//         $adminOnly = array('MxAccueil\\Controller\\Customers'=>array('get-segments'));
    
//         if (! in_array($event->getRouteMatch()->getParam('controller'), $whiteListController)) {
//             if (! $userService->isLoggedIn()) {
//                 throw new \Zend\Authentication\Exception\RuntimeException('Authentification requise');
//             }
//         }
//         if(isset($adminOnly[$controller]) && in_array($action, $adminOnly[$controller])){
//             if(!$userService->isAdmin()){
//                 throw new \Zend\Authentication\Exception\RuntimeException('Seuls les administrateurs ont accès à cette fonctionnalité');
//             }
//         }
    
    }
    
    public function initializeSession(MvcEvent $e)
    {
        $config = $e->getApplication()
        ->getServiceManager()
        ->get('Config');
    
        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($config['session']);
    
        $sessionManager = new SessionManager($sessionConfig);
        $sessionManager->start();
        //$sessionManager->regenerateId(false);
    
        Container::setDefaultManager($sessionManager);
    }
}
