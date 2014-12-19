<?php

/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
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
    protected function loadExtApps()
    {
        $this->viewData = array();
        $config = Manager::getService("config");
        $extjsOptions = array();
        $extjsOptions['debug'] = isset($config['rubedo_config']['extDebug']) ? $config['rubedo_config']['extDebug'] : "0";
        $extjsOptions['addECommerce'] = isset($config['rubedo_config']['addECommerce']) ? $config['rubedo_config']['addECommerce'] : "1";
        $extjsOptions['activateMagic'] = isset($config['rubedo_config']['activateMagic']) ? $config['rubedo_config']['activateMagic'] : "0";
        $this->viewData['baseUrl'] = $this->request->getBasePath();


        $this->viewData['extJsPath'] = $this->request->getBasePath() . '/components/sencha/extjs';
        // setting user language for loading proper extjs locale file
        $this->viewData['userLang'] = 'en'; // default value
        $currentUserLanguage = Manager::getService('CurrentUser')->getLanguage();
        if (!empty($currentUserLanguage)) {
            $this->viewData['userLang'] = $currentUserLanguage;
        }

        if ($extjsOptions['debug'] == "1") {
            $this->viewData['extJsScript'] = 'ext-all-debug.js';
        } else {
            $this->viewData['extJsScript'] = 'ext-all.js';
        }
        if ($extjsOptions['addECommerce'] == "1") {
            $this->viewData['addECommerce'] = true;
        } else {
            $this->viewData['addECommerce'] = false;
        }
        if ($extjsOptions['activateMagic'] == "1") {
            $this->viewData['activateMagic'] = true;
        } else {
            $this->viewData['activateMagic'] = false;
        }
        $viewModel = new ViewModel($this->viewData);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
}