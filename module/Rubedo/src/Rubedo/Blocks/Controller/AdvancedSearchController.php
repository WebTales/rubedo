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
use Zend\Debug\Debug;

/**
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
class AdvancedSearchController extends AbstractController
{

    public function indexAction ()
    {
        $taxonomyService = Manager::getService("Taxonomy");
        
        // get search parameters
        $output = array();
        $taxonomies = array();
        $params = $this->params()->fromQuery();
        $output = $params;
        
        // get Taxonomies associated to the content type
        $blockConfig = $params["block-config"];
        if (isset($blockConfig['contentTypes'])){
            $contentTypes = $blockConfig['contentTypes'];
        } else {
            $contentTypes=array();
        }
        
        $searchPage = isset($blockConfig["searchPage"]) ? $blockConfig["searchPage"] : null;
        if (empty($searchPage)){
            $output = array();
            $css=array();
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/single/noContent.html.twig");
            $js = array();
            return $this->_sendResponse($output, $template, $css, $js);
        }
        $placeholder = isset($blockConfig["placeholder"]) ? $blockConfig["placeholder"] : null;
        
        if (isset($contentTypes)&&is_array($contentTypes)){
            foreach ($contentTypes as $contentTypeId) {
                $taxonomiesArray = $taxonomyService->findByContentTypeId($contentTypeId);
                
                unset($taxonomiesArray[""]);
                
                $taxonomies = array_merge($taxonomies, $taxonomiesArray);
            }
        }
        
        $output["taxonomies"] = $taxonomies;
        $output["contentTypes"]= $contentTypes;
        $output["searchPage"] = $searchPage;
        $output["placeholder"] = $placeholder;
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/advancedSearch.html.twig");
        
        $css = array();
        $js = array();
        
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
