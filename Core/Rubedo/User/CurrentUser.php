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
namespace Rubedo\User;

use Rubedo\Interfaces\Services\ICurrentUser;

/**
 * Current User Service
 *
 * Get current user and user informations
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class CurrentUser implements ICurrentUser {

	/**
	 * Current User Object
	 * 
	 * Static : do not do an authentication and a data fetch each time the service is instanciated
	 *
	 * @var array
	 */
	protected static $_currentUser = null;

	/**
	 * Current User Id
	 *
	 * @var int
	 */
	protected static $_currentUserId = null;

	/**
	 * Return the authenticated user array
	 *
	 * @return array
	 */
	public function getCurrentUser() {
		if (!isset(self::$_currentUser)) {
			if ($this -> isAuthenticated()) {
				self::$_currentUser = $this -> fetchCurrentUser();
			}
		}
		return self::$_currentUser;
	}

	/**
	 * Check if a user is authenticated
	 *
	 * @todo to be implemented
	 * @return boolean
	 */
	public function isAuthenticated() {
		return true;
	}

	/**
	 * Fetch the current user information from the data storage
	 *
	 * @todo to be implemented
	 * @return array
	 */
	public function fetchCurrentUser() {
		return array('id' => 1, 'login' => 'jbourdin', 'fullName' => 'Julien Bourdin');
	}

}
