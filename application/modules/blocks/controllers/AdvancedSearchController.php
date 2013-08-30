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
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_AdvancedSearchController extends Blocks_AbstractController
{

    public function indexAction ()
    {
        $taxonomyService = Manager::getService("Taxonomy");
        
        // get search parameters
        $output = array();
        $taxonomies = array();
        $params = $this->getRequest()->getParams();
        $output = $params;
        
        // get Taxonomies associated to the content type
        $blockConfig = $params["block-config"];
        $contentTypes = $blockConfig['contentTypes'];
        
        $searchPage = isset($blockConfig["searchPage"]) ? $blockConfig["searchPage"] : null;
        $placeholder = isset($blockConfig["placeholder"]) ? $blockConfig["placeholder"] : null;
        
        foreach ($contentTypes as $contentTypeId) {
            $taxonomiesArray = $taxonomyService->findByContentTypeId($contentTypeId);
            
            unset($taxonomiesArray[""]);
            
            $taxonomies = array_merge($taxonomies, $taxonomiesArray);
        }
        
        $output["taxonomies"] = $taxonomies;
        $output["searchPage"] = $searchPage;
        $output["placeholder"] = $placeholder;
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/advancedSearch.html.twig");
        
        $css = array();
        $js = array();
        
        $this->_sendResponse($output, $template, $css, $js);
    }
}
