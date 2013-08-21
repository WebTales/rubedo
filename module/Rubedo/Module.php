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
use Zend\Debug\Debug;

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
        
        $defaultArray = array(
            'logLevel' => 3,
            'enableCache' => 1
        );
        Services\Manager::setOptions($defaultArray);
        
        $serviceOptions = Services\Manager::getOptions();
        
        Interfaces\config::initInterfaces();
        
        Services\Manager::setServiceLocator($e->getApplication()->getServiceManager());
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
}
