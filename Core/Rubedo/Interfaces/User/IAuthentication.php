<?php

/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace Rubedo\Interfaces\User;

/**
 * Authentication Service
 *
 * Authenticate user and get information about him
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IAuthentication
{
    
	/**
	 * Authenticate the user and set the session
	 * 
	 * @param $login It's the login of the user
	 * @param $password It's the password of the user
	 * 
	 * @return bool
	 */
    public function authenticate($login, $password);
	
	/**
	 * Return the identity of the current user in session
	 * 
	 * @return array
	 */
	public function getIdentity();
	
	/**
	 * Return true if there is a user connected
	 * 
	 * @return bool
	 */
	public function hasIdentity();
	
	/**
	 * Unset the session of the current user
	 * 
	 * @return bool
	 */
	public function clearIdentity();
	
	/**
	 * Ask a reauthentification without changing the session
	 * 
	 * @param $login It's the login of the user
	 * @param $password It's the password of the user
	 * 
	 * @return bool
	 */
	public function forceReAuth($login, $password);
	
}
