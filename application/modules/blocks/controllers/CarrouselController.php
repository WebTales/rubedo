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

require_once ('ContentListController.php');

/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_CarrouselController extends Blocks_ContentListController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $this->_dataReader = Manager::getService('Contents');
        $this->_queryReader = Manager::getService('Queries');
        $blockConfig = $this->getRequest()->getParam('block-config');
        
        $filters = Manager::getService('Queries')->getFilterArrayById($blockConfig['query']);
        
        if ($filters !== false) {
            $queryType = $filters["queryType"];
            $queryId = $this->getParam('query-id', $blockConfig['query']);
            
            $query = $this->_queryReader->getQueryById($queryId);
            
            if ($queryType === "manual" && $query != false && isset($query['query']) && is_array($query['query'])) {
                $contentOrder = $query['query'];
                $keyOrder = array();
                $contentArray = array();
                
                // getList
                $unorderedContentArray = $this->getContentList($filters, $this->setPaginationValues($blockConfig));
                
                foreach ($contentOrder as $value) {
                    foreach ($unorderedContentArray['data'] as $subKey => $subValue) {
                        if ($value === $subValue['id']) {
                            $keyOrder[] = $subKey;
                        }
                    }
                }
                
                foreach ($keyOrder as $value) {
                    $contentArray["data"][] = $unorderedContentArray["data"][$value];
                }
                
                $nbItems = $unorderedContentArray["count"];
            } else {
                $contentArray = $this->getContentList($filters, $this->setPaginationValues($blockConfig));
                
                $nbItems = $contentArray["count"];
            }
        } else {
            $nbItems = 0;
        }
        
        $data = array();
        if ($nbItems > 0) {
            foreach ($contentArray['data'] as $vignette) {
                $fields = $vignette['fields'];
                $terms = isset($vignette['taxonomy']) && count($vignette['taxonomy']) > 0 ? array_pop($vignette['taxonomy']) : array();
                $termsArray = array();
                foreach ($terms as $term) {
                    if ($term == 'navigation') {
                        continue;
                    }
                    $termsArray[] = Manager::getService('TaxonomyTerms')->getTerm($term);
                }
                $fields['terms'] = $termsArray;
                $fields['title'] = $fields['text'];
                unset($fields['text']);
                $fields['id'] = (string) $vignette['id'];
                $data[] = $fields;
            }
        }
        $output = $this->getAllParams();
        $output["items"] = $data;
        $output["imageWidth"] = isset($blockConfig['imageWidth']) ? $blockConfig['imageWidth'] : null;
        $output["imageHeight"] = isset($blockConfig['imageHeight']) ? $blockConfig['imageHeight'] : null;
        $output["mode"] = isset($blockConfig['mode']) ? $blockConfig['mode'] : null;
        if (isset($blockConfig['displayType']) && ! empty($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/carrousel.html.twig");
        }
        
        $css = array();
        $js = array(
            '/templates/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/gallery.js")
        );
        $this->_sendResponse($output, $template, $css, $js);
    }
}
