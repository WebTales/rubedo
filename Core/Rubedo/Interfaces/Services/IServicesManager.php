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

namespace Rubedo\Interfaces\Services;

/**
 * Service Manager Interface
 *
 * Proxy to actual services, offer a static getService and handle dependancy injection
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IServicesManager
{
    
    /**
     * Setter of services parameters, to init them from bootstrap
     * 
     * @param array $options
     */
    public static function setOptions($options);
	
	/**
     * getter of services parameters, to init them from bootstrap
     *
     */
    public static function getOptions ();
    
    
    /**
     * Public static method to get an instance of the service given by its name
     * 
     * Return an instance of the manager containing the actual service object
     *
     * @param string $serviceName name of the service 
     * @return static instance of the manager
     */
    public static function getService($serviceName);
    /**
     * Set a mock service for testing purpose
     *
     * @param string $serviceName Name of the service overridden
     * @param object $obj mock object substituted to the service
     */
    public static function setMockService($serviceName, $obj);
    /**
     * Reset the mockObject array for isolation purpose
     */
    public static function resetMocks();
    
    
}