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
        $taxonomyTermsService = Manager::getService('TaxonomyTerms');
        
        $filters = array();
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
        if (! array_key_exists('lang', $params)) {
            $session = Manager::getService('Session');
            $params['lang'] = $session->get('lang', 'fr');
        }
        
        if (! array_key_exists('pager', $params))
            $params['pager'] = $defaultVars['pager'];
        
        if (! array_key_exists('orderby', $params))
            $params['orderby'] = $defaultVars['orderby'];
        
        if (! array_key_exists('orderbyDirection', $params))
            $params['orderbyDirection'] = $defaultVars['orderbyDirection'];
        
        if (! array_key_exists('pagesize', $params))
            $params['pagesize'] = $defaultVars['pagesize'];
        
        if (! array_key_exists('query', $params))
            $params['query'] = $defaultVars['query'];
            
        // Build global filter
        
        $setFilter = false;
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
            
            $globalFilter->addFilter($workspacesFilter);
            $setFilter = true;
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
            $globalFilter->addFilter($frontEndFilter);
        }
        
        // filter on lang TOTO add lang filter
        /*
         * if ($lang != '') { $langFilter = new \Elastica_Filter_Term(); $langFilter->setTerm('lang', $lang); $globalFilter->addFilter($langFilter); $setFilter = true; }
         */
        
        // filter on query
        if ($params['query'] != '') {
            $filters['query'] = $params['query'];
        }
        
        // filter on content type
        if (array_key_exists('type', $params)) {
            $typeFilter = new \Elastica_Filter_Term();
            $typeFilter->setTerm('contentType', $params['type']);
            $globalFilter->addFilter($typeFilter);
            $filters['type'] = $params['type'];
            $setFilter = true;
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
                $globalFilter->addFilter($geoFilter);
                $setFilter = true;
            }
        }
        
        // filter on dam type
        if (array_key_exists('damType', $params)) {
            $typeFilter = new \Elastica_Filter_Term();
            $typeFilter->setTerm('damType', $params['damType']);
            $globalFilter->addFilter($typeFilter);
            $filters['damType'] = $params['damType'];
            $setFilter = true;
        }
        
        // filter on author
        if (array_key_exists('author', $params)) {
            $authorFilter = new \Elastica_Filter_Term();
            $authorFilter->setTerm('author', $params['author']);
            $globalFilter->addFilter($authorFilter);
            $filters['author'] = $params['author'];
            $setFilter = true;
        }
        
        // filter on date
        if (array_key_exists('lastupdatetime',$params)) {         
            $dateFilter = new \Elastica_Filter_Range('lastUpdateTime',array('from' => $params['lastupdatetime']));
            $globalFilter->addFilter($dateFilter);
            $filters['lastupdatetime'] = $params['lastupdatetime'];
            $setFilter = true;                      
        }
        
        // filter on geolocalisation if inflat, suplat, inflon and suplon are set
        if (isset($params['inflat']) && isset($params['suplat']) && isset($params['inflon']) && isset($params['suplon'])) {
            $topleft = array(
                $params['inflon'],
                $params['suplat']
            );
            $bottomright = array(
                $params['suplon'],
                $params['inflat']
            );
            $geoFilter = new \Elastica_Filter_GeoBoundingBox('position_location', array(
                $topleft,
                $bottomright
            ));
            $globalFilter->addFilter($geoFilter);
            $setFilter = true;
        }
        
        // filter on taxonomy
        foreach ($taxonomies as $taxonomy) {
            $vocabulary = $taxonomy['id'];
            if (array_key_exists($vocabulary, $params)) {
                if (! is_array($params[$vocabulary])) {
                    $params[$vocabulary] = array(
                        $params[$vocabulary]
                    );
                }
                
                foreach ($params[$vocabulary] as $term) {
                    if(empty($term)){
                        continue;
                    }
                    $taxonomyFilter = new \Elastica_Filter_Term();
                    $taxonomyFilter->setTerm('taxonomy.' . $vocabulary, $term);
                    $globalFilter->addFilter($taxonomyFilter);
                    $filters[$vocabulary][] = $term;
                    $setFilter = true;
                }
            }
        }
        
        // Set query on terms
        
        $elasticaQueryString = new \Elastica_Query_QueryString($params['query'] . "*");
        
        $elasticaQuery = new \Elastica_Query();
        
        $elasticaQuery->setQuery($elasticaQueryString);
        
        // Apply filter if needed
        if ($setFilter) {
            $elasticaQuery->setFilter($globalFilter);
            //$elasticaQuery->setFields(array());
        }
        
        // Define the type facet.
        $elasticaFacetType = new \Elastica_Facet_Terms('type');
        $elasticaFacetType->setField('contentType');
        
        // Exclude active Facets for this vocabulary
        if (isset($filters['type'])) {
            $elasticaFacetType->setExclude(array(
                $filters['type']
            ));
        }
        $elasticaFacetType->setSize(10);
        $elasticaFacetType->setOrder('reverse_count');
        if ($setFilter)
            $elasticaFacetType->setFilter($globalFilter);
            
        // Add type facet to the search query object.
        $elasticaQuery->addFacet($elasticaFacetType);
        
        // Define the dam type facet.
        $elasticaFacetDamType = new \Elastica_Facet_Terms('damType');
        $elasticaFacetDamType->setField('damType');
        
        // Exclude active Facets for this vocabulary
        if (isset($filters['damType'])) {
            $elasticaFacetDamType->setExclude(array(
                $filters['damType']
            ));
        }
        $elasticaFacetDamType->setSize(10);
        $elasticaFacetDamType->setOrder('reverse_count');
        if ($setFilter)
            $elasticaFacetDamType->setFilter($globalFilter);
            
        // Add dam type facet to the search query object.
        $elasticaQuery->addFacet($elasticaFacetDamType);
        
        // Define the author facet.
        $elasticaFacetAuthor = new \Elastica_Facet_Terms('author');
        $elasticaFacetAuthor->setField('author');
        
        // Exclude active Facets for this vocabulary
        if (isset($filters['author'])) {
            $elasticaFacetAuthor->setExclude(array(
                $filters['author']
            ));
        }
        $elasticaFacetAuthor->setSize(5);
        $elasticaFacetAuthor->setOrder('reverse_count');
        if ($setFilter)
            $elasticaFacetAuthor->setFilter($globalFilter);
            
        // Add that facet to the search query object.
        $elasticaQuery->addFacet($elasticaFacetAuthor);
        
        // Define the date facet.
        $elasticaFacetDate = new \Elastica_Facet_Range('date');
        $elasticaFacetDate->setField('lastUpdateTime');
        $d = Manager::getService('CurrentTime')->getCurrentTime();
        
        $today = mktime(0, 0, 0, date('m', $d), date('d', $d), date('Y', $d));
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
        if ($setFilter)
            $elasticaFacetDate->setFilter($globalFilter);
            
        // Add that facet to the search query object.
        $elasticaQuery->addFacet($elasticaFacetDate);
        
        // Define taxonomy facets
        foreach ($taxonomies as $taxonomy) {
            $vocabulary = $taxonomy['id'];
            $elasticaFacetTaxonomy = new \Elastica_Facet_Terms($vocabulary);
            $elasticaFacetTaxonomy->setField('taxonomy.' . $taxonomy['id']);
            // Exclude active Facets for this vocabulary
            if (isset($filters[$vocabulary])) {
                $elasticaFacetTaxonomy->setExclude($filters[$vocabulary]);
            }
            $elasticaFacetTaxonomy->setSize(20);
            $elasticaFacetTaxonomy->setOrder('count');
            if ($setFilter)
                $elasticaFacetTaxonomy->setFilter($globalFilter);
                // Add that facet to the search query object.
            $elasticaQuery->addFacet($elasticaFacetTaxonomy);
        }
        
        // Add pagination
        if (is_numeric($params['pagesize'])) {
            $elasticaQuery->setSize($params['pagesize'])->setFrom($params['pager'] * $params['pagesize']);
        }
        
        // add sort
        if ($params['orderby'] == 'text') {
            $params['orderby'] = 'text_not_analyzed';
        }
        $elasticaQuery->setSort(array(
            $params['orderby'] => strtolower($params['orderbyDirection'])
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
        $result['query'] = $params['query'];
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
            
            if ($withSummary and !isset( $data['summary'])) {
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
            //do not return attached file if exists : can't be declared not stored as any other fields
            unset($data['file']);
            $result['data'][] = $data;
        }
        
        // Add label to Facets, hide facets with 1 result,
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
                                $rangeCount =  $temp['ranges'][$key]['count'];   
                                // unset facet when count = 0 or total results count
                                if ($rangeCount>0 and $rangeCount<$result['total']) {                      
                                    $temp['ranges'][$key]['label'] = $timeLabel[$temp['ranges'][$key]['from']];
                                } else {
                                    unset ($temp['ranges'][$key]);
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
        foreach ($filters as $vocabularyId => $termId) {
            switch ($vocabularyId) {
                
                case 'damType':
                    $termItem = $this->_getDamType($termId);
                    $temp = array(
                        'id' => $vocabularyId,
                        'label' => Manager::getService('Translate')->translate("Search.Facets.Label.MediaType", 'Media type'),
                        'terms' => array(
                            array(
                                'term' => $termId,
                                'label' => $termItem['type']
                            )
                        )
                    );
                    break;
                
                case 'type':
                    $termItem = $this->_getContentType($termId);
                    $temp = array(
                        'id' => $vocabularyId,
                        'label' => Manager::getService('Translate')->translate("Search.Facets.Label.ContentType", 'Content type'),
                        'terms' => array(
                            array(
                                'term' => $termId,
                                'label' => $termItem['type']
                            )
                        )
                    );
                    break;
                
                case 'author':
                    $termItem = Manager::getService('Users')->findById($termId);
                    $temp = array(
                        'id' => $vocabularyId,
                        'label' => Manager::getService('Translate')->translate("Search.Facets.Label.Author", 'Author'),
                        'terms' => array(
                            array(
                                'term' => $termId,
                                'label' => $termItem['name']
                            )
                        )
                    );
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
