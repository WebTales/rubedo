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
use Rubedo\Elastic\DataAbstract;
use Zend\Json\Json;

class Module
{

    public function onBootstrap(MvcEvent $e)
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
        
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array(
            $this,
            'authPreDispatch'
        ), 1);
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
     * Authenticate user or redirect to log in
     */
    public function authPreDispatch(MvcEvent $event)
    {
        $controller = $event->getRouteMatch()->getParam('controller');
        $action = $event->getRouteMatch()->getParam('action');
        if ($controller != 'Rubedo\\Backoffice\\Controller\\XhrAuthentication' || $action != 'is-session-expiring') {
            $this->initializeSession($event);
        }
        
        //check authentication

        //check BO Token        
        $isBackoffice = strpos($controller,'Rubedo\\Backoffice\\Controller')===0;
        $doNotCheckTokenControllers = array('Rubedo\\Backoffice\\Controller\\Acl');
        if($isBackoffice && $event->getRequest()->isPost() && !in_array($controller,$doNotCheckTokenControllers)){
            $user = Manager::getService('Session')->get('user');
            $token = $event->getRequest()->getPost('token');
            
            if ($token !== $user['token']) {
                throw new \Rubedo\Exceptions\Access("The token given in the request doesn't match with the token in session", "Exception6");
            }
        }

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
        // $sessionManager->regenerateId(false);
        
        Container::setDefaultManager($sessionManager);
    }

    protected function initElastic($options)
    {
        if (isset($options)) {
            DataAbstract::setOptions($options['elastic']);
        }
        $indexContentOptionsJson = file_get_contents(APPLICATION_PATH . '/config/elastica.json');
        $indexContentOptions = Json::decode($indexContentOptionsJson,Json::TYPE_ARRAY);
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
}
