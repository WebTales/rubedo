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
	
	protected function _getZendAuth(){
		if(!isset(static::$_zendAuth)){
			static::$_zendAuth = \Zend_Auth::getInstance();
		}
		
		return static::$_zendAuth = new \Zend_Auth();
	}

    public function authenticate($login, $password){
    	$authAdapter = new \Rubedo\User\AuthAdapter($login,$password);
		$result = $this->_getZendAuth()->authenticate($authAdapter);
    	return $result->isValid();
    }
	
	public function getIdentity(){
    	return $this->_getZendAuth()->getIdentity();
    }
	
	public function hasIdentity(){
    	return $this->_getZendAuth()->hasIdentity();
    }
	
	public function clearIdentity(){
    	return $this->_getZendAuth()->clearIdentity();
    }

}
