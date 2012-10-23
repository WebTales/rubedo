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
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
class Authentication implements IAuthentication
{

	function Authentication(){
		$this->_session = Rubedo\Services\Manager::getService('Session');
	}

    public function authenticate($login, $password){
    	if($login === "admin" && $password === "pwd"){
    		$this->_session->set('user', 'Mickael Goncalves');
    		return true;
    	}
    }
	
	public function getIdentity(){
    	return $this->_session->get('user');
    }
	
	public function hasIdentity(){
    	if(!empty($this->_session->get('user'))){
    		return true;
    	}else{
    		return false;
    	}
    }
	
	public function clearIdentity(){
    	$this->_session->set('user', '');
    }

}
