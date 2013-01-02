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
namespace Rubedo\Elastic;

use Rubedo\Interfaces\Elastic\IDataSearch;
use Rubedo\Services\Manager;

/**
 * Class implementing the Rubedo API to Elastic Search using Elastica API
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataSearch extends DataAbstract implements IDataSearch
{

    /**
     * ES search
     *     
	 * @see \Rubedo\Interfaces\IDataSearch::search()
	 * @params array $params search parameters : query, type, lang, author, date, taxonomy, pager, orderby, pagesize
     * @return Elastica_ResultSet
     */
    public function search (array $params) {

		$filters = array();
		
		// Get taxonomies
		$collection = \Rubedo\Services\Manager::getService('MongoDataAccess');
		$collection->init("Taxonomy");	
		$taxonomyList = $collection->read();
		$taxonomies = $taxonomyList['data'];
		
		// Default parameters	
		$defaultVars = array(
			'query' => '',
			'type' => '',
			'lang' => '',
			'author' => '',
			'date' => '',
			'pager' => 0,
			'orderby' => '_score',
			'pagesize' => 10
		);
		
		// set default options
		if (!array_key_exists('lang',$params)) {
        	$session = Manager::getService('Session');
        	$params['lang'] = $session->get('lang','fr');
		}
		
		if (!array_key_exists('pager',$params)) $params['pager'] = $defaultVars['pager'];
		
		if (!array_key_exists('orderby',$params)) $params['orderby'] = $defaultVars['orderby'];
		
		if (!array_key_exists('pagesize',$params)) $params['pagesize'] = $defaultVars['pagesize'];
		
		if (!array_key_exists('query',$params)) $params['query']= $defaultVars['query'];
					
		try{

			// Build global filter
			
			$globalFilter = new \Elastica_Filter_And();
			$setFilter = false;
						
			// filter on lang TOTO add lang filter
			/*
			if ($lang != '') {
				$langFilter = new \Elastica_Filter_Term();
        		$langFilter->setTerm('lang', $lang);
				$globalFilter->addFilter($langFilter);
				$setFilter = true;
        	}
			 */
			
			// filter on query
			if ($params['query']!='') {
				$filters['query']=$params['query'];
			}
			
			// filter on type
			if (array_key_exists('type',$params)) {
				$typeFilter = new \Elastica_Filter_Term();
        		$typeFilter->setTerm('contentType', $params['type']);
				$globalFilter->addFilter($typeFilter);
				$filters["type"]=$params['type'];
				$setFilter = true;
			}
			
			// filter on author
			if (array_key_exists('author',$params)) {
				$authorFilter = new \Elastica_Filter_Term();
        		$authorFilter->setTerm('author', $params['author']);
				$globalFilter->addFilter($authorFilter);
				$filters["author"]=$params['author'];
				$setFilter = true;
			}
			
			// filter on date
			if (array_key_exists('date',$params)) {
				$dateFilter = new \Elastica_Filter_Range();
				$d = $params['date']/1000;
				$dateFrom = $dateTo = mktime(0, 0, 0, date('m',$d), date('d',$d), date('Y',$d))*1000; 
				$dateTo = mktime(0, 0, 0, date('m',$d)+1, date('d',$d), date('Y',$d))*1000;  
        		$dateFilter->addField('lastUpdateTime', array('from' => $dateFrom, "to" => $dateTo));
				$globalFilter->addFilter($dateFilter);
				$setFilter = true;
			}			

			// filter on taxonomy
			foreach ($taxonomies as $taxonomy) {
				$vocabulary = $taxonomy['name'];
				if (array_key_exists($vocabulary,$params)) {
				    if(!is_array($params[$vocabulary])){
				        $params[$vocabulary] = array($params[$vocabulary]);
				    }
					
					foreach ($params[$vocabulary] as $term){
					    $taxonomyFilter = new \Elastica_Filter_Term();
					    $taxonomyFilter->setTerm('taxonomy.'.$vocabulary, $term);
					    $globalFilter->addFilter($taxonomyFilter);
					    $filters[$vocabulary][]=$term;
					    $setFilter = true;
					}
					
					
								
				}
			}
						
			// Set query on terms
			$elasticaQueryString = new \Elastica_Query_QueryString($params['query']."*");
			
			$elasticaQuery = new \Elastica_Query();
			
			$elasticaQuery->setQuery($elasticaQueryString);
			
			// Apply filters if needed
			if ($setFilter) $elasticaQuery->setFilter($globalFilter);
		
			// Define the type facet.
			$elasticaFacetType = new \Elastica_Facet_Terms('type');
			$elasticaFacetType->setField('contentType');
			$elasticaFacetType->setSize(10);
			$elasticaFacetType->setOrder('reverse_count');
			if ($setFilter) $elasticaFacetType->setFilter($globalFilter);
						
			// Add type facet to the search query object.
			$elasticaQuery->addFacet($elasticaFacetType);
			
			// Define the author facet.
			$elasticaFacetAuthor = new \Elastica_Facet_Terms('author');
			$elasticaFacetAuthor->setField('author');
			$elasticaFacetAuthor->setSize(5);
			$elasticaFacetAuthor->setOrder('reverse_count');
			if ($setFilter) $elasticaFacetAuthor->setFilter($globalFilter);
						
			// Add that facet to the search query object.
			$elasticaQuery->addFacet($elasticaFacetAuthor);

			// Define the date facet.
			$elasticaFacetDate = new \Elastica_Facet_DateHistogram('date');
			$elasticaFacetDate->setField('lastUpdateTime');
			$elasticaFacetDate->setInterval('month');
			if ($setFilter) $elasticaFacetDate->setFilter($globalFilter);
												
			// Add that facet to the search query object.
			$elasticaQuery->addFacet($elasticaFacetDate);

			// Define taxonomy facets
			foreach ($taxonomies as $taxonomy) {
				$vocabulary = $taxonomy['name'];	
				$elasticaFacetTaxonomy = new \Elastica_Facet_Terms($vocabulary);
				$elasticaFacetTaxonomy->setField('taxonomy.'.$taxonomy['name']);
				$elasticaFacetTaxonomy->setSize(20);
				$elasticaFacetTaxonomy->setOrder('count');
				if ($setFilter) $elasticaFacetTaxonomy->setFilter($globalFilter);
				// Add that facet to the search query object.
				$elasticaQuery->addFacet($elasticaFacetTaxonomy);					        
			}
				
			// Add pagination 		
			if ($params['pagesize']!="all") {
				$elasticaQuery->setSize($params['pagesize'])->setFrom($params['pager']*$params['pagesize']);
			} 
						
			// add sort
			$elasticaQuery->setSort(array($params['orderby'] =>"desc"));

			// run query
			$elasticaResultSet = $this->_content_index->search($elasticaQuery);
			
			// Return resultset
			$result = array(
				"resultSet" => $elasticaResultSet,
				"filters" => $filters
			);
			return($result);
			
		} catch (Exception $e) {
            var_dump($e->getMessage());
			exit;
        }    	

    }
	
}
