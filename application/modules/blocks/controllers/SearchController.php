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

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        
        // get search parameters
        $params = $this->getRequest()->getParams();
        $params['pagesize'] = $this->getParam('pagesize', 10);
        $params['pager'] = $this->getParam('pager', 0);
        
        if (isset($params['block-config']['constrainToSite']) && $params['block-config']['constrainToSite']) {
            $site = $this->getRequest()->getParam('site');
            $siteId = $site['id'];
            $params['navigation'][] = $siteId;
            $serverParams['navigation'][] = $siteId;
        }
        
        // apply predefined facets
        $facetsToHide = array();
        if (isset($params['block-config']['predefinedFacets'])) {
            $predefParamsArray = \Zend_Json::decode($params['block-config']['predefinedFacets']);
            foreach ($predefParamsArray as $key => $value) {
                $params[$key] = $value;
                $facetsToHide[] = $key;
            }
        }
        
        Rubedo\Elastic\DataSearch::setIsFrontEnd(true);
        
        $query = Manager::getService('ElasticDataSearch');
        $query->init();
        
        $results = $query->search($params);
        
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
        $results['facetsToHide'] = $facetsToHide;
        $results['current'] = $params['pager'];
        $results['pagecount'] = $pagecount;
        $results['limit'] = min(array(
            $pagecount - 1,
            10
        ));
        
        $results['displayTitle'] = $this->getParam('displayTitle');
        $results['blockTitle'] = $this->getParam('blockTitle');
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/search.html.twig");
        
        $css = array();
        $js = array();
        
        $this->_sendResponse($results, $template, $css, $js);
    }
}
