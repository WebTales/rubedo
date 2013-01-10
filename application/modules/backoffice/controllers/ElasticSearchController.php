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
        $params['pagesize']=100;
        $search = $query->search($params);
        $elasticaResultSet = $search["resultSet"];
		$filters = $search["filters"];
		
		$elasticaResults = $elasticaResultSet->getResults();
		$elasticaFacets = $elasticaResultSet->getFacets();
		$results = array();
		$results['total'] = $elasticaResultSet->getTotalHits();
		$results['results'] = array();
		$results['facets'] = array();
		$results['filters'] = $filters;
		if ($results['total'] > 0) {
			foreach($elasticaResults as $result) {
				$results['results'][] = (array) $result;
			}
			foreach($elasticaFacets as $name => $facet) {
				$temp = (array) $facet;
				$temp['name'] = $name;
				$results['facets'][] = $temp;
			}
		}

        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-Type', "application/json", true);

        $returnValue = Zend_Json::encode($results);
        $returnValue = Zend_Json::prettyPrint($returnValue);

        $this->getResponse()->setBody($returnValue);
		
	}

}
