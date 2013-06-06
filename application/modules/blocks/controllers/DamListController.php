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
class Blocks_DamListController extends Blocks_AbstractController
{

    public function indexAction ()
    {
        
        // get search parameters
        $params = $this->getRequest()->getParams();
        
        $params['pager'] = $this->getParam('pager', 0);
        $params['orderbyDirection'] = 'asc';
        $params['orderby'] = 'text';
        $params['pagesize'] = 25;
        if (isset($params['block-config']['constrainToSite']) && $params['block-config']['constrainToSite']) {
            $site = $this->getRequest()->getParam('site');
            $siteId = $site['id'];
            $params['navigation'][] = $siteId;
            $serverParams['navigation'][] = $siteId;
        }
        if (isset($params['block-config']['sort']) && ($params['block-config']['sort'] == "desc")) {
            $params['orderbyDirection'] = 'desc';
        }
        if (isset($params['block-config']['pagesize'])) {
            $params['pagesize'] = $params['block-config']['pagesize'];
        }
        // apply predefined facets
        $facetsToHide = array();
        if (isset($params['block-config']['facets'])) {
            $predefParamsArray = \Zend_Json::decode($params['block-config']['facets']);
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
        $results = $query->search($params, 'dam');
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
        $results['prefix'] = $params['prefix'];
        $results['blockConfig'] = $params['block-config'];
        $results['current'] = $params['pager'];
        $results['pagecount'] = $pagecount;
        $results['limit'] = min(array(
            $pagecount - 1,
            10
        ));
        foreach ($results['data'] as $key => $value) {
            $results['data'][$key]['fileSize'] = $this->humanfilesize($value['fileSize']);
        }
        $results['displayTitle'] = $this->getParam('displayTitle');
        $results['blockTitle'] = $this->getParam('blockTitle');
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/damList.html.twig");
        $css = array();
        $js = array();
        if ((isset($params['xhrRefreshMode'])) && ($params['xhrRefreshMode'])) {
            $answer = array();
            $results['xhrRefreshMode'] = true;
            $answer['data'] = Manager::getService('FrontOfficeTemplates')->render($template, $results);
            $answer['success'] = true;
            $answer['message'] = 'OK';
            $this->_helper->json($answer);
        } else {
            $this->_sendResponse($results, $template, $css, $js);
        }
    }

    protected function humanfilesize ($bytes, $decimals = 0)
    {
        $size = array(
            'B',
            'kB',
            'MB',
            'GB',
            'TB',
            'PB',
            'EB',
            'ZB',
            'YB'
        );
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
}
