<?php

/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

/**
 * Application initialization class
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    /**
     * Init the DB info by setting static values in the DB class
     */
    protected function _initMongoDataStream ()
    {
        $options = $this->getOption('datastream');
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
            Rubedo\Mongo\DataAccess::setDefaultMongo($connectionString);
            
            Rubedo\Mongo\DataAccess::setDefaultDb($options['mongo']['db']);
        }
    }

    /**
     * Init the ES info by setting static values in the Search class
     */
    protected function _initElasticSearchStream ()
    {
        $options = $this->getOption('searchstream');
        if (isset($options)) {
            Rubedo\Elastic\DataAbstract::setOptions($options['elastic']);
        }
        $indexContentOptionsJson = file_get_contents(APPLICATION_PATH . '/configs/elastica.json');
        $indexContentOptions = Zend_Json::decode($indexContentOptionsJson);
        Rubedo\Elastic\DataAbstract::setContentIndexOption($indexContentOptions);
    }
    
    /**
     * Init the authenticated session lifeTime
     */
    protected function _initAuthentication ()
    {
        $options = $this->getOption('authentication');
        if (isset($options['authLifetime'])) {
            Rubedo\User\Authentication::setAuthLifetime($options['authLifetime']);
        }
    }

    /**
     * Load services parameter from application.ini to the service manager
     */
    protected function _initServices ()
    {
        $options = $this->getOption('services');
        if (isset($options)) {
            Rubedo\Services\Manager::setOptions($options);
        } else {
            $defaultArray = array(
                'logLevel' => 3,
                'enableCache' => 1
            );
            Rubedo\Services\Manager::setOptions($defaultArray);
        }
        $serviceOptions = Rubedo\Services\Manager::getOptions();
        
        Rubedo\Interfaces\config::initInterfaces();
        /*
         * define('LOG_LEVEL', $serviceOptions['logLevel']);
         * define('ENABLE_CACHE', $serviceOptions['enableCache']);
         */
    }

    /**
     * Load services parameter from application.ini to the service manager
     */
    protected function _initSites ()
    {
        $options = $this->getOption('site');
        if (isset($options['override'])) {
            Rubedo\Collection\Sites::setOverride($options['override']);
        }
    }

    /**
     * Load services parameter from application.ini to the service manager
     */
    protected function _initExtjs ()
    {
        $options = $this->getOption('backoffice');
        if (! isset($options['extjs'])) {
            $extjsOptions = array(
                'debug' => false,
                'network' => 'local'
            );
        } else {
            $extjsOptions = $options['extjs'];
        }
        Zend_Registry::set('extjs', $extjsOptions);
    }

    /**
     * Load parameter from application.ini for swiftMail
     */
    protected function _initSwiftMail ()
    {
        $options = $this->getOption('swiftmail');
        if (isset($options)) {
            Zend_Registry::set('swiftMail', $options);
            
            $options = $this->getOption('phpSettings');
            if (isset($options['enableEmailNotification'])) {
                Rubedo\Mail\Notification::setSendNotification(true);
                Rubedo\Mail\Notification::setOptions('defaultBackofficeHost', isset($options['defaultBackofficeHost']) ? $options['defaultBackofficeHost'] : null);
                Rubedo\Mail\Notification::setOptions('isBackofficeSSL', isset($options['isBackofficeSSL']) ? $options['isBackofficeSSL'] : false);
                Rubedo\Mail\Notification::setOptions('fromEmailNotification', isset($options['fromEmailNotification']) ? $options['fromEmailNotification'] : null);
            }
        }
    }

    /**
     * Load router configuration with specific rules
     */
    protected function _initRouter ()
    {
        $front = $this->bootstrap('FrontController')->getResource('FrontController');
        
        /**
         *
         * @var Zend_Controller_Router_Rewrite
         */
        $router = $front->getRouter();
        
        $route = new Zend_Controller_Router_Route_Regex('dam\..*', array('controller' => 'dam', 'action' => 'index', 'module' => 'default'));
		$router->addRoute('dam', $route);
        
        // default front office route
        $route = new Rubedo\Router\Route();
        $router->addRoute('rewrite', $route);
    }

    /**
     * Ensure UTF-8
     *
     * @return Zend_Controller_Front
     */
    protected function _initFrontControllerOutput ()
    {
        $this->bootstrap('FrontController');
        $frontController = $this->getResource('FrontController');
        
        $response = new Zend_Controller_Response_Http();
        if(APPLICATION_ENV != 'testing'){
            $response->setHeader('Content-Type', 'text/html; charset=UTF-8', true);
        }
        $frontController->setResponse($response);
        
        return $frontController;
    }
}
