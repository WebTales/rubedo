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

use Rubedo\Services\Manager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Language Switcher Controller
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class XhrLanguageController extends AbstractActionController
{

    /**
     * Allow to define the current language
     */
    public function defineLanguageAction()
    {
        $forceLocale = $this->params()->fromQuery('locale', null);
        
        // get current page property
        $this->currentPage = $this->params()->fromQuery('current-page');
        $currentPage = Manager::getService('Pages')->findById($this->currentPage);
        
        if (is_null($currentPage)) {
            throw new \Rubedo\Exceptions\Access('You can not access this page.', "Exception15");
        } else {
            Manager::getService('PageContent')->setCurrentPage($currentPage['id']);
        }
        
        $locale = Manager::getService('CurrentLocalization')->resolveLocalization($currentPage['site'], $forceLocale);
        $domain = $this->getRequest()->getHeader('host');
        if ($domain) {
            $languageCookie = setcookie('locale', $locale, strtotime('+1 year'), '/', $domain);
        }
        
        $response['success'] = $locale;
        return new JsonModel($response);
    }
}
