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

use Rubedo\Services\Manager;

/**
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class RichTextController extends AbstractController
{

    public function indexAction ()
    {
        $blockConfig = $this->params()->fromQuery('block-config', array());
        $content = array();
        if ($blockConfig["contentId"]) {
            $content = Manager::getService('Contents')->findById($blockConfig["contentId"], true, false);
        }
        $output = $this->params()->fromQuery();
        $output['contentId'] = $blockConfig["contentId"];
        $output['text'] = $content["fields"]["body"];
        $output['editorConfig'] = isset($blockConfig['editorConfig']) ? $blockConfig['editorConfig'] : null;
        $output["locale"] = Manager::getService('CurrentLocalization')->getCurrentLocalization();
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/richtext.html.twig");
        
        $css = array();
        $js = array();
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
