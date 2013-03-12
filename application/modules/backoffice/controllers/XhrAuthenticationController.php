<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
Use Rubedo\Services\Manager;

/**
 * Backoffice asynchroneous authentication Controller
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Backoffice_XhrAuthenticationController extends Zend_Controller_Action
{

    public function init ()
    {
        parent::init();
        
        // init the data access service
        $this->_dataService = Manager::getService(
                'Authentication');
    }

    /**
     * Login or not the user and return a boolean
     *
     * @return bool
     */
    public function loginAction ()
    {
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
    public function logoutAction ()
    {
        $logout = $this->_dataService->clearIdentity();
        
        $response['success'] = true;
        return $this->_helper->json($response);
    }

    public function isSessionExpiringAction ()
    {
        $hasIdentity = Manager::getService('Authentication')->hasIdentity();
        $time = Manager::getService('Authentication')->getExpirationTime();
        $status = $hasIdentity && ($time > 0);
        $this->_helper->json(array(
                'time' => $time,
                'status' => $status
        ));
    }
}
