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
 * Language Switcher Controller
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class XhrLanguageController extends Zend_Controller_Action
{

    /**
     * Allow to define the current language
     */
    public function defineLanguageAction()
    {
        $forceLocale = $this->getRequest()->getParam('locale', null);
        
        // get current page property
        $this->currentPage = $this->getParam('current-page');
        $currentPage = Manager::getService('Pages')->findById($this->currentPage);
        
        if (is_null($currentPage)) {
            throw new Rubedo\Exceptions\Access('You can not access this page.', "Exception15");
        } else {
            Manager::getService('PageContent')->setCurrentPage($currentPage['id']);
        }
        
        // init browser languages
        $zend_locale = new Zend_Locale(Zend_Locale::BROWSER);
        $browserLanguages = array_keys($zend_locale->getBrowser());
        $locale = Manager::getService('CurrentLocalization')->resolveLocalization($currentPage['site'], $forceLocale, $browserLanguages);
        
        $response['success'] = $locale;
        
        return $this->_helper->json($response);
        
        $language = $this->getRequest()->getParam('language', 'default');
        $this->_session->set('lang', $language);
        
        $response['success'] = $this->_session->get('lang');
        
        return $this->_helper->json($response);
    }
}
