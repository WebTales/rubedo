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

    public function authenticate($login, $password){
    	return true;
    }
	
	public function getIdentity(){
    	return "Mickael Goncalves";
    }
	
	public function hasIdentity(){
    	return true;
    }
	
	public function clearIdentity(){
    	return true;
    }

}
