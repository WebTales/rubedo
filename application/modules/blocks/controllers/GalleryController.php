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
class Blocks_GalleryController extends Blocks_ContentListController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $output = $this->_getList();
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/gallery.html.twig");
        
        $css = array();
        $js = array('/templates/'.Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/gallery.js"));
        
        $this->_sendResponse($output, $template, $css, $js);
    }

    public function xhrGetImagesAction ()
    {
        $twigVars = $this->_getList();
        
        $html = Manager::getService('FrontOfficeTemplates')->render('root/blocks/gallery/items.html.twig', $twigVars);
        $data = array(
            'html' => $html
        );
        $this->_helper->json($data);
    }

    /**
     * return a list of items based on the query
     * 
     * @return array
     */
    protected function _getList ()
    {
        $currentPage = $this->getRequest()->getParam('page', 1);
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $limit = (int)$this->getParam('itemsPerPage', 5);
            $prefix = $this->getParam('prefix');
            $imgWidth = $this->getParam('width',null);
            $imgHeight = $this->getParam('height',null);
            $query = Zend_Json::decode($this->getParam("query",Zend_Json::encode(null)));
            $filter = $this->setFilters($query);
        } else {
            $isDraft = Zend_Registry::get('draft');
            // Get queryId, blockConfig and Datalist
            $blockConfig = $this->getRequest()->getParam('block-config');
            $limit = (isset($blockConfig["pageSize"])) ? $blockConfig['pageSize'] : 5;
            
            $query = Zend_Json::decode($blockConfig["query"]);
            $filter = $this->setFilters($query);
            $imgWidth = $blockConfig['imageThumbnailWidth'];
            $imgHeight = $blockConfig['imageThumbnailHeight'];
            $prefix = $this->getParam('prefix', $this->getParam('prefix'));
        }
        
        $this->_dataService = Manager::getservice('Dam');
        
        // Get the number of pictures in database
        $allDamCount = $this->_dataService->count($filter['filter']);
        // Define the maximum number of pages
        $maxPage = (int) ($allDamCount / $limit);
        if ($allDamCount % $limit > 0) {
            $maxPage ++;
        }
        
        // Set the page to 1 if the user enter a bad page value in the URL
        if ($currentPage < 1 || $currentPage > $maxPage) {
            $currentPage = 1;
        }
        
        // Defines if the arrows of the carousel are displayed or none
        $next = true;
        $previous = true;
        
        if ($currentPage == $maxPage) {
            $next = false;
        }
        
        if ($currentPage <= 1) {
            $previous = false;
        }
        
        // Get the pictures
        $mediaArray = $this->_dataService->getList($filter['filter'], $filter['sort'], (($currentPage - 1) * $limit), $limit);
        
        // Set the ID and the title for each pictures
        foreach ($mediaArray['data'] as $media) {
            $fields["image"] = (string) $media['id'];
            $fields["title"] = $media['title'];
            $data[] = $fields;
        }
        
        // Values sent to the view
        $output = $this->getAllParams();
        $output['prefix']=$prefix;
        $output['items'] = $data;
        $output['allDamCount'] = $allDamCount;
        $output['maxPage'] = $maxPage;
        $output['previous'] = $previous;
        $output['next'] = $next;
        $output['count'] = $mediaArray['count'];
        $output['pageSize'] = $limit;
        $output["image"]["width"] = isset($imgWidth)?$imgWidth:null;
        $output["image"]["height"] = isset($imgHeight)?$imgHeight:null;
        $output['currentPage'] = $currentPage;
        $output['jsonQuery'] = Zend_Json::encode($query);
                
        return $output;
    }

    protected function setFilters ($query)
    {
        
        if ($query != null) {
            /* Add filters on TypeId and publication */
            $filterArray[] = array(
                'operator' => '$in',
                'property' => 'typeId',
                'value' => $query['DAMTypes']
            );
            /* Add filter on taxonomy */
            foreach ($query['vocabularies'] as $key => $value) {
                if (isset($value['rule'])) {
                    if ($value['rule'] == "some") {
                        $taxOperator = '$in';
                    } elseif ($value['rule'] == "all") {
                        $taxOperator = '$all';
                    } elseif ($value['rule'] == "someRec") {
                        if (count($value['terms']) > 0) {
                            foreach ($value['terms'] as $child) {
                                $terms = $this->_taxonomyReader->fetchAllChildren($child);
                                foreach ($terms as $taxonomyTerms) {
                                    $value['terms'][] = $taxonomyTerms["id"];
                                }
                            }
                        }
                        $taxOperator = '$in';
                    } else {
                        $taxOperator = '$in';
                    }
                } else {
                    $taxOperator = '$in';
                }
                if (count($value['terms']) > 0) {
                    $filterArray[] = array(
                        'operator' => $taxOperator,
                        'property' => 'taxonomy.' . $key,
                        'value' => $value['terms']
                    );
                }
            }
            
            $filter = array(
                    'property' => 'target',
                    'operator' => '$in',
                    'value' => array(
                            $this->_workspace,
                            'all'
                    )
            );
            
            /*
             * Add Sort
             */
            if (isset($query['fieldRules'])) {
                foreach ($query['fieldRules'] as $field => $rule) {
                    $sort[] = array(
                        "property" => $field,
                        'direction' => $rule['sort']
                    );
                }
            } else {
                $sort[] = array(
                    'property' => 'id',
                    'direction' => 'DESC'
                );
            }
        } else {
            return array();
        }
        $returnArray = array(
            "filter" => $filterArray,
            "sort" => isset($sort) ? $sort : null
        );
        return $returnArray;
    }
}
