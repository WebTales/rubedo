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
	 * @params array $params search parameters : query, type, damtype, lang, author, date, taxonomy, target, pager, orderby, pagesize
     * @return Elastica_ResultSet
     */
    public function search (array $params, $option = 'all') {

		$filters = array();
		$result = array();
		$result['data'] = array();

		// Get taxonomies
		$collection = \Rubedo\Services\Manager::getService('Taxonomy');
		$taxonomyList = $collection->getList();
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
			'orderbyDirection' => 'asc',
			'pagesize' => 25
		);
		
		// set default options
		if (!array_key_exists('lang',$params)) {
        	$session = Manager::getService('Session');
        	$params['lang'] = $session->get('lang','fr');
		}
		
		if (!array_key_exists('pager',$params)) $params['pager'] = $defaultVars['pager'];
		
		if (!array_key_exists('orderby',$params)) $params['orderby'] = $defaultVars['orderby'];
		
		if (!array_key_exists('orderbyDirection',$params)) $params['orderbyDirection'] = $defaultVars['orderbyDirection'];

		if (!array_key_exists('pagesize',$params)) $params['pagesize'] = $defaultVars['pagesize'];
		
		if (!array_key_exists('query',$params)) $params['query']= $defaultVars['query'];
					
		try{

			// Build global filter
			
			$globalFilter = new \Elastica_Filter_And();
			$workspacesFilter = new \Elastica_Filter_Or();
			
			$setFilter = false;
			$setWorkspaceFilter = false;
						
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
			
			// filter on content type
			if (array_key_exists('type',$params)) {
				$typeFilter = new \Elastica_Filter_Term();
        		$typeFilter->setTerm('contentType', $params['type']);
				$globalFilter->addFilter($typeFilter);
				$filters["type"]=$params['type'];
				$setFilter = true;
			}

			// filter on dam type
			if (array_key_exists('damType',$params)) {
				$typeFilter = new \Elastica_Filter_Term();
        		$typeFilter->setTerm('damType', $params['damType']);
				$globalFilter->addFilter($typeFilter);
				$filters["damType"]=$params['damType'];
				$setFilter = true;
			}
						
			// filter on author
			if (array_key_exists('author',$params)) {
				$authorFilter = new \Elastica_Filter_Term();
        		$authorFilter->setTerm('author', $params['author']);
				$globalFilter->addFilter($authorFilter);
				$filters['author']=$params['author'];
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

			// filter on target
			/*
			if (array_key_exists('target',$params)) {
				$targetFilter = new \Elastica_Filter_Term();
        		$targetFilter->setTerm('target', $params['target']);
				$globalFilter->addFilter($targetFilter);
				$filters["target"]=$params['target'];
				$setFilter = true;

			}
			 */				

			// filter on taxonomy
			foreach ($taxonomies as $taxonomy) {
				$vocabulary = $taxonomy['id'];
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
			
			// filter on read Workspaces		
			$readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
			if (!in_array('all',$readWorkspaceArray)) {
				foreach ($readWorkspaceArray as $workspace) {
					$workspaceFilter = new \Elastica_Filter_Term();
					$workspaceFilter->setTerm('target', $workspace);
					$workspacesFilter->addFilter($workspaceFilter);
				}
				$setWorkspaceFilter = true;				
			}
						
			// Set query on terms
			$elasticaQueryString = new \Elastica_Query_QueryString($params['query']."*");
			
			$elasticaQuery = new \Elastica_Query();
			
			$elasticaQuery->setQuery($elasticaQueryString);
			
			// Apply filters if needed
			if ($setFilter) $elasticaQuery->setFilter($globalFilter);
			if ($setWorkspaceFilter) $elasticaQuery->setFilter($workspacesFilter);
			
			// Define the type facet.
			$elasticaFacetType = new \Elastica_Facet_Terms('type');
			$elasticaFacetType->setField('contentType');
			// Exclude active Facets for this vocabulary
			if (isset($filters['type'])) {
				$elasticaFacetType->setExclude(array($filters['type']));					
			}
			$elasticaFacetType->setSize(10);
			$elasticaFacetType->setOrder('reverse_count');
			if ($setFilter) $elasticaFacetType->setFilter($globalFilter);

			// Add type facet to the search query object.
			$elasticaQuery->addFacet($elasticaFacetType);
			
			// Define the dam type facet.
			$elasticaFacetDamType = new \Elastica_Facet_Terms('damType');
			$elasticaFacetDamType->setField('damType');
			// Exclude active Facets for this vocabulary
			if (isset($filters['damType'])) {
				$elasticaFacetDamType->setExclude(array($filters['damType']));					
			}
			$elasticaFacetDamType->setSize(10);
			$elasticaFacetDamType->setOrder('reverse_count');
			if ($setFilter) $elasticaFacetDamType->setFilter($globalFilter);
									
			// Add dam type facet to the search query object.
			$elasticaQuery->addFacet($elasticaFacetDamType);
			
			// Define the author facet.
			$elasticaFacetAuthor = new \Elastica_Facet_Terms('author');
			$elasticaFacetAuthor->setField('author');
			// Exclude active Facets for this vocabulary
			if (isset($filters['author'])) {
				$elasticaFacetAuthor->setExclude(array($filters['author']));					
			}
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
				$vocabulary = $taxonomy['id'];	
				$elasticaFacetTaxonomy = new \Elastica_Facet_Terms($vocabulary);
				$elasticaFacetTaxonomy->setField('taxonomy.'.$taxonomy['id']);
				// Exclude active Facets for this vocabulary
				if (isset($filters[$vocabulary])) {
					$elasticaFacetTaxonomy->setExclude($filters[$vocabulary]);					
				}
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
			$elasticaQuery->setSort(array($params['orderby'] => $params['orderbyDirection']));

			// run query
			switch ($option) {
				case 'content': 
					$elasticaResultSet = $this->_content_index->search($elasticaQuery);
					break;
				case 'dam' :
					$elasticaResultSet = $this->_dam_index->search($elasticaQuery);
					break;
				case 'all' :
					$client = $this->_content_index->getClient();
					$search = new \Elastica_Search($client);
					$search->addIndex($this->_dam_index);
					$search->addIndex($this->_content_index);
					$elasticaResultSet = $search->search($elasticaQuery);
					break;
			}
			
			// Update data
			$resultsList = $elasticaResultSet->getResults();
			$result['total'] = $elasticaResultSet->getTotalHits();
			$result['query'] = $params['query'];
			foreach($resultsList as $resultItem) {
				$temp = array();
				$tmp['id'] = $resultItem->getId();
				$tmp['typeId'] = $resultItem->getType();
				$tmp['score'] = $resultItem->getScore();
				if (! is_float($tmp['score'])) $tmp['score'] = 1;
            	$tmp['score'] = round($tmp['score'] * 100);
				$data = $resultItem->getData();
				$tmp['title'] = $data['text'];
				$tmp['objectType'] = $data['objectType'];
				$tmp['readOnly'] = $data['readOnly'];
				if ($data['objectType'] === 'dam') {
					$tmp['damType'] = $data['damType'];
				}
				$tmp['summary'] = isset($data['summary']) ? $data['summary'] : $data['text'];		
				$tmp['author'] = $data['author'];
				$tmp['authorName'] = $data['authorName'];
				$tmp['lastUpdateTime'] = $data['lastUpdateTime'];
				if (array_key_exists('fileSize',$data)) {
					$tmp['fileSize'] = $data['fileSize'];
				} 
				switch ($data['objectType']) {
					case 'content':
						$contentType = \Rubedo\Services\Manager::getService('ContentTypes')->findById($data['contentType']);
						$tmp['type'] = $contentType['type'];
						break;
					case 'dam':
						$damType = \Rubedo\Services\Manager::getService('DamTypes')->findById($data['damType']);
						$tmp['type'] = $damType['type'];
						break;
				}
				
				$result['data'][] = $tmp;
			}
						
			// Add label to Facets, hide facets with 1 result, 
			$elasticaFacets = $elasticaResultSet->getFacets();
						
			$result['facets'] = array();
			
			foreach($elasticaFacets as $id => $facet) {
				$temp = (array) $facet;
				$renderFacet = true;
				if (!empty($temp)) {
					$temp['id'] = $id;
					switch ($id) {
						case 'navigation':
							
							$temp['label'] = 'Navigation';
							if (array_key_exists('terms', $temp) and count($temp['terms']) > 1) {
								$collection = \Rubedo\Services\Manager::getService('Pages');
								foreach ($temp['terms'] as $key => $value) {
									$termItem = $collection->findById($value['term']);
									$temp['terms'][$key]['label'] = $termItem['type'];
								}
							} else {
								$renderFacet = false;
							}
							break;

						case 'damType' :

							$temp['label'] = 'Type de document';
							if (array_key_exists('terms', $temp) and count($temp['terms']) > 0) {
								$collection = \Rubedo\Services\Manager::getService('DamTypes');
								foreach ($temp['terms'] as $key => $value) {
									$termItem = $collection->findById($value['term']);
									$temp['terms'][$key]['label'] = $termItem['type'];
								}
							} else {
								$renderFacet = false;
							}
							break;
																					
						case 'type' :
							
							$temp['label'] = 'Type de contenu';
							if (array_key_exists('terms', $temp) and count($temp['terms']) > 0) {
								$collection = \Rubedo\Services\Manager::getService('ContentTypes');
								foreach ($temp['terms'] as $key => $value) {
									$termItem = $collection->findById($value['term']);
									$temp['terms'][$key]['label'] = $termItem['type'];
								}
									
							} else {
								$renderFacet = false;
							}
							break;

						case 'author' :
							
							$temp['label'] = 'Auteur';
							if (array_key_exists('terms', $temp) and count($temp['terms']) > 1) {
								$collection = \Rubedo\Services\Manager::getService('Users');
								foreach ($temp['terms'] as $key => $value) {
									$termItem = $collection->findById($value['term']);
									$temp['terms'][$key]['label'] = $termItem['name'];
								}
							} else {
								$renderFacet = false;
							}
							break;
							
						default:
							
							$vocabularyItem = \Rubedo\Services\Manager::getService('Taxonomy')->findById($id);
							$temp['label'] = $vocabularyItem['name'];
							if (array_key_exists('terms', $temp) and count($temp['terms']) > 1) {
								$collection = \Rubedo\Services\Manager::getService('TaxonomyTerms');
								foreach ($temp['terms'] as $key => $value) {
									$termItem = $collection->findById($value['term']);
									$temp['terms'][$key]['label'] = $termItem['text'];
								}
							} else {
								$renderFacet = false;
							}
							break;
					}	
					if ($renderFacet) {
						$result['facets'][] = $temp;
					}
				}
			}
			
			// Add label to filters
				
			$result['activeFacets']= array();
			foreach ($filters as $vocabularyId => $termId) {
				switch ($vocabularyId) {
					case 'navigation':
						$termItem = \Rubedo\Services\Manager::getService('Pages')->findById($termId);
						$temp = array(
							'id' => $vocabularyId,
							'label' => 'Navigation',
							'terms' => array(
								array(
									'term' => $termId,
									'label' => $termItem['text']
								)
							)
						);
						break;
						
					case 'damType' :
						$termItem  = \Rubedo\Services\Manager::getService('DamTypes')->findById($termId);
						$temp = array(
							'id' => $vocabularyId,
							'label' => 'Types de documents',
							'terms' => array(
								array(
									'term' => $termId,
									'label' => $termItem['type']
								)
							)
						);
						break;
						
					case 'type' :
						$termItem  = \Rubedo\Services\Manager::getService('ContentTypes')->findById($termId);
						$temp = array(
							'id' => $vocabularyId,
							'label' => 'Types de Contenus',
							'terms' => array (
								array(
									'term' => $termId,
									'label' => $termItem['type']
								)
							)
						);
						break;
						
					case 'author' :
						$termItem  = \Rubedo\Services\Manager::getService('Users')->findById($termId);
						$temp = array(
							'id' => $vocabularyId,
							'label' => 'Auteur',
							'terms' => array (
								array(
									'term' => $termId,
									'label' => $termItem['name']
								)
							)
						);
						break;
					
					case 'query' :
						$temp = array(
							'id' => $vocabularyId,
							'label' => 'Query',
							'terms' => array(
								array(
									'term' => $termId,
									'label' => $termId
								)
							)
						);
						break;
					
					case 'target' :
						$temp = array(
							'id' => $vocabularyId,
							'label' => 'Target',
							'terms' => array(
								array(
									'term' => $termId,
									'label' => $termId
								)
							)
						);
						break;		
						
					case 'workspace' : 
						$temp = array(
							'id' => $vocabularyId,
							'label' => 'Workspace',
							'terms' => array(
								array(
									'term' => $termId,
									'label' => $termId
								)
							)
						);
						break;								
												
					default:
						$vocabularyItem = \Rubedo\Services\Manager::getService('Taxonomy')->findById($vocabularyId);	
						
						$temp = array(
							'id' => $vocabularyId,
							'label' => $vocabularyItem['name']
						);
						
						foreach ($termId as $term) {
							$termItem = \Rubedo\Services\Manager::getService('TaxonomyTerms')->findById($term);
							$temp['terms'][]=array(
								'term' => $term,
								'label' => $termItem['text']								
							);
	
						}
						
						break;							
						
				}

				$result['activeFacets'][] = $temp;
			}
		 
					
			return($result);
			
		} catch (Exception $e) {
            var_dump($e->getMessage());
			exit;
        }    	

    }
	
}
