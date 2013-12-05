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
     * Return the token of the current user
     *
     * @return string
     */
    public function getToken();

    /**
     * Generate a token for the current user
     *
     * @return string
     */
    public function generateToken();

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
     * @param string $oldPass
     *            current password
     * @param string $newPass
     *            new password
     */
    public function changePassword($oldPass, $newPass);

    /**
     * return the main group of the current User
     *
     * @return arr
     */
    public function getMainGroup();

    /**
     * return current user "can read" workspaces
     *
     * @return array
     */
    public function getReadWorkspaces();

    /**
     * return main workspace of the current user
     *
     * @return array
     */
    public function getMainWorkspace();

    /**
     * return current user "can write" workspaces
     *
     * @return array
     */
    public function getWriteWorkspaces();

    /**
     * Set the flag installer User
     *
     * @param boolean $_isInstallerUser
     */
    public static function setIsInstallerUser($_isInstallerUser);
}
