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
namespace Rubedo\Frontoffice\Controller;

use Rubedo\Blocks\Controller\AuthenticationController as AuthBlock;
use Rubedo\Services\Manager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Authentication Default Controller
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class XhrAuthenticationController extends AbstractActionController
{

    /**
     * Variable for Authentication service
     *
     * @var \Rubedo\Interfaces\User\IAuthentication
     */
    protected $_auth;

    /**
     * @var \Rubedo\Interfaces\Internationalization\ITranslate
     */
    protected $translateService;

    /**
     * Init the authentication service
     */
    public function __construct()
    {
        $this->_auth = Manager::getService('Authentication');
        $this->translateService = Manager::getService('Translate');
    }

    /**
     * Log in the user and set a json response with a boolean and a message
     */
    public function loginAction()
    {
        $login = $this->params()->fromPost('login');
        $password = $this->params()->fromPost('password');
        if ($this->getRequest()->isPost()) {
            if (! empty($login) && ! empty($password)) {
                try {
                    $this->_auth->authenticate($login, $password);
                    $response['success'] = true;
                } catch (\Exception $e) {
                    $response['success'] = false;
                    $response['msg'] = 'Blocks.Auth.Xhr.Login.CredentialsWrong';
                }
            } else {
                $response['success'] = false;
                $response['msg'] = 'Blocks.Auth.Xhr.Login.CredentialsEmpty';
            }
        } else {
            $response['succes'] = false;
            $response['msg'] = 'Blocks.Auth.Xhr.Login.POSTRequired';
        }

        if (isset($response['msg'])) {
            $response['msg'] = $this->translateService->translate($response['msg']);
        }
        return new JsonModel($response);
    }

    /**
     * Log out the user and set a json response with a boolean
     */
    public function logoutAction()
    {
        $this->_auth->clearIdentity();
        
        $response['success'] = true;
        return new JsonModel($response);
    }

    /**
     * check if a user is connected and return its login if true (json array)
     */
    public function isLoggedInAction()
    {
        /**
         * @var $currentUserService \Rubedo\Interfaces\User\ICurrentUser
         */
        $currentUserService = Manager::getService('CurrentUser');
        
        if (!$currentUserService->isAuthenticated()) {
            $response['loggedIn'] = false;
        } else {
            $response['loggedIn'] = true;
            $user = $currentUserService->getCurrentUserSummary();
            $response['username'] = $user['login'];
        }
        
        return new JsonModel($response);
    }

    public function sendTokenAction()
    {
        $params = $this->params()->fromPost();
        $output = array();
        $blockController = new AuthBlock();
        try {
            $params = $blockController->xhrRecoverPassword($params);
            $output['success'] = true;
            $output['msg'] = 'Blocks.Auth.Email.SentAuto';
            if (!isset($params['user'])) {
                $output['success'] = false;
                $output['msg'] = 'Blocks.Auth.Xhr.SendToken.UserNotExist';
            }
        } catch (\Exception $e) {
            $output['success'] = false;
            $output['msg'] = 'Blocks.Auth.Xhr.SendToken.MailNotSent';
        }
        if (isset($output['msg'])) {
            $output['msg'] = $this->translateService->translate($output['msg']);
        }
        return new JsonModel($output);
    }

    public function changePasswordAction()
    {
        $params = $this->params()->fromPost();
        $output = array();
        $blockController = new AuthBlock();
        try {
            $params = $blockController->xhrChangePassword($params);
            $output['success'] = isset($params['success']) && $params['success'] == true;
            $output['msg'] = $output['success'] ? 'Blocks.Auth.Email.SentAuto' : $params['error'];
        } catch (\Exception $e) {
            $output['success'] = false;
            $output['msg'] = 'Blocks.Auth.Xhr.SendToken.MailNotSent';
        }

        if (isset($output['msg'])) {
            $output['msg'] = $this->translateService->translate($output['msg']);
        }
        return new JsonModel($output);
    }

    public function setFingerprintAction(){
        $fingerprint=$this->params()->fromPost("fingerprint", null);
        if (!$fingerprint){
            return new JsonModel(array("success"=>false));
        }
        Manager::getService("Session")->set("fingerprint",$fingerprint);
        return new JsonModel(array("success"=>true));
    }
}
