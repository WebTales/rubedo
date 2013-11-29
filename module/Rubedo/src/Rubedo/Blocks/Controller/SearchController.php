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
use Rubedo\Elastic\DataSearch;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;

/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class SearchController extends AbstractController
{

    public function indexAction ()
    {
        
        // get search parameters
        $params = $this->params()->fromQuery();
        
        // remove empty facets from criteria
        foreach ($params as $key => $value) {
            
            if (is_array($value)) {
                foreach ($value as $subkey => $subvalue) {
                    if (empty($subvalue)) {
                        unset($params[$key][$subkey]);
                    }
                }
                if(count($params[$key])==0){
                    unset($params[$key]);
                }
            }
        }
        
        $params['pagesize'] = $this->params()->fromQuery('pagesize', 10);
        $params['pager'] = $this->params()->fromQuery('pager', 0);
        
        if (isset($params['block-config']['constrainToSite']) && $params['block-config']['constrainToSite']) {
            $site = $this->getRequest()
                ->params()
                ->fromQuery('site');
            $siteId = $site['id'];
            $params['navigation'][] = $siteId;
        }
        
        // apply predefined facets
        $facetsToHide = array();
        if (isset($params['block-config']['predefinedFacets'])) {
            $predefParamsArray = Json::decode($params['block-config']['predefinedFacets'], Json::TYPE_ARRAY);
            if (is_array($predefParamsArray)) {
                foreach ($predefParamsArray as $key => $value) {
                    $params[$key][] = $value;
                    $facetsToHide[] = $value;
                }
            }
        }
        
        Datasearch::setIsFrontEnd(true);
        
        $query = Manager::getService('ElasticDataSearch');
        $query->init();
        
        $results = $query->search($params);
        $results['searchParams'] = Json::encode($params, Json::TYPE_ARRAY);
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
        $results['profilePage'] = isset($params['block-config']['profilePage']) ? $params['block-config']['profilePage'] : false;
        if ($results["profilePage"]){
            $urlOptions = array(
                'encode' => true,
                'reset' => true
            );

            $results['profilePageUrl'] = $this->url()->fromRoute(null, array(
                'pageId' => $results["profilePage"]
            ), $urlOptions);
        }
        $singlePage = isset($params['block-config']['singlePage']) ? $params['block-config']['singlePage'] : $this->params()->fromQuery('current-page');
        $results['singlePage'] = $this->params()->fromQuery('single-page', $singlePage);
        
        $results['displayTitle'] = $this->params()->fromQuery('displayTitle');
        $results['blockTitle'] = $this->params()->fromQuery('blockTitle');
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/search.html.twig");
        
        $css = array();
        $js = array(
            $this->getRequest()->getBasePath() . '/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/facetsCheckBox.js"),
            $this->getRequest()->getBasePath() . '/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/autocomplete.js")
        );
        
        return $this->_sendResponse($results, $template, $css, $js);
    }

    public function xhrGetSuggestsAction ()
    {
        // get search parameters
        $params = Json::decode($this->getRequest()
            ->params()
            ->fromQuery('searchParams'), Json::TYPE_ARRAY);
        
        // get current language
        $currentLocale = Manager::getService('CurrentLocalization')->getCurrentLocalization();
        
        // set query
        $params['query'] = $this->getRequest()
            ->params()
            ->fromQuery('query');
        
        // set field for autocomplete
        $params['field'] = 'autocomplete_' . $currentLocale;
        
        Datasearch::setIsFrontEnd(true);
        
        $elasticaQuery = Manager::getService('ElasticDataSearch');
        $elasticaQuery->init();
        
        $suggestTerms = $elasticaQuery->search($params, 'suggest');
        
        $data = array(
            'terms' => $suggestTerms
        );
        return new JsonModel($data);
    }
}
