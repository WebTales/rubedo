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

use Rubedo\Interfaces\User\ISession;
use Zend\Session\Container as SessionContainer;
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
class Session implements ISession
{

    protected static $_sessionName = 'Default';

    protected $_sessionObject = null;

    /**
     * Returns a session object
     *
     * @return SessionContainer
     */
    public function getSessionObject ()
    {
        if (! $this->_sessionObject instanceof SessionContainer) {
            $this->_sessionObject = new SessionContainer(static::$_sessionName);
        }
        return $this->_sessionObject;
    }

    /**
     * Set the session object with name and value params
     *
     * @param string $name            
     * @param string $value            
     */
    public function set ($name, $value)
    {
        $this->getSessionObject()->$name = $value;
    }

    /**
     * Return the session object requested by $name
     *
     * @param string $name
     *            name of the parameter
     * @param mixed $defaultValue
     *            default value in case of not set parameter in session
     * @return mixed value in session
     */
    public function get($name, $defaultValue = null)
    {
        $config = Manager::getService('Application')->getConfig();
        $cookieName = $config['session']['name'];
        if (isset($_COOKIE[$cookieName])) {
            if (! isset($this->getSessionObject()->$name)) {
                $this->getSessionObject()->$name = $defaultValue;
                return $defaultValue;
            } else {
                return $this->getSessionObject()->$name;
            }
        } else{
            return $defaultValue;
        }
    }
}
