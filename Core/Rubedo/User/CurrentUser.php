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
use Rubedo\Services\Manager;

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
     * Static : do not do an authentication and a data fetch each time the
     * service is instanciated
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
     * current user group list
     * 
     * @var array
     */
    protected static $_groups = null;

    /**
     * is current User a global Admin ?
     * 
     * @var boolean
     */
    protected static $_isGlobalAdmin = null;

    /**
     * Return the authenticated user array
     *
     * @return array
     */
    public function getCurrentUser ()
    {
        if (! isset(self::$_currentUser)) {
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
    public function getCurrentUserSummary ()
    {
        $userInfos = $this->getCurrentUser();
        return array(
            'id' => $userInfos['id'],
            'login' => $userInfos['login'],
            'fullName' => $userInfos['name']
        );
    }

    /**
     * Check if a user is authenticated
     *
     * @return boolean
     */
    public function isAuthenticated ()
    {
        $serviceAuth = \Rubedo\Services\Manager::getService('Authentication');
        return $serviceAuth->hasIdentity();
    }

    /**
     * Fetch the current user information from the data storage
     *
     * @return array
     */
    protected function _fetchCurrentUser ()
    {
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
    public function getGroups ()
    {
        if (! isset(self::$_groups)) {
            $user = $this->getCurrentUser();
            if (is_null($user)) {
                return array(
                    Manager::getService('Groups')->getPublicGroup()
                );
            }
            
            $groupsArray = Manager::getService('Groups')->getListByUserId($user['id']);
            if (count($groupsArray['data']) == 0) {
                return array(
                    Manager::getService('Groups')->getPublicGroup()
                );
            }
            self::$_groups = $groupsArray['data'];
        }
        return self::$_groups;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\User\ICurrentUser::getMainGroup()
     */
    public function getMainGroup ()
    {
        $user = $this->getCurrentUser();
        if(isset($user['defaultGroup'])){
            return $user['defaultGroup'];
        }else{
            return null;
        }
        
    }

    /**
     * Change the password of the current user
     *
     * @param string $oldPass
     *            current password
     * @param string $newPass
     *            new password
     */
    public function changePassword ($oldPass, $newPass)
    {
        $user = $this->getCurrentUser();
        
        $serviceAuth = \Rubedo\Services\Manager::getService('Authentication');
        if ($serviceAuth->forceReAuth($user['login'], $oldPass)) {
            $serviceUser = \Rubedo\Services\Manager::getService('Users');
            return $serviceUser->changePassword($newPass, $user['version'], $user['id']);
        } else {
            return false;
        }
    }

    /**
     * Generate a token for the current user
     *
     * @return string
     */
    public function generateToken ()
    {
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
    public function getToken ()
    {
        $sessionService = \Rubedo\Services\Manager::getService('Session');
        
        $user = $sessionService->get('user');
        $token = isset($user['token']) ? $user['token'] : "";
        
        if ($token == "") {
            $token = $this->generateToken();
        }
        return $token;
    }

    /**
     * return current user "can read" workspaces
     *
     * @return array
     */
    public function getReadWorkspaces ()
    {
        $groupArray = $this->getGroups();
        if ($this->_isGlobalAdmin()) {
            return array(
                'all'
            );
        }
        $workspaceArray = array();
        
        foreach ($groupArray as $group) {
            $workspaceArray = array_unique(array_merge($workspaceArray, Manager::getService('Groups')->getReadWorkspaces($group['id'])));
        }
        return $workspaceArray;
    }

    /**
     * return main workspace of the current user
     *
     * @todo implement real "main group" & "main workspace" base on order.
     * @return array
     */
    public function getMainWorkspace ()
    {
        
        //return Manager::getService('Workspaces')->findById('global');
        
        $mainGroup = $this->getMainGroup();
        if ($mainGroup==null) {
            return Manager::getService('Workspaces')->findById('global');
        }
        return Manager::getService('Groups')->getMainWorkspace($mainGroup);
    }

    /**
     * return current user "can write" workspaces
     *
     * @return array
     */
    public function getWriteWorkspaces ()
    {
        if($this->_isGlobalAdmin()){
            $workspaceList = Manager::GetService("Workspaces")->getWholeList();
            foreach ($workspaceList['data'] as $workspace) {
                $workspaceArray[] = $workspace['id'];
            }
        }else{
            $groupArray = $this->getGroups();
            $workspaceArray = array();
            
            foreach ($groupArray as $group) {
                $workspaceArray = array_unique(array_merge($workspaceArray, Manager::getService('Groups')->getWriteWorkspaces($group['id'])));
            }
        }
        
        return $workspaceArray;
    }

    /**
     * Is the current user a global admin ?
     * @return boolean
     */
    protected function _isGlobalAdmin ()
    {
        if (! isset(self::$_isGlobalAdmin)) {
            self::$_isGlobalAdmin = false;
            $groupList = $this->getGroups();
            foreach ($groupList as $group) {
                if ($group['name'] == 'admin') {
                    self::$_isGlobalAdmin = true;
                }
            }
        }
        return self::$_isGlobalAdmin;
    }
}
