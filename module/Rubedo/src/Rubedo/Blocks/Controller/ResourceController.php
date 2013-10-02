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
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class ResourceController extends AbstractController
{

    protected $_defaultTemplate = 'resource';
    
    /*
     * (non-PHPdoc) @see Blocks_AbstractController::init()
     */
    public function init ()
    {
        // TODO Auto-generated method stub
    }

    public function indexAction ()
    {
        $blockConfig = $this->params()->fromQuery('block-config', array());
        $output = $this->params()->fromQuery();
        
        if ((isset($blockConfig['introduction'])) && ($blockConfig['introduction'] != "")) {
            $content = Manager::getService('Contents')->findById($blockConfig["introduction"], true, false);
            $output['contentId'] = $blockConfig["introduction"];
            $output['text'] = $content["fields"]["body"];
            $output["locale"] = isset($content["locale"]) ? $content["locale"] : null;
        }
        if (isset($blockConfig['documentId'])) {
            $output['downloadUrl'] = Manager::getService('Url')->mediaUrl($blockConfig['documentId'],true);
        }
        
        if (isset($blockConfig['displayType']) && ! empty($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $this->_defaultTemplate . ".html.twig");
        }
        
        $css = array();
        $js = array();
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
