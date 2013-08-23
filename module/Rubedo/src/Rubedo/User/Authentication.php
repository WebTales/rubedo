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

use Rubedo\Interfaces\User\IAuthentication;
use Zend\Authentication\AuthenticationService;
use Rubedo\User\AuthAdapter;
use Zend\Session\Container as SessionContainer;
use Rubedo\Services\Manager;

/**
 * Current Authentication Service
 *
 * Authenticate user and get information about him
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Authentication implements IAuthentication
{

    /**
     * Authentication service of ZF
     *
     * @param
     *            AuthenticationService
     *            
     */
    protected static $zendAuth;

    protected static $_authLifetime = 60;

    /**
     * Return the Zend_Auth object and instanciate it if it's necessary
     *
     * @return AuthenticationService
     */
    protected function getZendAuth ()
    {
        if (! isset(static::$zendAuth)) {
            static::$zendAuth = new AuthenticationService();
        }
        
        return static::$zendAuth;
    }

    /**
     * Authenticate the user and set the session
     *
     * @param $login It's
     *            the login of the user
     * @param $password It's
     *            the password of the user
     *            
     * @return bool
     */
    public function authenticate ($login, $password)
    {
        $authAdapter = new AuthAdapter($login, $password);
        $result = $this->getZendAuth()->authenticate($authAdapter);
        if (! $result->isValid()) {
            Throw new \Rubedo\Exceptions\User(implode(' - ', $result->getMessages()));
        }
        return $result->isValid();
    }

    /**
     * Return the identity of the current user in session
     *
     * @return array
     */
    public function getIdentity ()
    {
        return $this->getZendAuth()->getIdentity();
    }

    /**
     * Return true if there is a user connected
     *
     * @return bool
     */
    public function hasIdentity ()
    {
        return $this->getZendAuth()->hasIdentity();
    }

    /**
     * Unset the session of the current user
     *
     * @return bool
     */
    public function clearIdentity ()
    {
        $this->getZendAuth()->clearIdentity();
        Manager::getService('Session')->getSessionObject()->getManager()
            ->getStorage()
            ->clear();
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
    public function forceReAuth ($login, $password)
    {
        $authAdapter = new AuthAdapter($login, $password);
        $result = $authAdapter->authenticate($authAdapter);
        return $result->isValid();
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\User\IAuthentication::resetExpirationTime()
     */
    public function resetExpirationTime ()
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\User\IAuthentication::getExpirationTime()
     */
    public function getExpirationTime ()
    {}

    /**
     *
     * @return the $_authLifetime
     */
    public static function getAuthLifetime ()
    {
        return Authentication::$_authLifetime;
    }

    /**
     *
     * @param number $_authLifetime            
     */
    public static function setAuthLifetime ($_authLifetime)
    {
        Authentication::$_authLifetime = $_authLifetime;
    }
}
