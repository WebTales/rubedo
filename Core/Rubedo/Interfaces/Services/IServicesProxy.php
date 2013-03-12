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
 * Service Proxy
 *
 * Proxy to actual services
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IServicesProxy
{
    /**
     * protected constructor : create manager object and nested service object
     *
     * @param string $serviceClassName Name of nested class
     * @param string $serviceName Name of the service
	 * @param object $serviceObject Override the service object
     */
    public function __construct($serviceClassName, $serviceName, $serviceObject = null);

    /**
     * Getter of the current service  parameters or the specified parameter
     *
     * @param string $name optionnal parameter name
     * @return mixed value or array of valuefor asked parameter
     */
    public function getCurrentOptions($name = null);

    /**
     * Getter of the current service name
     * @return string
     */
    public function getServiceName();

    /**
     *
     * Call : magic method invoke when calling a none existing manager method, proxy to the service object
     *
     * @param string $name service method name
     * @param array $arguments service method array of arguments
     */
    public function __call($name, $arguments);
}
