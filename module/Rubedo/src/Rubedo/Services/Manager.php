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
namespace Rubedo\Services;

/**
 * Service Manager Interface
 *
 * Proxy to actual services, offer a static getService and handle dependancy
 * injection
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Manager
{

    protected static $_serviceLocator;

    /**
     * Array of mock service
     */
    protected static $_mockServicesArray = array();

    /**
     * Reset the mockObject array for isolation purpose
     */
    public static function resetMocks()
    {
        self::$_mockServicesArray = array();
    }

    /**
     * Set a mock service for testing purpose
     *
     * @param string $serviceName
     *            Name of the service overridden
     * @param object $obj
     *            mock object substituted to the service
     */
    public static function setMockService($serviceName, $obj)
    {
        self::$_mockServicesArray[$serviceName] = $obj;
    }

    /**
     * Public static method to get an instance of the service given by its
     * name
     *
     *
     * @param string $serviceName
     *            name of the service
     * @return static instance of the service
     */
    public static function getService($serviceName)
    {
        if (array_key_exists($serviceName, self::$_mockServicesArray)) {
            return self::$_mockServicesArray[$serviceName];
        }
        return self::getServiceLocator()->get($serviceName);
    }

    /**
     *
     * @return the $_serviceLocator
     */
    public static function getServiceLocator()
    {
        return Manager::$_serviceLocator;
    }

    /**
     *
     * @param field_type $_serviceLocator            
     */
    public static function setServiceLocator($_serviceLocator)
    {
        Manager::$_serviceLocator = $_serviceLocator;
    }
}
