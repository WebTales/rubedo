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
namespace Rubedo\Elastic;

use Rubedo\Interfaces\Elastic\IDataSearch, Rubedo\Services\Manager;

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
     * Is the context a front office rendering ?
     *
     * @var boolean
     */
    protected static $_isFrontEnd;
    
    protected $_globalFilterList = array();
    protected $_filters;
    protected $_setFilter;
    protected $_params;
    protected $_facetOperators;

    protected function _getContentType ($contentTypeId)
    {
        if (! isset($this->contentTypesService)) {
            $this->contentTypesService = Manager::getService('ContentTypes');
        }
        if (! isset($this->contentTypesArray[$contentTypeId])) {
            $this->contentTypesArray[$contentTypeId] = $this->contentTypesService->findById($contentTypeId);
        }
        return $this->contentTypesArray[$contentTypeId];
    }

    protected function _getDamType ($damTypeId)
    {
        if (! isset($this->damTypesService)) {
            $this->damTypesService = Manager::getService('DamTypes');
        }
        if (! isset($this->damTypesArray[$damTypeId])) {
            $this->damTypesArray[$damTypeId] = $this->damTypesService->findById($damTypeId);
        }
        return $this->damTypesArray[$damTypeId];
    }

    protected function _addFilter ($name, $field)
    {
    	// transform param to array if single value
    	if (!is_array($this->_params[$name])) {
    		$this->_params[$name] = array($this->_params[$name]);
    	}
        // get mode for this facet
        $operator = isset($this->_facetOperators[$name]) ? $this->_facetOperators[$name] : 'and';

        $filterEmpty = true;
        switch ($operator) {
            case 'or':
                $filter = new \Elastica_Filter_Terms();
                $filter->setTerms($field, $this->_params[$name]);
                $filterEmpty = false;
                break;
            case 'and':
            default:
                $filter = new \Elastica_Filter_And();      
                foreach($this->_params[$name] as $type) {
                    $termFilter = new \Elastica_Filter_Term();
                    $termFilter->setTerm($field, $type);
                    $filter->addFilter($termFilter);
                    $filterEmpty = false;
                }    
                break;

        }
        if (!$filterEmpty) {
            $this->_globalFilterList[$name]=$filter;
            $this->_filters[$name] = $this->_params[$name];
            $this->_setFilter = true;
        }
  
    }

    protected function _getFacetFilter($name)
    {
        // get mode for this facet
        $operator = isset($this->_facetOperators[$name]) ? $this->_facetOperators[$name] : 'and';
        if (!empty($this->_globalFilterList)) {
            $facetFilter = new \Elastica_Filter_And();
            $result = false;
            foreach ($this->_globalFilterList as $key=>$filter) {
                if ($key!=$name or $operator=='and') {
                    $facetFilter->addFilter($filter);
                    $result = true;
                }
            }
            if ($result) {
                return $facetFilter;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
    
    /**
     * ES search
     *
     * @see \Rubedo\Interfaces\IDataSearch::search()
     * @param
     *            s array $params search parameters : query, type, damtype, lang, author, date, taxonomy, target, pager, orderby, pagesize
     * @return Elastica_ResultSet
     */
    public function search (array $params, $option = 'all', $withSummary = true)
    {
    	$taxonomyService = Manager::getService('Taxonomy');
        $taxonomyTermsService = Manager::getService('TaxonomyTerms');
        
        $this->_params = $params;

        

        // front-end search
        if ((self::$_isFrontEnd)) {

        	// get list of displayed Facets
        	$displayedFacets = isset($this->_params['block-config']['displayedFacets']) ? $this->_params['block-config']['displayedFacets'] : array();

        	// if there is any facet to display
        	if (!empty($displayedFacets)) {
        		
        		$this->_facetOperators = array();
        		
        		// check if facetOverrides exists      	
        		
        		$facetOverrides = isset($this->_params['block-config']['facetOverrides']) ? (\Zend_Json::decode($this->_params['block-config']['facetOverrides'])) : array();
        		
       			if (!empty($facetOverrides)) {
		            
		            foreach ($facetOverrides as $facet) {
		                if (in_array($facet['id'],$displayedFacets)) {
		                    if ($facet['id']=='contentType') $facet['id'] = 'type';
		                    $this->_facetOperators[$facet['id']]=strtolower($facet['facetOperator']);
		                }
		            }
		            
	        	} else {
	        		
	        		// otherwise get facet operators from taxonomies

	        		foreach ($displayedFacets as $facetId) {

	        			$taxonomy = $taxonomyService->findById($facetId);
	        			if ($taxonomy) {
	        				$this->_facetOperators[$facetId]= isset($taxonomy['facetOperator']) ? strtolower($taxonomy['facetOperator']) : 'and';
	        			}
	        		}
	        		
	        		
	        	}
        	}
        }
        
        $result = array();
        $result['data'] = array();
        
        // Get taxonomies
        $collection = Manager::getService('Taxonomy');
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
        if (! array_key_exists('lang', $this->_params)) {
            $session = Manager::getService('Session');
            $this->_params['lang'] = $session->get('lang', 'fr');
        }
        
        if (! array_key_exists('pager', $this->_params))
            $this->_params['pager'] = $defaultVars['pager'];
        
        if (! array_key_exists('orderby', $this->_params))
            $this->_params['orderby'] = $defaultVars['orderby'];
        
        if (! array_key_exists('orderbyDirection', $this->_params))
            $this->_params['orderbyDirection'] = $defaultVars['orderbyDirection'];
        
        if (! array_key_exists('pagesize', $this->_params))
            $this->_params['pagesize'] = $defaultVars['pagesize'];
        
        if (! array_key_exists('query', $this->_params))
            $this->_params['query'] = $defaultVars['query'];
            
            // Build global filter
        
        $this->_setFilter = false;
       
        $globalFilter = new \Elastica_Filter_And();
        
        
        // Filter on read Workspaces
        
        $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
        
        if (! in_array('all', $readWorkspaceArray) && ! empty($readWorkspaceArray)) {
            
            $workspacesFilter = new \Elastica_Filter_Or();
            foreach ($readWorkspaceArray as $wsTerm) {
                $workspaceFilter = new \Elastica_Filter_Term();
                $workspaceFilter->setTerm('target', $wsTerm);
                $workspacesFilter->addFilter($workspaceFilter);
            }
            
            $globalFilterList['target']=$workspacesFilter;
            $this->_setFilter = true;
        }
        
        // Frontend filter on start and end publication date
        
        if ((self::$_isFrontEnd)) {
            $now = Manager::getService('CurrentTime')->getCurrentTime();
            
            // filter on start
            $beginFilterValue = new \Elastica_Filter_NumericRange('startPublicationDate', array(
                'to' => $now
            ));
            $beginFilterNotExists = new \Elastica_Filter_Not(new \Elastica_Filter_Exists('startPublicationDate'));
            $beginFilter = new \Elastica_Filter_Or();
            $beginFilter->addFilter($beginFilterNotExists);
            $beginFilter->addFilter($beginFilterValue);
            
            // filter on end : not set or not ended
            $endFilter = new \Elastica_Filter_Or();
            $endFilterWithValue = new \Elastica_Filter_NumericRange('endPublicationDate', array(
                'from' => $now
            ));
            $endFilterWithoutValue = new \Elastica_Filter_Term();
            $endFilterWithoutValue->setTerm('endPublicationDate', 0);
            $endFilterNotExists = new \Elastica_Filter_Not(new \Elastica_Filter_Exists('endPublicationDate'));
            $endFilter->addFilter($endFilterNotExists);
            $endFilter->addFilter($endFilterWithoutValue);
            $endFilter->addFilter($endFilterWithValue);
            
            // build complete filter
            $frontEndFilter = new \Elastica_Filter_And();
            $frontEndFilter->addFilter($beginFilter);
            $frontEndFilter->addFilter($endFilter);
            
            // push filter to global
            $globalFilterList['frontend']=$frontEndFilter;
            $this->_setFilter = true;
        }
               
        // filter on query
        if ($this->_params['query'] != '') {
            $this->_filters['query'] = $this->_params['query'];
        }
        
        // filter on content type
        if (array_key_exists('type', $this->_params)) {
            $this->_addFilter('type', 'contentType');           
        }
        
        // add filter for geo search on content types with 'position' field
        if ($option == 'geo') {
            $contentTypeList = Manager::getService('ContentTypes')->getGeolocatedContentTypes();
            if (! empty($contentTypeList)) {
                $geoFilter = new \Elastica_Filter_Or();
                foreach ($contentTypeList as $contentTypeId) {
                    $geoTypeFilter = new \Elastica_Filter_Term();
                    $geoTypeFilter->setTerm('contentType', $contentTypeId);
                    $geoFilter->addFilter($geoTypeFilter);
                }
                // push filter to global
                //$globalFilter->addFilter($geoFilter);
                $this->_globalFilterList['geoTypes']=$geoFilter;
                $this->_setFilter = true;
            }
        }
        
        // filter on dam type
        if (array_key_exists('damType', $this->_params)) {
            $this->_addFilter ('damType', 'damType', 'and');
        }
        
        // filter on author
        if (array_key_exists('author', $this->_params)) {
            $this->_addFilter ('author', 'author', 'and');
        }
        
        // filter on date
        if (array_key_exists('lastupdatetime', $this->_params)) {
            $filter = new \Elastica_Filter_Range('lastUpdateTime', array(
                'from' => $this->_params['lastupdatetime']
            ));
            $this->_globalFilterList['lastupdatetime']=$filter;
            $this->_filters['lastupdatetime'] = $this->_params['lastupdatetime'];
            $this->_setFilter = true;
            
        }
        
        // filter on geolocalisation if inflat, suplat, inflon and suplon are set
        if (isset($this->_params['inflat']) && isset($this->_params['suplat']) && isset($this->_params['inflon']) && isset($this->_params['suplon'])) {
            $topleft = array(
                $this->_params['inflon'],
                $this->_params['suplat']
            );
            $bottomright = array(
                $this->_params['suplon'],
                $this->_params['inflat']
            );
            $filter = new \Elastica_Filter_GeoBoundingBox('position_location', array(
                $topleft,
                $bottomright
            ));
            $this->_globalFilterList['geo']=$filter;
            $this->_setFilter = true;

        }
        
        // filter on taxonomy
        foreach ($taxonomies as $taxonomy) {
            $vocabulary = $taxonomy['id'];
            if (array_key_exists($vocabulary, $this->_params)) {
            	// transform param to array if single value
            	if (!is_array($this->_params[$vocabulary])) {
            		$this->_params[$vocabulary] = array($this->_params[$vocabulary]);
            	}                
                foreach ($this->_params[$vocabulary] as $term) {

                    $this->_addFilter ($vocabulary, 'taxonomy.' . $vocabulary, 'and');
   
                }
            }
        }
        
        // Set query on terms
        
        $elasticaQueryString = new \Elastica_Query_QueryString($this->_params['query'] . "*");
        
        $elasticaQuery = new \Elastica_Query();
        
        $elasticaQuery->setQuery($elasticaQueryString);
        
        // Apply filter to query
        if (!empty($this->_globalFilterList)) {
            foreach ($this->_globalFilterList as $filter) {
                $globalFilter->addFilter($filter);
            }
            $elasticaQuery->setFilter($globalFilter);
        }
        
        // Define the type facet
        
        if (!self::$_isFrontEnd or in_array('contentType',$displayedFacets)) {
            $elasticaFacetType = new \Elastica_Facet_Terms('type');
            $elasticaFacetType->setField('contentType');
            
            // Exclude active Facets for this vocabulary
            if (isset($this->_filters['type'])) {
                $elasticaFacetType->setExclude(array(
                    $this->_filters['type']
                ));
            }
            $elasticaFacetType->setSize(10);
            $elasticaFacetType->setOrder('reverse_count');
    
            // Apply filters from other facets
            $facetFilter = $this->_getFacetFilter('type');
            if (!is_null($facetFilter)) {
                $elasticaFacetType->setFilter($facetFilter);
            }
            
            // Add type facet to the search query object
            $elasticaQuery->addFacet($elasticaFacetType);
        }
        
        // Define the dam type facet
        
        if (!self::$_isFrontEnd or in_array('damType',$displayedFacets)) {

            $elasticaFacetDamType = new \Elastica_Facet_Terms('damType');
            $elasticaFacetDamType->setField('damType');
            
            // Exclude active Facets for this vocabulary
            if (isset($this->_filters['damType'])) {
                $elasticaFacetDamType->setExclude(array(
                    $this->_filters['damType']
                ));
            }
            $elasticaFacetDamType->setSize(10);
            $elasticaFacetDamType->setOrder('reverse_count');
    
            // Apply filters from other facets
            $facetFilter = $this->_getFacetFilter('damType');

            if (!is_null($facetFilter)) {
                $elasticaFacetDamType->setFilter($facetFilter);
            }
                
            // Add dam type facet to the search query object.
            $elasticaQuery->addFacet($elasticaFacetDamType);
        }
        
        // Define the author facet
        
        if (!self::$_isFrontEnd or in_array('author',$displayedFacets)) {
            
            $elasticaFacetAuthor = new \Elastica_Facet_Terms('author');
            $elasticaFacetAuthor->setField('author');
            
            // Exclude active Facets for this vocabulary
            if (isset($this->_filters['author'])) {
                $elasticaFacetAuthor->setExclude(array(
                    $this->_filters['author']
                ));
            }
            $elasticaFacetAuthor->setSize(5);
            $elasticaFacetAuthor->setOrder('reverse_count');
    
            // Apply filters from other facets
            $facetFilter = $this->_getFacetFilter('author');
            if (!is_null($facetFilter)) {
                $elasticaFacetAuthor->setFilter($facetFilter);
            }        
                
            // Add that facet to the search query object.
            $elasticaQuery->addFacet($elasticaFacetAuthor);
        }
        
        // Define the date facet.
        
        if (!self::$_isFrontEnd or in_array('date',$displayedFacets)) {
        
            $elasticaFacetDate = new \Elastica_Facet_Range('date');
            $elasticaFacetDate->setField('lastUpdateTime');
            $d = Manager::getService('CurrentTime')->getCurrentTime();
            
            $lastday = mktime(0, 0, 0, date('m', $d), date('d', $d) - 1, date('Y', $d));
            $lastweek = mktime(0, 0, 0, date('m', $d), date('d', $d) - 7, date('Y', $d));
            $lastmonth = mktime(0, 0, 0, date('m', $d) - 1, date('d', $d), date('Y', $d));
            $lastyear = mktime(0, 0, 0, date('m', $d), date('d', $d), date('Y', $d) - 1);
            $ranges = array(
                array(
                    'from' => $lastday
                ),
                array(
                    'from' => $lastweek
                ),
                array(
                    'from' => $lastmonth
                ),
                array(
                    'from' => $lastyear
                )
            );
            $timeLabel = array();
            $timeLabel[$lastday] = Manager::getService('Translate')->translate("Search.Facets.Label.Date.Day", 'Past 24H');
            $timeLabel[$lastweek] = Manager::getService('Translate')->translate("Search.Facets.Label.Date.Week", 'Past week');
            $timeLabel[$lastmonth] = Manager::getService('Translate')->translate("Search.Facets.Label.Date.Month", 'Past month');
            $timeLabel[$lastyear] = Manager::getService('Translate')->translate("Search.Facets.Label.Date.Year", 'Past year');
            
            $elasticaFacetDate->setRanges($ranges);
    
            // Apply filters from other facets
            $facetFilter = $this->_getFacetFilter('date');
            if (!is_null($facetFilter)) {
                $elasticaFacetDate->setFilter($facetFilter);
            }
            
            // Add that facet to the search query object.
            $elasticaQuery->addFacet($elasticaFacetDate);
        }
        
        // Define taxonomy facets
        foreach ($taxonomies as $taxonomy) {
            $vocabulary = $taxonomy['id'];
            
            if (!self::$_isFrontEnd or in_array($vocabulary,$displayedFacets)) {
            
                $elasticaFacetTaxonomy = new \Elastica_Facet_Terms($vocabulary);
                $elasticaFacetTaxonomy->setField('taxonomy.' . $taxonomy['id']);
                
                // Exclude active Facets for this vocabulary
                if (isset($this->_filters[$vocabulary])) {
                    $elasticaFacetTaxonomy->setExclude($this->_filters[$vocabulary]);
                }
                $elasticaFacetTaxonomy->setSize(20);
                $elasticaFacetTaxonomy->setOrder('count');
    
                // Apply filters from other facets
                $facetFilter = $this->_getFacetFilter($vocabulary);
                if (!is_null($facetFilter)) {
                    $elasticaFacetTaxonomy->setFilter($facetFilter);
                }            
                
                // Add that facet to the search query object.
                $elasticaQuery->addFacet($elasticaFacetTaxonomy);
            }
        }
        
        // Add pagination
        if (is_numeric($this->_params['pagesize'])) {
            $elasticaQuery->setSize($this->_params['pagesize'])->setFrom($this->_params['pager'] * $this->_params['pagesize']);
        }
        
        // add sort
        if ($this->_params['orderby'] == 'text') {
            $this->_params['orderby'] = 'text_not_analyzed';
        }
        $elasticaQuery->setSort(array(
            $this->_params['orderby'] => strtolower($this->_params['orderbyDirection'])
        ));
        
        // run query
        switch ($option) {
            case 'content':
                $elasticaResultSet = self::$_content_index->search($elasticaQuery);
                break;
            case 'dam':
                $elasticaResultSet = self::$_dam_index->search($elasticaQuery);
                break;
            case 'all':
                $client = self::$_content_index->getClient();
                $search = new \Elastica_Search($client);
                $search->addIndex(self::$_dam_index);
                $search->addIndex(self::$_content_index);
                $elasticaResultSet = $search->search($elasticaQuery);
                break;
            case 'geo':
                $elasticaResultSet = self::$_content_index->search($elasticaQuery);
                break;
        }
        
        // Update data
        $resultsList = $elasticaResultSet->getResults();
        $result['total'] = $elasticaResultSet->getTotalHits();
        $result['query'] = $this->_params['query'];
        $userWriteWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
        $userCanWriteContents = Manager::getService('Acl')->hasAccess("write.ui.contents");
        $userCanWriteDam = Manager::getService('Acl')->hasAccess("write.ui.dam");
        
        $writeWorkspaceArray = Manager::getService('CurrentUser')->getWriteWorkspaces();
        
        foreach ($resultsList as $resultItem) {
            
            $data = $resultItem->getData();
            
            $data['id'] = $resultItem->getId();
            $data['typeId'] = $resultItem->getType();
            $score = $resultItem->getScore();
            if (! is_float($score))
                $score = 1;
            $data['score'] = round($score * 100);
            
            $data['title'] = $data['text'];
            
            if ($withSummary and ! isset($data['summary'])) {
                $data['summary'] = $data['text'];
            }
            
            switch ($data['objectType']) {
                case 'content':
                    $contentType = $this->_getContentType($data['contentType']);
                    if (! $userCanWriteContents || $contentType['readOnly']) {
                        $data['readOnly'] = true;
                    } elseif (! in_array($resultItem->writeWorkspace, $userWriteWorkspaces)) {
                        $data['readOnly'] = true;
                    }
                    $data['type'] = $contentType['type'];
                    break;
                case 'dam':
                    $damType = $this->_getDamType($data['damType']);
                    if (! $userCanWriteDam || $damType['readOnly']) {
                        $data['readOnly'] = true;
                    } elseif (! in_array($resultItem->writeWorkspace, $userWriteWorkspaces)) {
                        $data['readOnly'] = true;
                    }
                    $data['type'] = $damType['type'];
                    break;
            }
            
            // Set read only
            
            if (in_array($data['writeWorkspace'], $writeWorkspaceArray)) {
                $data['readOnly'] = false;
            } else {
                $data['readOnly'] = true;
            }
            // do not return attached file if exists : can't be declared not stored as any other fields
            unset($data['file']);
            $result['data'][] = $data;
        }
        
        // Add label to Facets, hide empty facets,
        $elasticaFacets = $elasticaResultSet->getFacets();
        $result['facets'] = array();
        
        foreach ($elasticaFacets as $id => $facet) {
            $temp = (array) $facet;
            $renderFacet = true;
            if (! empty($temp)) {
                $temp['id'] = $id;
                switch ($id) {
                    case 'navigation':
                        
                        $temp['label'] = Manager::getService('Translate')->translate("Search.Facets.Label.Navigation", 'Navigation');
                        if (array_key_exists('terms', $temp) and count($temp['terms']) > 0) {
                            foreach ($temp['terms'] as $key => $value) {
                                $termItem = $taxonomyTermsService->getTerm($value['term'], 'navigation');
                                $temp['terms'][$key]['label'] = $termItem["Navigation"];
                            }
                        } else {
                            $renderFacet = false;
                        }
                        break;
                    
                    case 'damType':
                        
                        $temp['label'] = Manager::getService('Translate')->translate("Search.Facets.Label.MediaType", 'Media type');
                        if (array_key_exists('terms', $temp) and count($temp['terms']) > 0) {
                            foreach ($temp['terms'] as $key => $value) {
                                $termItem = $this->_getDamType($value['term']);
                                if ($termItem && isset($termItem['type'])) {
                                    $temp['terms'][$key]['label'] = $termItem['type'];
                                }
                            }
                        } else {
                            $renderFacet = false;
                        }
                        break;
                    
                    case 'type':
                        
                        $temp['label'] = Manager::getService('Translate')->translate("Search.Facets.Label.ContentType", 'Content type');
                        if (array_key_exists('terms', $temp) and count($temp['terms']) > 0) {
                            foreach ($temp['terms'] as $key => $value) {
                                
                                $termItem = $this->_getContentType($value['term']);
                                $temp['terms'][$key]['label'] = $termItem['type'];
                            }
                        } else {
                            $renderFacet = false;
                        }
                        break;
                    
                    case 'author':
                        
                        $temp['label'] = Manager::getService('Translate')->translate("Search.Facets.Label.Author", 'Author');
                        if (array_key_exists('terms', $temp) and count($temp['terms']) > 1) {
                            $collection = Manager::getService('Users');
                            foreach ($temp['terms'] as $key => $value) {
                                $termItem = $collection->findById($value['term']);
                                $temp['terms'][$key]['label'] = $termItem['name'];
                            }
                        } else {
                            $renderFacet = false;
                        }
                        break;
                    
                    case 'date':
                        
                        $temp['label'] = Manager::getService('Translate')->translate("Search.Facets.Label.ModificationDate", 'Modification date');
                        if (array_key_exists('ranges', $temp) and count($temp['ranges']) > 0) {
                            foreach ($temp['ranges'] as $key => $value) {
                                $rangeCount = $temp['ranges'][$key]['count'];
                                // unset facet when count = 0 or total results count
                                if ($rangeCount > 0 and $rangeCount < $result['total']) {
                                    $temp['ranges'][$key]['label'] = $timeLabel[$temp['ranges'][$key]['from']];
                                } else {
                                    unset($temp['ranges'][$key]);
                                }
                            }
                        } else {
                            $renderFacet = false;
                        }
                        
                        $temp["ranges"] = array_values($temp["ranges"]);
                        
                        break;
                    
                    default:
                        
                        $vocabularyItem = Manager::getService('Taxonomy')->findById($id);
                        $temp['label'] = $vocabularyItem['name'];
                        if (array_key_exists('terms', $temp) and count($temp['terms']) > 0) {
                            foreach ($temp['terms'] as $key => $value) {
                                $termItem = $taxonomyTermsService->findById($value['term']);
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
        
        $result['activeFacets'] = array();
        if (is_array($this->_filters)) {
        foreach ($this->_filters as $vocabularyId => $termId) {
            switch ($vocabularyId) {
                
                case 'damType':
                    $temp = array(
                            'id' => $vocabularyId,
                            'label' => Manager::getService('Translate')->translate("Search.Facets.Label.MediaType", 'Media type'),
                    );
                    foreach ($termId as $term) {
                        $termItem = $this->_getDamType($term);
                        $temp['terms'][] = array(
                                'term' => $term,
                                'label' => $termItem['type']
                        );
                    }
                                        
                    break;
                
                case 'type':
                    $temp = array(
                            'id' => $vocabularyId,
                            'label' => Manager::getService('Translate')->translate("Search.Facets.Label.ContentType", 'Content type'),
                    );
                    foreach ($termId as $term) {
                        $termItem = $this->_getContentType($term);
                        $temp['terms'][] = array(
                                'term' => $term,
                                'label' => $termItem['type']
                        );
                    }
                 
                    break;
                
                case 'author':
                    $temp = array(
                            'id' => $vocabularyId,
                            'label' => Manager::getService('Translate')->translate("Search.Facets.Label.Author", 'Author'),
                    );
                    foreach ($termId as $term) {
                        $termItem = Manager::getService('Users')->findById($term);
                        $temp['terms'][] = array(
                                'term' => $term,
                                'label' => $termItem['name']
                        );
                    }                    
                    

                    break;
                
                case 'lastupdatetime':
                    $temp = array(
                        'id' => 'lastupdatetime',
                        'label' => 'Date',
                        'terms' => array(
                            array(
                                'term' => $termId,
                                'label' => $timeLabel[$termId]
                            )
                        )
                    );
                    break;
                
                case 'query':
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
                
                case 'target':
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
                
                case 'workspace':
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
                case 'navigation':
                default:
                    $vocabularyItem = Manager::getService('Taxonomy')->findById($vocabularyId);
                    
                    $temp = array(
                        'id' => $vocabularyId,
                        'label' => $vocabularyItem['name']
                    );
                    
                    foreach ($termId as $term) {
                        $termItem = $taxonomyTermsService->findById($term);
                        $temp['terms'][] = array(
                            'term' => $term,
                            'label' => $termItem['text']
                        );
                    }
                    
                    break;
            }
            
            $result['activeFacets'][] = $temp;
        }
        }
        
        return ($result);
    }

    /**
     *
     * @param field_type $_isFrontEnd            
     */
    public static function setIsFrontEnd ($_isFrontEnd)
    {
        DataSearch::$_isFrontEnd = $_isFrontEnd;
    }
}
