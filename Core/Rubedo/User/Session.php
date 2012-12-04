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

use Rubedo\Interfaces\User\ISession;

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
     * @return object
     */
    public function getSessionObject() {
        if (!$this->_sessionObject instanceof \Zend_Session_Namespace) {
            $this->_sessionObject = new \Zend_Session_Namespace(static::$_sessionName);

        }
        return $this->_sessionObject;
    }
	
	 /**
     * Set the session object with name and value params
     *
	  * @param string $name
	  * @param string $value
     */
    public function set($name, $value) {
        $this->getSessionObject()->$name = $value;
    }

    /**
     * Return the session object requested by $name
     * 
     * @param string $name
	 * @param string $defaultValue
     * @return string
     */
    public function get($name,$defaultValue = null) {
		if(!isset($this->getSessionObject()->$name)){
			$this->getSessionObject()->$name = $defaultValue;
			return $defaultValue;
		}else{
			return $this->getSessionObject()->$name;
		}
    }

}
