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

use Rubedo\Interfaces\User\IAuthentication;

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
	 * embed zend_auth
	 * 
	 * @param Zend_Auth
	 */
	static protected $_zendAuth;
	
	/**
	 * Return the Zend_Auth object and instanciate it if it's necessary
	 * 
	 * @return Zend_Auth object
	 */
	protected function _getZendAuth(){
		if(!isset(static::$_zendAuth)){
			static::$_zendAuth = \Zend_Auth::getInstance();
		}
		
		return static::$_zendAuth;
	}
	
	/**
	 * Authenticate the user and set the session
	 * 
	 * @param $login It's the login of the user
	 * @param $password It's the password of the user
	 * 
	 * @return bool
	 */
    public function authenticate($login, $password){
    	$authAdapter = new \Rubedo\User\AuthAdapter($login,$password);
		$result = $this->_getZendAuth()->authenticate($authAdapter);
    	return $result->isValid();
    }
	
	/**
	 * Return the identity of the current user in session
	 * 
	 * @return array
	 */
	public function getIdentity(){
    	return $this->_getZendAuth()->getIdentity();
    }
	
	/**
	 * Return true if there is a user connected
	 * 
	 * @return bool
	 */
	public function hasIdentity(){
    	return $this->_getZendAuth()->hasIdentity();
    }
	
	/**
	 * Unset the session of the current user
	 * 
	 * @return bool
	 */
	public function clearIdentity(){
    	return $this->_getZendAuth()->clearIdentity();
    }
	
	/**
	 * Ask a reauthentification without changing the session
	 * 
	 * @param $login It's the login of the user
	 * @param $password It's the password of the user
	 * 
	 * @return bool
	 */
	public function forceReAuth($login, $password){
    	$authAdapter = new \Rubedo\User\AuthAdapter($login,$password);
		$result = $authAdapter->authenticate($authAdapter);
    	return $result->isValid();
    }

}
