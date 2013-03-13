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
