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
namespace Rubedo\User\Authentication;

use Rubedo\Interfaces\User\IAuthentication;
use Rubedo\Services\Events;
use Rubedo\Services\Manager;
use Rubedo\User\Authentication\Adapter\CoreAdapter;
use Zend\Authentication\AuthenticationService as ZendAuthenticationService;

/**
 * Current Authentication Service
 *
 * Authenticate user and get information about him
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class AuthenticationService extends ZendAuthenticationService implements IAuthentication
{

    const SUCCESS = 'rubedo_authentication_success';

    const FAIL = 'rubedo_authentication_fail';

    protected static $_authLifetime = 60;

    /**
     * Return the Zend_Auth object and instanciate it if it's necessary
     *
     * @return AuthenticationService
     */

    /**
     * Return the identity of the current user in session
     *
     * @return array
     */
    public function getIdentity()
    {
        if (isset($_COOKIE['accessToken'])) {
            return parent::getIdentity();
        } else {
            return null;
        }
    }

    /**
     * Return true if there is a user connected
     *
     * @return bool
     */
    public function hasIdentity()
    {
        $config = Manager::getService('Application')->getConfig();
        $cookieName = $config['session']['name'];
        if (isset($_COOKIE[$cookieName])) {
            return parent::hasIdentity();
        } else {
            return false;
        }
    }

    /**
     * Unset the session of the current user
     *
     * @return bool
     */
    public function clearIdentity()
    {
        parent::clearIdentity();
        Manager::getService('Session')->getSessionObject()
            ->getManager()
            ->getStorage()
            ->clear();
        $config = Manager::getService('Application')->getConfig();
        $cookieName = $config['session']['name'];
        setcookie($cookieName, null, -1, '/');
    }

    /**
     * Ask a reauthentification without changing the session
     *
     * @param $login It's
     *            the login of the user
     * @param $password It's
     *            the password of the user
     *            
     * @return bool
     */
    public function forceReAuth($login, $password)
    {
        $authAdapter = new CoreAdapter($login, $password);
        $result = $authAdapter->authenticate($authAdapter);
        return $result->isValid();
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\User\IAuthentication::resetExpirationTime()
     */
    public function resetExpirationTime()
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\User\IAuthentication::getExpirationTime()
     */
    public function getExpirationTime()
    {}

    /**
     *
     * @return the $_authLifetime
     */
    public static function getAuthLifetime()
    {
        return static::$_authLifetime;
    }

    /**
     *
     * @param number $_authLifetime            
     */
    public static function setAuthLifetime($_authLifetime)
    {
        static::$_authLifetime = $_authLifetime;
    }

    /**
     * Authenticate the user and set the session
     *
     * @param $login string login of user
     * @param $password string password of the user
     *
     * @throws \Rubedo\Exceptions\User
     * @return bool
     */
    public function coreAuthenticate($login, $password)
    {
        $authAdapter = new CoreAdapter($login, $password);
        $result = parent::authenticate($authAdapter);
        if (! $result->isValid()) {
            Events::getEventManager()->trigger(self::FAIL, null, array(
                'login' => $login,
                'error' => $result->getMessages()
            ));
            Throw new \Rubedo\Exceptions\User(implode(' - ', $result->getMessages()));
        }
        Events::getEventManager()->trigger(self::SUCCESS);
        Manager::getService('CurrentUser')->getToken();
        return $result->isValid();
    }
}
