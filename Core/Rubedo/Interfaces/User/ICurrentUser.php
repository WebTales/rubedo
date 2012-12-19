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
 * Current User Service
 *
 * Get current user and user informations
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface ICurrentUser
{
    /**
     * Return the authenticated user array
     *
     * @return array
     */
    public function getCurrentUser();

    /**
     * Return the current user short info array
     *
     * @return array
     */
    public function getCurrentUserSummary();

    /**
     * Check if a user is authenticated
     *
     * @return boolean
     */
    public function isAuthenticated();
	
	/**
	 * return the groups of the current user.
	 * 
	 * @return array
	 */
	public function getGroups();
	
	/**
	 * Change the password of the current user
	 * 
	 * @param string $oldPass current password
	 * @param string $newPass new password
	 */
	public function changePassword($oldPass,$newPass);
}
