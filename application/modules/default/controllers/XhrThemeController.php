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
 * Theme default controller
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class XhrThemeController extends AbstractController {
    /**
     * variable for the Session service
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
	 * Allow to define the current theme
	 */
    public function defineThemeAction() {

        $theme = $this->getRequest()->getParam('theme', "default");
        $this->_session->set('themeCSS', $theme);

        $response['success'] = $this->_session->get('themeCSS');
		
        return $this->_helper->json($response);
    }

}
