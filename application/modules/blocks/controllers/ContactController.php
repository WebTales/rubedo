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

require_once ('AbstractController.php');

/**
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_ContactController extends Blocks_AbstractController
{

    protected $_defaultTemplate = 'contact';
    
    public function indexAction ()
    {
    	$blockConfig = $this->getRequest()->getParam('block-config');
    	
    	if(isset($blockConfig['captcha'])){
    		$contactForm = new Application_Form_Contact(null, $blockConfig['captcha']);
    	} else {
    		$contactForm = new Application_Form_Contact();
    	}
    	
        $output["blockConfig"]=$blockConfig;
        
        if (isset($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
                    "blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
                    "blocks/" . $this->_defaultTemplate . ".html.twig");
        }
        
        $output['contactForm'] = $contactForm;
        
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
}
