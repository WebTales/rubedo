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
namespace Rubedo\User;

use Rubedo\Interfaces\User\ICurrentUser, Rubedo\Services\Manager, Rubedo\Collection\AbstractCollection;

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

    protected static $_readWorkspaces = null;

    protected static $_mainWorkspace = null;

    protected static $_writeWorkspaces = null;

    protected static $_rubedoUser = array(
        'fullName' => 'Rubedo',
        'id' => 'rubedo',
        'login' => 'rubedo'
    );

    protected static $_isInstallerUser = false;

    /**
     * Return the authenticated user array
     *
     * @return array
     */
    public function getCurrentUser ()
    {
        if (! isset(self::$_currentUser)) {
            if ($this->isAuthenticated()) {
                $user = $this->_fetchCurrentUser();
                if ($user === null) {
                    Manager::getService('Authentication')->clearIdentity();
                }
                
                self::$_currentUser = $user;
                if ($user) {
                    $mainWorkspace = $this->getMainWorkspace();
                    if ($mainWorkspace) {
                        $user['defaultWorkspace'] = $mainWorkspace['id'];
                        self::$_currentUser = $user;
                    }
                }
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
        if (self::$_isInstallerUser) {
            return self::$_rubedoUser;
        }
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
        $serviceAuth = Manager::getService('Authentication');
        return $serviceAuth->hasIdentity();
    }

    /**
     * Fetch the current user information from the data storage
     *
     * @return array
     */
    protected function _fetchCurrentUser ()
    {
        $serviceAuth = Manager::getService('Authentication');
        $sessionUser = $serviceAuth->getIdentity();
        
        $serviceReader = Manager::getService('Users');
        
        $user = $serviceReader->findById($sessionUser['id'], true);
        
        return $user;
    }

    /**
     * return the groups of the current user.
     *
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
            if (isset($user['id'])) {
                $groupsArray = Manager::getService('Groups')->getListByUserId($user['id']);
            } else {
                $groupsArray = array(
                    'data' => array()
                );
            }
            
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
        if (isset($user['defaultGroup'])) {
            return $user['defaultGroup'];
        } else {
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
        
        $serviceAuth = Manager::getService('Authentication');
        if ($serviceAuth->forceReAuth($user['login'], $oldPass)) {
            $serviceUser = Manager::getService('Users');
            return $serviceUser->changePassword($newPass, $user['version'], $user['id']);
        } else {
            throw new \Rubedo\Exceptions\User('Bad initial password', "Exception77");
        }
    }

    /**
     * Generate a token for the current user
     *
     * @return string
     */
    public function generateToken ()
    {
        $sessionService = Manager::getService('Session');
        $hashService = Manager::getService('Hash');
        
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
        $sessionService = Manager::getService('Session');
        
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
        if (! isset(self::$_readWorkspaces)) {
            $wasFiltered = AbstractCollection::disableUserFilter();
            $groupArray = $this->getGroups();
            $workspaceArray = array();
            foreach ($groupArray as $group) {
                if (isset($group['id'])) {
                    $workspaceArray = array_unique(array_merge($workspaceArray, Manager::getService('Groups')->getReadWorkspaces($group['id'])));
                    $workspaceArray = array_merge($workspaceArray, array_unique(array_merge($workspaceArray, Manager::getService('Groups')->getWriteWorkspaces($group['id']))));
                }
            }
            self::$_readWorkspaces = array_unique($workspaceArray);
            AbstractCollection::disableUserFilter($wasFiltered);
        }
        return self::$_readWorkspaces;
    }

    /**
     * return main workspace of the current user
     *
     * @return array
     */
    public function getMainWorkspace ()
    {
        if (! isset(self::$_mainWorkspace)) {
            $mainGroup = $this->getMainGroup();
            if ($mainGroup == null) {
                return Manager::getService('Workspaces')->findById('global');
            }
            self::$_mainWorkspace = Manager::getService('Groups')->getMainWorkspace($mainGroup);
        }
        return self::$_mainWorkspace;
    }

    public function getMainWorkspaceId ()
    {
        $workspace = $this->getMainWorkspace();
        if ($workspace) {
            return $workspace['id'];
        } else {
            return 'global';
        }
    }

    /**
     * return current user "can write" workspaces
     *
     * @return array
     */
    public function getWriteWorkspaces ()
    {
        if (! isset(self::$_writeWorkspaces)) {
            $groupArray = $this->getGroups();
            $workspaceArray = array();
            
            foreach ($groupArray as $group) {
                $workspaceArray = array_unique(array_merge($workspaceArray, Manager::getService('Groups')->getWriteWorkspaces($group['id'])));
            }
            if (in_array('all', $workspaceArray)) {
                $workspaceArray = array();
                $workspaceList = Manager::GetService("Workspaces")->getWholeList();
                foreach ($workspaceList['data'] as $workspace) {
                    $workspaceArray[] = $workspace['id'];
                }
            }
            
            self::$_writeWorkspaces = $workspaceArray;
        }
        return self::$_writeWorkspaces;
    }

    /**
     *
     * @param boolean $_isInstallerUser            
     */
    public static function setIsInstallerUser ($_isInstallerUser)
    {
        CurrentUser::$_isInstallerUser = $_isInstallerUser;
    }

    public function getLanguage ()
    {
        $user = $this->getCurrentUser();
        
        if (isset($user) && isset($user['language']) && ! empty($user['language'])) {
            $lang = $user['language'];
        } else {
            $lang = 'en';
        }
        
        return $lang;
    }
}
