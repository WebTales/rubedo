<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    yet to be written
 * @version    $Id:
 */

/**
 * Front Office Defautl Controller
 *
 * Invoked when calling front office URL
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class XhrLanguageController extends AbstractController {
    /**
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

        $response['language'] = $this->_session->get('lang');
		
        return $this->_helper->json($response);
    }

}
