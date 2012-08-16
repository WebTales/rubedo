<?php

/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
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
     * getter of services 
     * 
     * @return array array of all the services 
     */
    public static function getOptions ();
    
    /**
     * Getter of the current service  parameters or the specified parameter
     * 
     * @param string $name optionnal parameter name
     * @return mixed value or array of valuefor asked parameter
     */
    public function getCurrentOptions ($name = null);
    
    /**
     * Getter of the current service name
     * @return string
     */
    public function getServiceName();
    
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
     *
     * Call : magic method invoke when calling a none existing manager method, proxy to the service object
     *
     * @param string $name service method name
     * @param array $arguments service method array of arguments
     */
    public function __call($name, $arguments);
}