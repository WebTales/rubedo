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
namespace Rubedo\Blocks\Controller;

Use Rubedo\Services\Manager;

/**
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class AddthisfollowController extends AbstractController
{

    public function indexAction ()
    {
        $blockConfig = $this->getParam('block-config', array());
        $networks = $blockConfig;
        unset($networks["disposition"]);
        unset($networks["small"]);
        foreach ($networks as $name => $user) {
            $fields["network"] = $name;
            $fields["userId"] = $user;
            $data[] = $fields;
        }
        $output = $this->getAllParams();
        $output['networks'] = $data;
        $output["type"] = isset($blockConfig["disposition"]) ? $blockConfig["disposition"] : "Horizontal";
        $output['small'] = $blockConfig['small'] == 1 ? false : true;
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/addthisfollow.html.twig");
        
        $css = array();
        $js = array(
            '//s7.addthis.com/js/300/addthis_widget.js'
        );
        return $this->_sendResponse($output, $template, $css, $js);
    }
}