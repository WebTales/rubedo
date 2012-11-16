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
     * Initialise the controller
     */
    public function init() {
        parent::init();

        $this->_auth = \Rubedo\Services\Manager::getService('Authentication');
    }

    /**
     * Get informations of the user
     *
     * @return array
     */
    public function indexAction() {
        $currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
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
        $usersService = \Rubedo\Services\Manager::getService('Users');
        $data = $this->getRequest()->getParam('data');

        if (!is_null($data)) {
            $insertData = Zend_Json::decode($data);
            if (is_array($insertData)) {
                $result = $this->_auth->getIdentity();
                if ($result) {
                    $userId = $result['id'];

                    if ($userId === $insertData['id']) {
                        $returnArray = $usersService->update($insertData, true);
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
            $currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
            $result = $currentUserService->changePassword($oldPassword, $newPassword);
        } else {
            $result = false;
        }
        $this->_helper->json(array('success' => $result));

    }

}
