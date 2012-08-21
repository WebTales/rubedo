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
