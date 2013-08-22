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

use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

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
class CurrentUserController extends AbstractActionController
{

    /**
     * Variable for Authentication service
     *
     * @param
     *            Rubedo\Interfaces\User\IAuthentication
     */
    protected $_auth;

    /**
     * Variable for currentUser service
     */
    protected $_currentUserService;

    /**
     * Initialise the controller
     */
    public function __construct()
    {
        $this->_auth = Manager::getService('Authentication');
        $this->_currentUserService = Manager::getService('CurrentUser');
    }

    /**
     * Get informations of the user
     *
     * @return array
     */
    public function indexAction()
    {
        $currentUserService = Manager::getService('CurrentUser');
        $response = $currentUserService->getCurrentUser();
        
        if (! is_null($response)) {
            $newResponse['success'] = true;
            $newResponse['data'] = $response;
        } else {
            $newResponse['sucess'] = false;
        }
        return new JsonModel($newResponse);
    }

    /**
     * Update the current values for the user
     */
    public function updateAction()
    {
        $usersService = Manager::getService('Users');
        $data = $this->params()->fromPost('data');
        
        if (! is_null($data)) {
            $insertData = Json::decode($data, Json::TYPE_ARRAY);
            if (is_array($insertData)) {
                $result = $this->_auth->getIdentity();
                if ($result) {
                    $userId = $result['id'];
                    
                    if ($userId === $insertData['id']) {
                        $returnArray = $usersService->update($insertData);
                    } else {
                        $returnArray = array(
                            'success' => false,
                            'message' => 'Bad id'
                        );
                    }
                } else {
                    $returnArray = array(
                        'success' => false,
                        "msg" => 'No user connected'
                    );
                }
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'Not an array'
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => 'No Data'
            );
        }
        if (! $returnArray['success']) {
            $this->getResponse()->setStatusCode(500);
        }
        return new JsonModel($returnArray);
    }

    /**
     * Action to change the current user password
     */
    public function changePasswordAction()
    {
        $oldPassword = $this->params()->fromPost('oldPassword');
        $newPassword = $this->params()->fromPost('newPassword');
        
        if (is_string($oldPassword) && is_string($newPassword)) {
            $currentUserService = Manager::getService('CurrentUser');
            $result = $currentUserService->changePassword($oldPassword, $newPassword);
        } else {
            $result = false;
        }
        return new JsonModel($result);
    }

    /**
     * Return a json with the token of the current user
     */
    public function getTokenAction()
    {
        $response = array();
        $response['token'] = $this->_currentUserService->getToken();
        
        if (mb_strlen($response['token']) != 128 && ! ctype_alnum($response['token'])) {
            $this->getResponse()->setStatusCode(500);
        } else {
            return new JsonModel($response);
        }
    }
}
