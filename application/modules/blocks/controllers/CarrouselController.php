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
        $isDraft = Zend_Registry::get('draft');
        $this->_queryReader = Manager::getService('Queries');

        $blockConfig = $this->getRequest()->getParam('block-config');

        $filters = Manager::getService('Queries')->getFilterArrayById($blockConfig['query']);
        
        if ($filters !== false) {
            $queryType = $filters["queryType"];
            // getList
            $contentArray = $this->getContentList($filters, $this->setPaginationValues($blockConfig));
            $nbItems = $contentArray["count"];
        } else {
            $nbItems = 0;
        }
        
        $data = array();
        if ($nbItems > 0) {
            foreach ($contentArray['data'] as $vignette) {
                $fields = $vignette['fields'];
                $terms = array_pop($vignette['taxonomy']);
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
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/carrousel.html.twig");
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }

}
