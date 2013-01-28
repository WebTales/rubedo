<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
 
/**
 * Controller providing Elastic Search querying
 *
 *
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 *
 */
 
class Backoffice_ElasticSearchController extends Zend_Controller_Action {
	
	protected $_option = 'all';
	 
	public function indexAction() {
		
		// get params 
		$params = $this->getRequest()->getParams();
		
		// get option : all, dam, content
		
		if (isset($params['option'])) {
			$this->_option = $params['option'];
		}
		
		// search over every sites
		$params['site']=null;
		
        $query = \Rubedo\Services\Manager::getService('ElasticDataSearch');
        
        $query->init();
        if (isset($params['limit'])) {
        	$params['pagesize'] = (int) $params['limit'];
		}
		if (isset($params['page'])) {
			$params['pager'] = (int) $params['page']-1;
		}
		if (isset($params['sort'])) {
			$sort = Zend_Json::decode($params['sort']);
			$params['orderby'] = ($sort[0]['property']=='score') ? '_score' : $sort[0]['property'];
			$params['orderbyDirection'] = $sort[0]['direction'];
		}

        $search = $query->search($params,$this->_option);

        $elasticaResultSet = $search["resultSet"];
		if (is_array($search["filters"])) {
			$filters = $search["filters"];
		} else {
			$filters = array();
		}
		
		$elasticaResults = $elasticaResultSet->getResults();
		$elasticaFacets = $elasticaResultSet->getFacets();
		$results = array();
		$results['total'] = $elasticaResultSet->getTotalHits();
		$results['data'] = array();
		$results['facets'] = array();
		$results['activeFacets'] = $filters;
		if ($results['total'] > 0) {
			foreach($elasticaResults as $result) {
				$temp = array();
				$tmp['id'] = $result->getId();
				$tmp['typeId'] = $result->getType();
				$tmp['_score'] = $result->getScore();
				$results['data'][] = array_merge($tmp,$result->getData());
			}
			foreach($elasticaFacets as $name => $facet) {
				$temp = (array) $facet;
				if (!empty($temp)) {
					$temp['name'] = $name;
					$results['facets'][] = $temp;
				}
			}
		}
		$results['success']=true;
		$results['message']='OK';

        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-Type', "application/json", true);

        $returnValue = Zend_Json::encode($results);
        $returnValue = Zend_Json::prettyPrint($returnValue);

        $this->getResponse()->setBody($returnValue);
		
	}

}
