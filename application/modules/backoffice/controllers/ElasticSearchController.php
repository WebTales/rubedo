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
		
        // get query
        $terms = $this->getRequest()->getParam('query');
        
        // get type filter
        $type = $this->getRequest()->getParam('type');
        
        // get lang filter : TODO get lang from search
        $lang = "fr";
        
        // get author filter
        $author = $this->getRequest()->getParam('author');
        
        // get date filter
        $date = $this->getRequest()->getParam('date');
		
        // get taxonomy filter
        $taxonomy = $this->getRequest()->getParam('taxonomy');
        
        // get pager
        $pager = $this->getRequest()->getParam('pager',0);
            
        // get orderBy
        $orderBy = $this->getRequest()->getParam('orderby','_score');
            
        // get page size
        $pageSize = $this->getRequest()->getParam('pagesize',10);

        $query = \Rubedo\Services\Manager::getService('ElasticDataSearch');
        
        $query->init();
        
        $elasticaResultSet = $query->search($terms, $type, $lang, $author, 
                $date, $taxonomy, $pager, $orderBy, $pageSize);		
		
		$elasticaResults = $elasticaResultSet->getResults();
		$elasticaFacets = $elasticaResultSet->getFacets();
		$results = array();
		$results['total'] = $elasticaResultSet->getTotalHits();
		$results["results"] = array();
		$results["facets"] = array();
		if ($results['total'] > 0) {
			foreach($elasticaResults as $result) {
				$results["results"][] = (array) $result;
			}
			foreach($elasticaFacets as $name => $facet) {
				$temp = (array) $facet;
				$temp["name"] = $name;
				$results["facets"][] = $temp;
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
