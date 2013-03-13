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
Use Rubedo\Services\Manager;

/**
 * Back Office Default Controller
 *
 * Invoked when calling /backoffice URL
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Backoffice_IndexController extends Zend_Controller_Action
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $this->_auth = Manager::getService('Authentication');
        
        if (! $this->_auth->getIdentity()) {
            $this->_helper->redirector->gotoUrl(
                    $this->_helper->url('index', 'login', 'backoffice'));
        }
        
        if (! Manager::getService('Acl')->hasAccess('ui.backoffice')) {
            $this->_helper->redirector->gotoUrl(
                    $this->_helper->url('confirm-logout', 'logout', 
                            'backoffice'));
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

