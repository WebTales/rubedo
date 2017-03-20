<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace RubedoAPI\Services\User;


use Rubedo\Services\Manager;
use RubedoAPI\Traits\LazyServiceManager;

/**
 * Class CurrentUser
 * @package RubedoAPI\Services\User
 */
class CurrentUser extends \Rubedo\User\CurrentUser
{
    use LazyServiceManager;

    /** @var  array */
    static public $token;

    /** @var  \Rubedo\Interfaces\Collection\IUsers */
    protected $usersCollection;

    /**
     * Check if access token exist
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        $accessToken = $this->getAccessToken();
        if (!empty($accessToken))
            return true;
        return parent::isAuthenticated();
    }

    /**
     * Retrieve the current user
     *
     * @return array
     */
    protected function _fetchCurrentUser()
    {
        $serviceReader = Manager::getService('Users');
        $user = $serviceReader->findById($this->getAccessToken()['user']['id']);
        if (!empty($user))
            return $user;
        return parent::_fetchCurrentUser();
    }

    /**
     * Retrieve access_token
     *
     * @return array|null
     */
    protected function getAccessToken()
    {
        if (!isset(static::$token)) {
            if(get_class(Manager::getService('Application')->getRequest()) == "Zend\\Console\\Request") {
                $queryArray = array();
            } else {
                $queryArray = Manager::getService('Application')->getRequest()->getQuery()->toArray();
            }

            if (isset($queryArray['access_token'])) {
                $this->setAccessToken($queryArray['access_token']);
            } elseif (isset($_COOKIE['accessToken'])) {
                $this->setAccessToken($_COOKIE['accessToken']);
            } else {
                return null;
            }
        }
        return static::$token;
    }

    /**
     * Hack to refresh user
     *
     * @param $access_token
     */
    public function setAccessToken($access_token, $token = null)
    {
        static::$token = $this->getUserTokensAPICollection()->findOneByAccessToken($access_token, $token);
        static::$_currentUser = null;
        static::$_currentUserId = null;
    }

    /**
     * Lazy load current user
     *
     * @return array
     */
    public function getCurrentUser()
    {
        if (!isset(static::$_currentUser)) {
            if ($this->isAuthenticated()) {
                $user = $this->_fetchCurrentUser();

                static::$_currentUser = $user;
                if ($user) {
                    $mainWorkspace = $this->getMainWorkspace();
                    if ($mainWorkspace) {
                        $user['defaultWorkspace'] = $mainWorkspace['id'];
                        static::$_currentUser = $user;
                    }
                }
            }
        }
        return static::$_currentUser;
    }
}