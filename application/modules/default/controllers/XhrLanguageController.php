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
 * Language Default Controller
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class XhrLanguageController extends Zend_Controller_Action {
    /**
     * Variable for Session service
	 * 
	 * @param 	Rubedo\Interfaces\User\ISession
     */
    protected $_session;
	
	/**
	 * Init the session service
	 */
    public function init() {
        $this->_session = Rubedo\Services\Manager::getService('Session');
    }
	
	/**
	 * Allow to define the current language
	 */
    public function defineLanguageAction() {
        $language = $this->getRequest()->getParam('language', 'default');
        $this->_session->set('lang', $language);

        $response['success'] = $this->_session->get('lang');
		
        return $this->_helper->json($response);
    }

}
