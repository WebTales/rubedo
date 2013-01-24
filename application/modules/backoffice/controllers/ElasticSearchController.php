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
	 
	public function indexAction() {
		
		// get params 
		$params = $this->getRequest()->getParams();
		$params['site']=null;
		
        $query = \Rubedo\Services\Manager::getService('ElasticDataSearch');
        
        $query->init();
        if (isset($params['limit'])) $params['pagesize'] = $params['limit'];
		if (isset($params['page'])) $params['pager'] = $params['page'];
		if (isset($params['sort'])) {
			$params['orderBy'] = $params['sort']['property'];
			$params['orderByDirection'] = $params['sort']['direction'];
		}
        $search = $query->search($params);
        $elasticaResultSet = $search["resultSet"];
		$filters = $search["filters"];
		
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
				$tmp['score'] = $result->getScore();
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

		//Zend_Debug::dump($results);
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-Type', "application/json", true);

        $returnValue = Zend_Json::encode($results);
        $returnValue = Zend_Json::prettyPrint($returnValue);

        $this->getResponse()->setBody($returnValue);
		
	}

}
