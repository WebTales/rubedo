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
use Rubedo\Services\Manager;

/**
 * Logout Defautl Controller
 * 
 * Invoked when calling /backoffice/logout URL
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Backoffice_LogoutController extends Zend_Controller_Action
{

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
        $this->_auth = Manager::getService('Authentication');
    }
	
	/**
	 * Redirect the user to the login page if he's not connected
	 */
    public function indexAction ()
    {
        if($this->_auth->getIdentity()){
			$result = $this->_auth->clearIdentity();

			$response['success'] =true;
			
			
		}
		
		if($this->getRequest()->isXmlHttpRequest()){
		    $this->_helper->json($response);
		}else{
		    $this->_helper->redirector->gotoUrl("/backoffice/login");
		}
    }
    
    public function confirmLogoutAction(){
        
    }
	
}

