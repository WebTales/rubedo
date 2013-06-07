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

require_once ('AbstractExtLoaderController.php');

/**
 * Back Office Default Controller
 *
 * Invoked when calling /backoffice URL
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Backoffice_IndexController extends Backoffice_AbstractExtLoaderController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $this->_auth = Manager::getService('Authentication');
        
        if (! $this->_auth->getIdentity()) {
            $backofficeUrl = $this->_helper->url('index', 'login', 'backoffice');
            if ($this->getParam('content')) {
                $backofficeUrl .= '?content=' . $this->getParam('content');
            }
            $this->_helper->redirector->gotoUrl($backofficeUrl);
        }
        
        if (! Manager::getService('Acl')->hasAccess('ui.backoffice')) {
            $this->_helper->redirector->gotoUrl($this->_helper->url('confirm-logout', 'logout', 'backoffice'));
        }
        
        $this->loadExtApps();
    }
}

