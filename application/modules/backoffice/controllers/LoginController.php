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

/**
 * BO Login Controller
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Backoffice_LoginController extends Zend_Controller_Action
{

    /**
     * Variable for Authentication service
     *
     * @param
     *            Rubedo\Interfaces\User\IAuthentication
     */
    protected $_auth;

    /**
     * Init the authentication service
     */
    public function init ()
    {
        $this->_auth = Rubedo\Services\Manager::getService('Authentication');
        $this->getHelper('Layout')->disableLayout();
    }

    /**
     * Redirect the user to the backoffice if he's connected
     */
    public function indexAction ()
    {
        if ($this->_auth->getIdentity()) {
        	$backofficeUrl =$this->view->baseUrl() . '/backoffice/';
        	if($this->getParam('content')){
        		$backofficeUrl .='?content='.$this->getParam('content');
        		
        	}
            $this->_helper->redirector->gotoUrl($backofficeUrl);
        }
        
        $extjsOptions = Zend_Registry::get('extjs');
        
        if (isset($extjsOptions['network']) && $extjsOptions['network'] == 'cdn') {
            $this->view->extJsPath = 'http://cdn.sencha.com/ext-' .
                     $extjsOptions['version'] . '-gpl';
        } else {
            $this->view->extJsPath = $this->view->baseUrl() .
                     '/components/sencha/extjs';
        }
        
        if (! isset($extjsOptions['debug']) || $extjsOptions['debug'] == true) {
            $this->view->extJsScript = 'ext-all-debug.js';
        } else {
            $this->view->extJsScript = 'ext-all.js';
        }
        
        $this->getHelper('Layout')->disableLayout();
    }
}

