<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    yet to be written
 * @version    $Id:
 */

/**
 * Authentication Default Controller
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class XhrAuthenticationController extends AbstractController {
    /**
     * Variable for Authentication service
	 * 
	 * @param 	Rubedo\Interfaces\User\IAuthentication
     */
    protected $_auth;
	
	/**
	 * Init the authentication service
	 */
    public function init() {
        $this->_auth = Rubedo\Services\Manager::getService('Authentication');
    }
	
	/**
	 * Login or not the user and return a boolean
	 * 
	 * @return bool
	 */
	public function loginAction(){
		if(isset($_GET['login']) && isset($_GET['password'])){
			$login = $_GET['login'];
			$password = $_GET['password'];
			if(!empty($login) && !empty($password)){
				$loginResult = $this->_auth->authenticate($login, $password);
				
				if($loginResult){
					$response['success'] = true;
					return $this->_helper->json($response);
				}else{
					$response['success'] = false;
					return $this->_helper->json($response);
				}
			} else {
				$response['succes'] = false;
				$response['message'] = 'The login and the password should not be empty';
				return $this->_helper->json($response);
			}
		} else {
			$response['succes'] = false;
			$response['message'] = 'The login and the password should be sent in a POST request !';
			return $this->_helper->json($response);
		}
	}
	
	/**
	 * Logout the user and return a boolean
	 * 
	 * @return bool
	 */
	public function logoutAction(){
		$logout = $this->_auth->clearIdentity();
		
		if($logout){
			$response['success'] = true;
			return $this->_helper->json($response);
		}else{
			$response['success'] = false;
			return $this->_helper->json($response);
		}
	}

}