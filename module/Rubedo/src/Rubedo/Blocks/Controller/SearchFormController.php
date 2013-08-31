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
class SearchFormController extends AbstractController
{

    /**
     * Default Action
     */
    public function indexAction ()
    {
        // get block config
        $blockConfig = $this->getParam('block-config', array());
        
        if (isset($blockConfig['searchPage'])) {
            $searchPage = $blockConfig['searchPage'];
        } else {
            $searchPage = null;
        }
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/searchForm.html.twig");
        
        $css = array();
        $js = array();
        
        $output = $this->getAllParams();
        $output['searchPage'] = $searchPage;
        $output['placeholder'] = isset($blockConfig['placeholder']) ? $blockConfig['placeholder'] : null;
        
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
