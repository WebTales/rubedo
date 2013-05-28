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

use Rubedo\Services\Manager;

/**
 * Controller providing action concerning the current user
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_CurrentUserController extends Zend_Controller_Action
{

    /**
     * Variable for Authentication service
     *
     * @param 	Rubedo\Interfaces\User\IAuthentication
     */
    protected $_auth;
	
	/**
	 * Variable for currentUser service
	 */
	protected $_currentUserService;
	
	/**
	 * Array with the read only actions
	 */
	protected $_readOnlyAction = array('index', 'get-token');

    /**
     * Initialise the controller
     */
    public function init() {
        parent::init();

        $this->_auth = Manager::getService('Authentication');
		$this->_currentUserService = Manager::getService('CurrentUser');
		
		// refuse write action not send by POST
        if (!$this->getRequest()->isPost() && !in_array($this->getRequest()->getActionName(), $this->_readOnlyAction)) {
            throw new \Rubedo\Exceptions\Access("You can't call a write action with a GET request", "Exception5");
        } else {
        	if(!in_array($this->getRequest()->getActionName(), $this->_readOnlyAction)){
        		$user = Manager::getService('Session')->get('user');
        		$token = $this->getRequest()->getParam('token');
				
				if($token !== $user['token']){
					throw new \Rubedo\Exceptions\Access("The token given in the request doesn't match with the token in session", "Exception6");
				}
        	}
        }
    }

    /**
     * Get informations of the user
     *
     * @return array
     */
    public function indexAction() {
        $currentUserService = Manager::getService('CurrentUser');
        $response = $currentUserService->getCurrentUser();

        if (!is_null($response)) {
            $newResponse['success'] = true;
            $newResponse['data'] = $response;
        } else {
            $newResponse['sucess'] = false;
        }

        $this->_helper->json($newResponse);
    }

    /**
     * Update the current values for the user
     */
    public function updateAction() {
        $usersService = Manager::getService('Users');
        $data = $this->getRequest()->getParam('data');

        if (!is_null($data)) {
            $insertData = Zend_Json::decode($data);
            if (is_array($insertData)) {
                $result = $this->_auth->getIdentity();
                if ($result) {
                    $userId = $result['id'];

                    if ($userId === $insertData['id']) {
                        $returnArray = $usersService->update($insertData);
                    } else {
                        $returnArray = array('success' => false, 'message' => 'Bad id');
                    }
                } else {
                    $returnArray = array('success' => false, "msg" => 'No user connected');
                }
            } else {
                $returnArray = array('success' => false, "msg" => 'Not an array');
            }
        } else {
            $returnArray = array('success' => false, "msg" => 'No Data');
        }
        if (!$returnArray['success']) {
            $this->getResponse()->setHttpResponseCode(500);
        }
        $this->_helper->json($returnArray);
    }

	/**
	 * Action to change the current user password
	 * 
	 */
    public function changePasswordAction() {
        $oldPassword = $this->getRequest()->getParam('oldPassword');
        $newPassword = $this->getRequest()->getParam('newPassword');

        if (is_string($oldPassword) && is_string($newPassword)) {
            $currentUserService = Manager::getService('CurrentUser');
            $result = $currentUserService->changePassword($oldPassword, $newPassword);
        } else {
            $result = false;
        }
        $this->_helper->json(array('success' => $result));
    }
	
	/**
	 * Return a json with the token of the current user
	 */
	public function getTokenAction() {
		$response = array();
		$response['token'] = $this->_currentUserService->getToken();
		
		if(mb_strlen($response['token']) != 128 && !ctype_alnum($response['token'])){
			$this->getResponse()->setHttpResponseCode(500);
		} else {
			$this->_helper->json($response);
		}
	}

}
