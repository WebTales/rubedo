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

use Rubedo\Services\Manager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


/**
 * Abstract ext loader controller
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */

abstract class AbstractExtLoaderController extends AbstractActionController
{
        
    /**
     * Load ext apps for the backoffice and the front office
     */
    protected function loadExtApps() {
        $this->viewData = array();
        $extjsOptions = array();//Zend_Registry::get('extjs');
        $this->viewData['baseUrl'] = $this->request->getBasePath();
        
        if (isset($extjsOptions['network']) && $extjsOptions['network'] == 'cdn') {
            $this->viewData['extJsPath'] = 'http://cdn.sencha.com/ext-' . $extjsOptions['version'] . '-gpl';
        } else {
            $this->viewData['extJsPath'] = $this->request->getBasePath().'/components/sencha/extjs';
        }
        // setting user language for loading proper extjs locale file
        $this->viewData['userLang'] = 'en'; // default value
        $currentUserLanguage = Manager::getService('CurrentUser')->getLanguage();
        if (! empty($currentUserLanguage)) {
            $this->viewData['userLang'] = $currentUserLanguage;
        }
        
        if (! isset($extjsOptions['debug']) || $extjsOptions['debug'] == true) {
            $this->viewData['extJsScript'] = 'ext-all-debug.js';
        } else {
            $this->viewData['extJsScript'] = 'ext-all.js';
        }
        $viewModel = new ViewModel($this->viewData);
        $viewModel->setTerminal(true);
        
        return $viewModel;
    }
}