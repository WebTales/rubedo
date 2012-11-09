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
 * Backoffice authentication Controller
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Backoffice_XhrAuthenticationController extends Zend_Controller_Action
{
    public function init(){
		parent::init();
		
		// init the data access service
		$this -> _dataService = Rubedo\Services\Manager::getService('Authentication');
	}

    /**
     * Login or not the user and return a boolean
     *
     * @return bool
     */
    public function loginAction() {
        $login = $_POST['login'];
        $password = $_POST['password'];

        $loginResult = $this->_dataService->authenticate($login, $password);

        if ($loginResult) {
            $response['success'] = true;
            return $this->_helper->json($response);
        } else {
            $response['success'] = false;
            return $this->_helper->json($response);
        }
    }

    /**
     * Logout the user and return a boolean
     *
     * @return bool
     */
    public function logoutAction() {
        $logout = $this->_dataService->clearIdentity();

        $response['success'] = true;
        return $this->_helper->json($response);

    }

}
