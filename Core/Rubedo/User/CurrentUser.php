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
namespace Rubedo\User;

use Rubedo\Interfaces\User\ICurrentUser;

/**
 * Current User Service
 *
 * Get current user and user informations
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class CurrentUser implements ICurrentUser
{

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
            if ($this->isAuthenticated()) {
                self::$_currentUser = $this->_fetchCurrentUser();
            }
        }
        return self::$_currentUser;
    }

    /**
     * Return the current user short info array
     *
     * @return array
     */
    public function getCurrentUserSummary() {
        $userInfos = $this->getCurrentUser();
        return array('id' => $userInfos['id'], 'login' => $userInfos['login'], 'fullName' => $userInfos['name']);
    }

    /**
     * Check if a user is authenticated
     *
     * @return boolean
     */
    public function isAuthenticated() {
        $serviceAuth = \Rubedo\Services\Manager::getService('Authentication');
        return $serviceAuth->hasIdentity();
    }

    /**
     * Fetch the current user information from the data storage
     *
     * @return array
     */
    protected function _fetchCurrentUser() {
        $serviceAuth = \Rubedo\Services\Manager::getService('Authentication');
        $sessionUser = $serviceAuth->getIdentity();

        $serviceReader = \Rubedo\Services\Manager::getService('Users');

        $user = $serviceReader->findById($sessionUser['id']);
        return $user;
    }

    /**
     * return the groups of the current user.
     *
     * @todo to be implemented with real groups !
     * @return array
     */
    public function getGroups() {
        $user = $this->getCurrentUser();
        $groups = array();
        switch($user['login']) {
            case 'admin' :
                $groups[] = 'admin';
            case 'valideur' :
                $groups[] = 'valideur';
            case 'redacteur' :
                $groups[] = 'redacteur';
            default :
                $groups[] = 'public';
                break;
        }

        return $groups;
    }

	/**
	 * Change the password of the current user
	 * 
	 * @param string $oldPass current password
	 * @param string $newPass new password
	 */
	public function changePassword($oldPass,$newPass){
		$user = $this->getCurrentUser();

		$serviceAuth = \Rubedo\Services\Manager::getService('Authentication');
		if($serviceAuth->forceReAuth($user['login'], $oldPass)){
			$serviceUser = \Rubedo\Services\Manager::getService('Users');
			return $serviceUser->changePassword($newPass,$user['version'],$user['id']);
		}else{
			return false;
		}	
	}
	
	/**
	 * Generate a token for the current user
	 * 
	 * @return string
	 */
	public function generateToken() {
		$sessionService = \Rubedo\Services\Manager::getService('Session');
		$hashService = \Rubedo\Services\Manager::getService('Hash');
		
		$user = $sessionService->get('user');
		
		$token = $hashService->generateRandomString(20);
		$user['token'] = $hashService->derivatePassword($token, $hashService->generateRandomString(10));
		$sessionService->set('user', $user);
		
		return $user['token'];
	}
	
	/**
	 * Return the token of the current user
	 * 
	 * @return string
	 */
	public function getToken() {
		$sessionService = \Rubedo\Services\Manager::getService('Session');
		
		$user = $sessionService->get('user');
		$token = isset($user['token'])?$user['token']:"";
		
		if($token == ""){
			$token = $this->generateToken();
		}
		
		return $token;
	}
	
}
