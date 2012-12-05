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

namespace Rubedo\Interfaces\User;

/**
 * Session Service
 *
 * Get current user and user informations
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface ISession
{
     /**
     * Returns a session object
     *
     * @return object
     */
    public function getSessionObject();
	
	 /**
     * Set the session object with name and value params
     *
	  * @param string $name
	  * @param mixed $value 
     */
	public function set($name, $value);
	
    /**
     * Return the session object requested by $name
     * 
     * @param string $name name of the parameter
	 * @param mixed $defaultValue default value in case of not set parameter in session
     * @return mixed value in session
     */
	public function get($name,$defaultValue = null);
}
