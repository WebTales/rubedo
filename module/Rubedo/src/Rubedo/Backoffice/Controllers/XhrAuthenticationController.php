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
namespace Rubedo\Backoffice\Controller;


use Manager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Backoffice asynchroneous authentication Controller
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class XhrAuthenticationController extends AbstractActionController
{

    public function __construct()
    {        
        // init the data access service
        $this->_dataService = Manager::getService('Authentication');
    }

    /**
     * Login or not the user and return a boolean
     *
     * @return bool
     */
    public function loginAction()
    {
        $login = $this->params()->fromPost('login');//$_POST['login'];
        $password = $this->params()->fromPost('password');//$_POST['password'];
        
        $loginResult = $this->_dataService->authenticate($login, $password);
        
        if ($loginResult) {
            $response['success'] = true;
        } else {
            $response['success'] = false;
        }
        return new JsonModel($response);
    }

    /**
     * Logout the user and return a boolean
     *
     * @return bool
     */
    public function logoutAction()
    {
        $this->_dataService->clearIdentity();
        
        $response['success'] = true;
        return new JsonModel($response);
    }

    public function isSessionExpiringAction()
    {
        $hasIdentity = Manager::getService('Authentication')->hasIdentity();
        $time = Manager::getService('Authentication')->getExpirationTime();
        $status = $hasIdentity && ($time > 0);
        return new JsonModel(array(
            'time' => $time,
            'status' => $status
        ));
    }
}
