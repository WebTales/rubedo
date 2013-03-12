<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
Use Rubedo\Services\Manager;

require_once ('AbstractController.php');
/**
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_TwigController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction() {
        $session = Manager::getService('Session');
        $lang = $session->get('lang', 'fr');

        $output = $this->getAllParams();
		
		$templateName = $this->getRequest()->getParam('template','block.html');
		
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath($templateName);
        if(!is_file(Manager::getService('FrontOfficeTemplates')->getTemplateDir().'/'.$template)){
               throw new Rubedo\Exceptions\Server('File '.Manager::getService('FrontOfficeTemplates')->getTemplateDir().'/'.$template.' does not exists');
        }
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }

}
