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
class XhrAuthenticationController extends Zend_Controller_Action
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
        $this->_auth = Rubedo\Services\Manager::getService('Authentication');
    }

    /**
     * Log in the user and set a json response with a boolean and a message
     *
     */
    public function loginAction() {
        $login = $this->getRequest()->getParam('login');
        $password = $this->getRequest()->getParam('password');
        if ($this->getRequest()->isPost()) {

            if (!empty($login) && !empty($password)) {
                $loginResult = $this->_auth->authenticate($login, $password);

                if ($loginResult) {
                    $response['success'] = true;
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Wrong crendentials';
                }
            } else {
                $response['succes'] = false;
                $response['message'] = 'The login and the password should not be empty';
            }
        } else {
            $response['succes'] = false;
            $response['message'] = 'The login and the password should be sent in a POST request !';

        }
        return $this->_helper->json($response);
    }

    /**
     * Log out the user and set a json response with a boolean
     *
     */
    public function logoutAction() {
        $logout = $this->_auth->clearIdentity();

        $response['success'] = true;
        return $this->_helper->json($response);

    }

    /**
     * check if a user is connected and return its login if true (json array)
     */
    public function isLoggedInAction() {
        $currentUserService = Rubedo\Services\Manager::getService('CurrentUser');

        if (!$currentUserService->isAuthenticated()) {
            $response['loggedIn'] = false;
        } else {
            $response['loggedIn'] = true;
            $user = $currentUserService->getCurrentUserSummary();
            $response['username'] = $user['login'];
        }

        return $this->_helper->json($response);
    }

}
