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
class Blocks_TextController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $blockConfig = $this->getParam('block-config', array());
        $content=array();
        if ($blockConfig["contentId"]){
            $content = Manager::getService('Contents')->findById($blockConfig["contentId"],true,false);
        }
        $output = $this->getAllParams();
        $output['contentId'] = $blockConfig["contentId"];
        $output['text'] = $content["fields"]["body"];
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/text.html.twig");
        
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
}
