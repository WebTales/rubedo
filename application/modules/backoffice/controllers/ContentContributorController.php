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

require_once ('AbstractExtLoaderController.php');

/**
 * Controller for handling FO contributions
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Backoffice_ContentContributorController extends Backoffice_AbstractExtLoaderController
{

    public function indexAction ()
    {
        $this->_auth = Rubedo\Services\Manager::getService('Authentication');
        
        if (! $this->_auth->getIdentity()) {
            $this->_helper->redirector->gotoUrl("/backoffice/login");
        }
        
        $this->loadExtApps();
    }
}

