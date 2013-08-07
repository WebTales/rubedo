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
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_SearchController extends Blocks_AbstractController
{

    public function indexAction ()
    {
        
        // get search parameters
        $params = $this->getRequest()->getParams();

        //remove empty facets from criteria
        foreach($params as $key => $value){
            
            if(is_array($value)){
                foreach ($value as $subkey => $subvalue){
                    if(empty($subvalue)){
                        unset($params[$key][$subkey]);
                    }
                }
                if(count($value)==0){
                    unset($params[$key]);
                }
            }
        }
        
        $params['pagesize'] = $this->getParam('pagesize', 10);
        $params['pager'] = $this->getParam('pager', 0);
        
        if (isset($params['block-config']['constrainToSite']) && $params['block-config']['constrainToSite']) {
            $site = $this->getRequest()->getParam('site');
            $siteId = $site['id'];
            $params['navigation'][] = $siteId;
        }
        
        // apply predefined facets
        $facetsToHide = array();
        if (isset($params['block-config']['predefinedFacets'])) {
            $predefParamsArray = \Zend_Json::decode($params['block-config']['predefinedFacets']);
            if (is_array($predefParamsArray)) {
                foreach ($predefParamsArray as $key => $value) {
                    $params[$key] = $value;
                    $facetsToHide[] = $key;
                }
            }
        }
        
        Rubedo\Elastic\DataSearch::setIsFrontEnd(true);
        
        $query = Manager::getService('ElasticDataSearch');
        $query->init();
                
        $results = $query->search($params);
        $results['searchParams']=\Zend_Json::encode($params);
        $results['currentSite'] = isset($siteId) ? $siteId : null;
        if (isset($params['block-config']['constrainToSite']) && $params['block-config']['constrainToSite']) {
            $results['constrainToSite'] = true;
        }
        
        // Pagination
        if ($params['pagesize'] != "all") {
            $pagecount = intval(($results['total'] - 1) / $params['pagesize'] + 1);
        } else {
            $pagecount = 1;
        }
        $results['displayMode'] = isset($params['block-config']['displayMode']) ? $params['block-config']['displayMode'] : 'standard';
        $results['autoComplete'] = isset($params['block-config']['autoComplete']) ? $params['block-config']['autoComplete'] : false;
        $results['facetsToHide'] = $facetsToHide;
        $results['current'] = $params['pager'];
        $results['pagecount'] = $pagecount;
        $results['limit'] = min(array(
            $pagecount - 1,
            10
        ));
        
        $singlePage = isset($params['block-config']['singlePage']) ? $params['block-config']['singlePage'] : $this->getParam('current-page');       
        $results['singlePage'] = $this->getParam('single-page', $singlePage);
        
        $results['displayTitle'] = $this->getParam('displayTitle');
        $results['blockTitle'] = $this->getParam('blockTitle');
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/search.html.twig");
        
        $css = array();
        $js = array(
        	'/templates/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/facetsCheckBox.js"),
            '/templates/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/autocomplete.js")
		);
        
        $this->_sendResponse($results, $template, $css, $js);
    }
    
    public function xhrGetSuggestsAction ()
    {
        // get search parameters

        $params = \Zend_Json::decode($this->getRequest()->getParam('searchParams'));
        
        // get current language
        $currentLocale = Manager::getService('CurrentLocalization')->getCurrentLocalization();
        
        // set query
        $params['query'] = $this->getRequest()->getParam('query');
       
        // set field for autocomplete
        $params['field'] = 'autocomplete_'.$currentLocale;
               
        Rubedo\Elastic\DataSearch::setIsFrontEnd(true);
        
        $elasticaQuery = Manager::getService('ElasticDataSearch');
        $elasticaQuery->init();

        $suggestTerms = $elasticaQuery->search($params,'suggest');
    
        $data = array(
                'terms' => $suggestTerms
        );
        $this->_helper->json($data);
    }
}
