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
 * BO Logout controller
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
     * @param Rubedo\Interfaces\User\IAuthentication
     */
    protected $_auth;

    /**
     * Init the authentication service
     */
    public function init ()
    {
        $this->_auth = Manager::getService('Authentication');
    }

    /**
     * Redirect the user to the login page if he's not connected
     */
    public function indexAction ()
    {
        if ($this->_auth->getIdentity()) {
            $result = $this->_auth->clearIdentity();
            
            $response['success'] = true;
        }
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->json($response);
        } else {
            $this->_helper->redirector->gotoUrl("/backoffice/login");
        }
    }

    public function confirmLogoutAction ()
    {}
}

