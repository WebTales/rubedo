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
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class FooterController extends AbstractController
{

    public function indexAction ()
    {
        $output = $this->getAllParams();
        $output["items"] = null;
        
        // $template = manager::getService('template')->findTemplateFileFor('carrousel');
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/footer.html.twig");
        
        $css = array();
        $js = array();
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
