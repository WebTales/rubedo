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
                self::$_currentUser = $this->fetchCurrentUser();
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
    public function fetchCurrentUser() {		
    	$serviceAuth = \Rubedo\Services\Manager::getService('Authentication');	
    	$sessionUser = $serviceAuth->getIdentity();
		
		$serviceReader = \Rubedo\Services\Manager::getService('MongoDataAccess');
		$serviceReader->init('Users');
		$serviceReader->addToExcludeFieldList(array('password'));
		
		$user = $serviceReader->findById($sessionUser['id']);
		return $user;
    }

}
