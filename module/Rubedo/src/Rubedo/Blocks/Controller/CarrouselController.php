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
use WebTales\MongoFilters\Filter;

/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class CarrouselController extends ContentListController
{

    public function indexAction()
    {
        $this->_dataReader = Manager::getService('Contents');
        $this->_queryReader = Manager::getService('Queries');
        $blockConfig = $this->getRequest()->getParam('block-config');
        if(isset($blockConfig['imageField'])){
            $imageField = $blockConfig['imageField'];
        }else{
            $imageField = 'image';
        }
        
        $filters = Manager::getService('Queries')->getFilterArrayById($blockConfig['query']);
        $localFilters = $filters['filter'];
        $imageFilter = Filter::factory('OperatorToValue')->setName('fields.'.$imageField)->setOperator('$exists')->setValue(true);
        $localFilters->addFilter($imageFilter);
        $imageFilter = Filter::factory('OperatorToValue')->setName('fields.'.$imageField)->setOperator('$ne')->setValue('');
        $localFilters->addFilter($imageFilter);
        
        if ($filters !== false) {
            $queryType = $filters["queryType"];
            $queryId = $this->params()->fromQuery('query-id', $blockConfig['query']);
            
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
                if ($terms) {
                    foreach ($terms as $term) {
                        if ($term == 'navigation') {
                            continue;
                        }
                        $termsArray[] = Manager::getService('TaxonomyTerms')->getTerm($term);
                    }
                }
                
                $fields['terms'] = $termsArray;
                $fields['title'] = $fields['text'];
                unset($fields['text']);
                $fields['id'] = (string) $vignette['id'];
                $data[] = $fields;
            }
        }
        $output = $this->params()->fromQuery();
        $output['nbItems']= $nbItems;
        $output["items"] = $data;
        $output["imageField"] = $imageField;
        $output["imageWidth"] = isset($blockConfig['imageWidth']) ? $blockConfig['imageWidth'] : null;
        $output["imageHeight"] = isset($blockConfig['imageHeight']) ? $blockConfig['imageHeight'] : null;
        $output["mode"] = isset($blockConfig['mode']) ? $blockConfig['mode'] : null;
        $singlePage = isset($blockConfig['singlePage']) ? $blockConfig['singlePage'] : $this->params()->fromQuery('current-page');
        $output['singlePage'] = $this->params()->fromQuery('single-page', $singlePage);
        if (isset($blockConfig['displayType']) && ! empty($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/carrousel.html.twig");
        }
        
        $css = array();
        $js = array(
            $this->getRequest()->getBasePath() . '/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/gallery.js")
        );
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
