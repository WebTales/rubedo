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
class Blocks_ImageController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $blockConfig = $this->getParam('block-config', array());

        $site = $this->getParam('site');
        $output = $this->getAllParams();
        $output['mode'] = isset($blockConfig['mode']) ? $blockConfig['mode'] : 'morph';
        $output['imageLink'] = isset($blockConfig['imageLink']) ? $blockConfig['imageLink'] : null;
        $output['externalURL'] = isset($blockConfig['externalURL']) ? $blockConfig['externalURL']:null;
        $output['imageAlt'] = isset($blockConfig['imageAlt']) ? $blockConfig['imageAlt'] : null;
        $output['imageFile'] = isset($blockConfig['imageFile']) ? $blockConfig['imageFile'] : null;
        $output['imageWidth'] = isset($blockConfig['imageWidth']) ? $blockConfig['imageWidth'] : null;
        $output['imageHeight'] = isset($blockConfig['imageHeight']) ? $blockConfig['imageHeight'] : null;
                                      
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/image.html.twig");
        
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
}
