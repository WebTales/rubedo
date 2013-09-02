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

use Zend\Mvc\Controller\AbstractActionController;
/**
 * Theme default controller
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class XhrThemeController extends AbstractActionController
{

    /**
     * variable for the Session service
     *
     * @param
     *            Rubedo\Interfaces\User\ISession
     */
    protected $_session;

    /**
     * Init the session service
     */
    public function init ()
    {
        $this->_session = Rubedo\Services\Manager::getService('Session');
    }

    /**
     * Allow to define the current theme
     */
    public function defineThemeAction ()
    {
        $theme = $this->getRequest()->getParam('theme', "default");
        $this->_session->set('themeCSS', $theme);
        
        $response['success'] = $this->_session->get('themeCSS');
        
        return $this->_helper->json($response);
    }
}
