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
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_FormsController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
    	$blockConfig = $this->getParam('block-config', array());
    	
    	$form=Manager::getService('Forms')->findById($blockConfig["formId"]);
    	\Zend_Debug::dump($form);die();
    	$output["form"]["pages"] = Zend_Json::encode($form["formPages"]);
    	$output["form"]["id"]=$form["id"];
    	$template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/form.html.twig");
    	$css = array();
    	$js = array('/components/st3ph/easyWizard/lib/jquery.easyWizard.js','/templates/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/easyForm.js"));
    	$this->_sendResponse($output, $template, $css, $js);
     
    }
}
