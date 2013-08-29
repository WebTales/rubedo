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
class Blocks_AddthisController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $blockConfig = $this->getParam('block-config', array());
        $output = $this->getAllParams();
        $output['type'] = $blockConfig["disposition"];
        $output['small'] = $blockConfig['small'] == 1 ? false : true;
        $output['like'] = isset($blockConfig['like']) ? $blockConfig['like'] : false;
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/addthis.html.twig");
        $css = array();
        $js = array(
            '//s7.addthis.com/js/300/addthis_widget.js'
        );
        $this->_sendResponse($output, $template, $css, $js);
    }
}

