<?php

/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2015 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Elastic;

use Zend\Json\Json;

/**
 * Class implementing the Rubedo API to Elastic Search using elastic API
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataSearch extends DataAbstract
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
    protected $_displayedFacets = array();
    protected $_facetDisplayMode;
    private $table = '0123456789bcdefghjkmnpqrstuvwxyz';

    /**
     * Cached getter for content type
     *
     * @param string $contentTypeId
     *            content type id
     * @return array
     */
    protected function _getContentType($contentTypeId)
    {
        if (!isset ($this->contentTypesService)) {
            $this->contentTypesService = $this->_getService('ContentTypes');
        }
        if (!isset ($this->contentTypesArray [$contentTypeId])) {
            $this->contentTypesArray [$contentTypeId] = $this->contentTypesService->findById($contentTypeId);
        }
        return $this->contentTypesArray [$contentTypeId];
    }

    /**
     * Cached getter for dam type
     *
     * @param string $damTypeId
     *            dam type id
     * @return array
     */
    protected function _getDamType($damTypeId)
    {
        if (!isset ($this->damTypesService)) {
            $this->damTypesService = $this->_getService('DamTypes');
        }
        if (!isset ($this->damTypesArray [$damTypeId])) {
            $this->damTypesArray [$damTypeId] = $this->damTypesService->findById($damTypeId);
        }
        return $this->damTypesArray [$damTypeId];
    }

    /**
     * Cached getter for user type
     *
     * @param string $userTypeId
     *            user type id
     * @return array
     */
    protected function _getUserType($userTypeId)
    {
        if (!isset ($this->userTypesService)) {
            $this->userTypesService = $this->_getService('userTypes');
        }
        if (!isset ($this->userTypesArray [$userTypeId])) {
            $this->userTypesArray [$userTypeId] = $this->userTypesService->findById($userTypeId);
        }
        return $this->userTypesArray [$userTypeId];
    }

    /**
     * Add filter to Query
     *
     * @param string $name
     *            filter name
     *            string $field
     *            field to apply filter
     * @return array            
     */
    protected function _addFilter($name, $field)
    {
        // transform param to array if single value
        if (!is_array($this->_params [$name])) {
            $this->_params [$name] = array(
                $this->_params [$name]
            );
        }
        // get mode for this facet
        $operator = isset ($this->_facetOperators [$name]) ? strtolower($this->_facetOperators [$name]) : 'and';

        $filterEmpty = true;
        $filter = array();
        switch ($operator) {
            case 'or' :
                $termFilter = [
                	'terms' => [
                		$field => $this->_params [$name]
        			]
        		];
        		$filter['or'][] = $termFilter;
                $filterEmpty = false;
                break;
            case 'and' :
            default :
                foreach ($this->_params [$name] as $type) {
                    $termFilter = [
        				'term' => [
        					$field => $type
                		]
                    ];
                    $filter['and'][] = $termFilter;
                    $filterEmpty = false;
                }
                break;
        }
        if (!$filterEmpty) {
            $this->_globalFilterList [$name] = $filter;
            $this->_filters [$name] = $this->_params [$name];
            $this->_setFilter = true;
        }
    }

    /**
     * Add facet to Query
     *
     * @param 	string $facetName 
     *        	string $fieldName
     *        	string $orderField
     *        	string $orderDirection
     * @return 	array
     */
    
    protected function _addTermsFacet($facetName, $fieldName = null, $orderField = '_count', $orderDirection = 'desc', $size = 1000) {

    	// Set default value for fieldName
    	If (is_null($fieldName)) $fieldName = $facetName;
    	
    	// Exclude active Facets for this vocabulary
    	$exclude = $this->_excludeActiveFacets($facetName);
    	
    	// Apply filters from other facets 
    	$facetFilter = self::_getFacetFilter($facetName);
    	 
    	// Build facet 
    	$result = [
    		'filter' => $facetFilter,
    		'aggs' => [
    			'aggregation' => [
    				'terms' => [
    					'field' => 	$fieldName,
    					'exclude' => $exclude,
    					'size' => $size,
    					'order' => [$orderField => $orderDirection]
    				]
    			]
    		]
    	];
    	
    	return $result;
    }
    
    protected function _addRangeFacet($facetName, $fieldName = null, $ranges) {
    
    	// Set default value for fieldName   	 
    	If (is_null($fieldName)) $fieldName = $facetName;
    	 
    	// Exclude active Facets for this vocabulary 
    	$exclude = $this->_excludeActiveFacets($facetName);
    	 
    	// Apply filters from other facets
    	$facetFilter = self::_getFacetFilter($facetName);
    
    	// Build facet
    	$result = [
    		'filter' => $facetFilter,
    		'aggs' => [
    			'aggregation' => [
    				'date_range' => [
    					'field' => 	$fieldName,
    					'ranges' => $ranges
				    ]
    			]
    		]
    	];
    	 
    	return $result;
    }
        
    protected function _excludeActiveFacets ($facetName) {
    	$exclude = [''];
    	if ($this->_facetDisplayMode != 'checkbox' and isset ($this->_filters [$facetName])) {
    		$exclude = $this->_filters [$facetName];
    	}
    	return $exclude;    	
    }

    protected function setLocaleFilter(array $values)
    {
        $filter = [
        	'or' => [
        		['missing' => ['field' => 'availableLanguages']],
	    		['terms' => ['availableLanguages' => $values]]
        	]
        ];
        $this->_globalFilterList ['availableLanguages'] = $filter;
        $this->_setFilter = true;
    }

    /**
     * Build facet filter from name
     *
     * @param string $name
     *            filter name
     * @return array or null
     */
    protected function _getFacetFilter($name)
    {
        // get mode for this facet
        $operator = isset ($this->_facetOperators [$name]) ? $this->_facetOperators [$name] : 'and';
        if (!empty ($this->_globalFilterList)) {
            $facetFilter = array();
            $result = false;
            foreach ($this->_globalFilterList as $key => $filter) {
                if ($key != $name or $operator == 'and') {
                    $facetFilter['and'][] = $filter;
                    $result = true;
                }
            }
            if ($result) {
                return $facetFilter;
            } else {
                return new \stdClass();
            }
        } else {
            return new \stdClass();
        }
    }

    /**
     * Is displayed Facet ?
     *
     * @param string $name
     *            facet name
     * @return boolean
     */
    protected function _isFacetDisplayed($name)
    {
        if (!self::$_isFrontEnd or $this->_displayedFacets == array(
                'all'
            ) or in_array($name, $this->_displayedFacets) or in_array(array(
                'name' => $name,
                'operator' => 'AND'
            ), $this->_displayedFacets) or in_array(array(
                'name' => $name,
                'operator' => 'OR'
            ), $this->_displayedFacets)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * ES search
     *
     * @param array $params search parameters : query, type, damtype,
     *            lang, author, date, taxonomy, target, pager, orderby, pagesize
     * @return array
     */
    public function search(array $params, $option = 'all', $withSummary = true)
    {
	
        $this->_params = $params;

        $this->_facetDisplayMode = isset ($this->_params ['block-config'] ['displayMode']) ? $this->_params ['block-config'] ['displayMode'] : 'standard';

        // front-end search        
        if ((self::$_isFrontEnd)) {

            // get list of displayed Facets
            $this->_displayedFacets = isset ($this->_params ['block-config'] ['displayedFacets']) ? $this->_params ['block-config'] ['displayedFacets'] : array();

            if (is_string($this->_displayedFacets)) {
                if ((empty ($this->_displayedFacets)) || ($this->_displayedFacets == "['all']")) {
                    $this->_displayedFacets = array(
                        'all'
                    );
                } else {
                    $this->_displayedFacets = Json::decode($this->_displayedFacets, Json::TYPE_ARRAY);
                }
            }

            // get current user language
            $currentLocale = $this->_getService('CurrentLocalization')->getCurrentLocalization();

            // get site localization strategy
            $localizationStrategy = $this->_getService('Taxonomy')->getLocalizationStrategy();

            // get locale fall back
            $fallBackLocale = $this->_getService('Taxonomy')->getFallbackLocale();

            // if there is any facet to display, get overrides
            if (!empty ($this->_displayedFacets)) {

                $this->_facetOperators = array();

                // check if facetOverrides exists
                $facetOverrides = isset ($this->_params ['block-config'] ['facetOverrides']) ? (Json::decode($this->_params ['block-config'] ['facetOverrides'], Json::TYPE_ARRAY)) : array();

                if (!empty ($facetOverrides)) { // This code is only for 2.0.x backward compatibility

                    foreach ($facetOverrides as $facet) {
                        if ($this->_displayedFacets == ['all'] or in_array($facet ['id'], $this->_displayedFacets)) {
                            if ($facet ['id'] == 'contentType') $facet ['id'] = 'type';
                            $this->_facetOperators [$facet ['id']] = strtolower($facet ['facetOperator']);
                        }
                    }
                } else {

                    // if all facets are displayed
                    if ($this->_displayedFacets == ['all']) {
                        // get facets operators from all taxonomies
                        $taxonomyList = $this->_getService('Taxonomy')->getList();

                        foreach ($taxonomyList ['data'] as $taxonomy) {
                            $this->_facetOperators [$taxonomy ['id']] = isset ($taxonomy ['facetOperator']) ? strtolower($taxonomy ['facetOperator']) : 'and';
                        }
                    } else {
                        // otherwise get facets operators from displayed facets only
                        foreach ($this->_displayedFacets as $facet) {

                            // Get facet operator from block
                            if ($facet ['operator']) {
                                $this->_facetOperators [$facet ['name']] = strtolower($facet ['operator']);
                            } else {
                                // Get default facet operator from taxonomy if not present in block configuration
                                if (preg_match('/[\dabcdef]{24}/', $facet ['name']) == 1 || $facet ['name'] == 'navigation') {
                                    $taxonomy = $this->_getService('Taxonomy')->findById($facet ['name']);
                                    if ($taxonomy) {
                                        $this->_facetOperators [$facet ['name']] = isset ($taxonomy ['facetOperator']) ? strtolower($taxonomy ['facetOperator']) : 'and';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            // for BO, the strategy is to search into the working langage with
            // fallback on all other langages (_all)
            $localizationStrategy = 'backOffice';
            $currentUser = $this->_getService('CurrentUser')->getCurrentUser();
            $currentLocale = $currentUser ['workingLanguage'];
        }

        // Get taxonomies
        $taxonomyList = $this->_getService('Taxonomy')->getList();
        $taxonomies = $taxonomyList ['data'];

        // Get faceted fields
        $collection = $this->_getService('ContentTypes');
        $facetedFields = $collection->getFacetedFields();
        foreach ($facetedFields as $facetedField) {
            // get default facet operator from faceted field if not present in block configuration
            if (!isset ($this->_facetOperators [$facetedField ['name']])) {
                $this->_facetOperators [$facetedField ['name']] = $facetedField ['facetOperator'];
            }
        }

        $result = array();
        $result ['data'] = array();

        // Default parameters
        $defaultVars = array(
            'query' => '',
            'pager' => 0,
            'orderby' => '_score',
            'orderbyDirection' => 'desc',
            'pagesize' => 25,
            'searchMode' => 'default'
        );

        // set default options
        if (!array_key_exists('lang', $this->_params)) {
            $session = $this->_getService('Session');
            $this->_params ['lang'] = $session->get('lang', 'fr');
        }

        foreach ($defaultVars as $varKey => $varValue) {
            if (!array_key_exists($varKey, $this->_params))
                $this->_params [$varKey] = $varValue;
        }

        $this->_params ['query'] = strip_tags($this->_params ['query']);

        // Build global and filter
        $this->_setFilter = false;

        $globalFilter = array();

        // Filter on read Workspaces
        $readWorkspaceArray = $this->_getService('CurrentUser')->getReadWorkspaces();

        if (($option != 'user') && (!in_array('all', $readWorkspaceArray)) && (!empty ($readWorkspaceArray))) {

        	$workspacesFilter = array();
            foreach ($readWorkspaceArray as $wsTerm) {
            	$workspacesFilter['or'][] = [
        			'term' => ['target' => $wsTerm]
            	];
            }

            $this->_globalFilterList ['target'] = $workspacesFilter;
            $this->_setFilter = true;
        }
        
        // Products filter        
        if (isset($this->_params['isProduct'])&&$this->_params['isProduct']){
        	$isProductFilter = [
        		'term' => ['isProduct' => true]
        	];
            $this->_globalFilterList ['isProduct']=$isProductFilter;
        }

        // Frontend filters, for contents only : online, start and end publication date
        if ((self::$_isFrontEnd) && ($option != 'user') && ($option != 'dam')) {

            // Only 'online' contents         
        	$onlineFilter = [
        		'or' => [
        			['term' => [
        				'online' => true]
        			],
        			['missing'=> [
        				'field' => 'online',
        				'existence' => true,
        				'null_value' => true
        				]
        			]
        		]
        	];
        	
            //  Filter on start and end publication date
            $now = $this->_getService('CurrentTime')->getCurrentTime();

            // filter on start
            $beginFilter = [
            	'or' => [
            		['missing'=> [
        				'field' => 'startPublicationDate',
        				'existence' => true,
        				'null_value' => true
        			]],
        			['term' => [
        				'startPublicationDate' => 0
        			]],
        			['range' => [
        				'startPublicationDate' => [
        					'lte' => $now
            			]
        			]]
            	]
            ];

            // filter on end : not set or not ended
            $endFilter = [
            	'or' => [
            		['missing'=> [
            			'field' => 'endPublicationDate',
            			'existence' => true,
            			'null_value' => true
            		]],
	            	['term' => [
	            		'endPublicationDate' => 0
        			]],
            		['range' => [
            			'endPublicationDate' => [
            				'gte' => $now
            			]
            		]]
            	]
            ];            

            // build complete filter
            $frontEndFilter = [
        		'and' => [
        			$onlineFilter,
        			$beginFilter,
        			$endFilter
            	]
            ];

            // push filter to global
            $this->_globalFilterList ['frontend'] = $frontEndFilter;
            $this->_setFilter = true;

        }

        // filter on query
        if ($this->_params ['query'] != '') {
            $this->_filters ['query'] = $this->_params ['query'];
        }

        // filter on object type : content, dam or user
        if (array_key_exists('objectType', $this->_params)) {
            $this->_addFilter('objectType', 'objectType');
        }

        // filter on content type
        if (array_key_exists('type', $this->_params)) {
            $this->_addFilter('type', 'contentType');
        }

        // filter on dam type
        if (array_key_exists('damType', $this->_params)) {
            $this->_addFilter('damType', 'damType');
        }

        // filter on user type
        if (array_key_exists('userType', $this->_params)) {
            $this->_addFilter('userType', 'userType');
        }
        
        // add filter for geo search on content types with 'position' field
        if ($option == 'geo') {
        	
            $contentTypeList = $this->_getService('ContentTypes')->getGeolocatedContentTypes();
            if (!empty ($contentTypeList)) {
            	$geoFilter = array();
                foreach ($contentTypeList as $contentTypeId) {
                    $geoFilter['or'][] = [
                    	'term' => ['contentType' => $contentTypeId]
            		];
                }
                // push filter to global
                $this->_globalFilterList ['geoTypes'] = $geoFilter;
                $this->_setFilter = true;
            }

            $geoAgreggation = [
            	'aggs' => [
            		'hash' => [
            			'geohash_grid' => [
            				'field' => 'fields.position.location.coordinates'
            			]
            		]	
            	]
            ];
        }

        // filter on author
        if (array_key_exists('author', $this->_params)) {
            $this->_addFilter('author', 'createUser.id');
        }

        // filter on user name
        if (array_key_exists('userName', $this->_params)) {
            $this->_addFilter('userName', 'first_letter');
        }

        // filter on lastupdatetime
        if (array_key_exists('lastupdatetime', $this->_params)) {
            $filter = [
            	'range' => [
            		'lastUpdateTime' => [
            			'gte' => $this->_params ['lastupdatetime']
            		]
           		]
        	];
            
           	$this->_globalFilterList ['lastupdatetime'] = $filter;
            $this->_filters ['lastupdatetime'] = $this->_params ['lastupdatetime'];
            $this->_setFilter = true;
        }
        
        // filter on geolocalisation if inflat, suplat, inflon and suplon are
        // set
        if (isset ($this->_params ['inflat']) && isset ($this->_params ['suplat']) && isset ($this->_params ['inflon']) && isset ($this->_params ['suplon'])) {

            $geoBoundingBoxFilter = [
            	'geo_bounding_box' => [
            		'fields.position.location.coordinates' => [
            			'top_left' => [
            				$this->_params ['inflon'] + 0,
            				$this->_params ['suplat'] + 0
            			],
            			'bottom_right' => [
                			$this->_params ['suplon'] + 0,
                			$this->_params ['inflat'] + 0
            			]
            		]
            	]
            ];
            
            $this->_globalFilterList ['geo'] = $geoBoundingBoxFilter;
            $this->_setFilter = true;
            // set precision for geohash aggregation
            $bucketWidth = round($this->get_distance_m($this->_params ['inflat'], $this->_params ['inflon'], $this->_params ['inflat'], $this->_params ['suplon']) / 8);
            switch ($bucketWidth) {
                case 0:
                    $geoPrecision = 1;
                    break;
                case $bucketWidth > 5009400:
                    $geoPrecision = 1;
                    break;
                case $bucketWidth > 1252300:
                    $geoPrecision = 2;
                    break;
                case $bucketWidth > 156500:
                    $geoPrecision = 3;
                    break;
                case $bucketWidth > 39100:
                    $geoPrecision = 4;
                    break;
                case $bucketWidth > 4900:
                    $geoPrecision = 5;
                    break;
                case $bucketWidth > 1200:
                    $geoPrecision = 6;
                    break;
                case $bucketWidth > 153:
                    $geoPrecision = 7;
                    break;
                case $bucketWidth > 38:
                    $geoPrecision = 8;
                    break;
                case $bucketWidth > 5:
                    $geoPrecision = 9;
                    break;
            }
        }

        // filter on taxonomy
        foreach ($taxonomies as $taxonomy) {
            $vocabulary = $taxonomy ['id'];

            if (array_key_exists($vocabulary, $this->_params)) {
                // transform param to array if single value
                if (!is_array($this->_params [$vocabulary])) {
                    $this->_params [$vocabulary] = array(
                        $this->_params [$vocabulary]
                    );
                }
                foreach ($this->_params [$vocabulary] as $term) {

                    $this->_addFilter($vocabulary, 'taxonomy.' . $vocabulary);
                }
            }
        }

        // filter on fields
        foreach ($facetedFields as $field) {

            if ($field ['useAsVariation']) {
                $fieldName = 'productProperties.variations.' . $field ['name'];
            } else {
                if (!$field ['localizable']) {
                    $fieldName = $field ['name'];
                } else {
                    $fieldName = $field ['name'] . '_' . $currentLocale;
                }
            }

            if (array_key_exists(urlencode($field ['name']), $this->_params)) {
                $this->_addFilter($field ['name'], $fieldName);
            }
        }
        
        $searchParams=[];
        
        // Setting fields from localization strategy for content or dam search
        // only
        if ($option != 'user') {
            switch ($localizationStrategy) {
                case 'backOffice' :
                	$elasticQueryString = [
                    	'fields' => [
                    		'all_' . $currentLocale,
                    		'_all^0.1'
                    	]
                	];
                    break;
                case 'onlyOne' :
                    $this->setLocaleFilter(array(
                        $currentLocale
                    ));
                    $elasticQueryString = [
                    	'fields' => [
                    		'all_' . $currentLocale,
                    		'all_nonlocalized',
                    		'_all'
                    	]
                    ];
                    break;

                case 'fallback' :
                default :
                    $this->setLocaleFilter(array(
                        $currentLocale,
                        $fallBackLocale
                    ));
                    if ($currentLocale != $fallBackLocale) {
                    	$elasticQueryString = [
                    		'fields' => [
                    			'all_' . $currentLocale,
                    			'all_' . $fallBackLocale . '^0.1',
                    			'all_nonlocalized^0.1',
                    			'_all'
                   			]
                    	];
                    } else {
                        $elasticQueryString = [
                        	'fields' => [
                            	'all_' . $currentLocale,
                            	'all_nonlocalized',
                        		'_all'
                        	]
                        ];
                    }
                    break;
            }
        } else {

            // user search do not use localization
            $elasticQueryString = [
            	'fields' => [
            		'all_nonlocalized'
                ]
            ];
            
        }
        
        // add user query
        if ($this->_params ['query'] != '') {
        	$elasticQueryString['query'] = $this->_params ['query'];
        } else {
        	$elasticQueryString['query'] = '*';
        }

        $searchParams['body']['query']['query_string'] = $elasticQueryString;
        
        // Apply filter to query and aggregations        
        if (!empty ($this->_globalFilterList)) {
            foreach ($this->_globalFilterList as $filter) {
            	$globalFilter['and'][] = $filter;
            }
            $searchParams['body']['post_filter'] = $globalFilter;
        }

        // Define the objectType facet (content, dam or user)
        if ($this->_isFacetDisplayed('objectType')) {

        	$searchParams['body']['aggs']['objectType'] = $this->_addTermsFacet('objectType');

        }

        // Define the type facet
        if (($this->_isFacetDisplayed('contentType')) || ($this->_isFacetDisplayed('type'))) {

        	$searchParams['body']['aggs']['type'] = $this->_addTermsFacet('type', 'contentType');

        }
        
        // Define the dam type facet
        if ($this->_isFacetDisplayed('damType')) {

        	$searchParams['body']['aggs']['damType'] = $this->_addTermsFacet('damType');
        	 
        }
        
        // Define the user type facet
        if ($this->_isFacetDisplayed('userType')) {

			$searchParams['body']['aggs']['userType'] = $this->_addTermsFacet('userType');

        }
        
        // Define the author facet
        if ($this->_isFacetDisplayed('author')) {
        
        	$searchParams['body']['aggs']['author'] = $this->_addTermsFacet('author','createUser.id');
 
        }
        
        // Define the alphabetical name facet for users    
        if ($option == 'user') {

        	$searchParams['body']['aggs']['userName'] = $this->_addTermsFacet('userName','first_letter');

        }

        // Define the date facet    
        $d = $this->_getService('CurrentTime')->getCurrentTime();
       
        $lastday = ( string ) mktime(0, 0, 0, date('m', $d), date('d', $d) - 1, date('Y', $d)) * 1000;
        $lastweek = ( string )mktime(0, 0, 0, date('m', $d), date('d', $d) - 7, date('Y', $d)) * 1000;
        $lastmonth = ( string )mktime(0, 0, 0, date('m', $d) - 1, date('d', $d), date('Y', $d)) * 1000;
        $lastyear = ( string )mktime(0, 0, 0, date('m', $d), date('d', $d), date('Y', $d) - 1) * 1000;
        
        $ranges = [
        	['from' => $lastday, 'to' => 'now'],
        	['from' => $lastweek, 'to' => 'now'],
        	['from' => $lastmonth, 'to' => 'now'],
        	['from' => $lastyear, 'to' => 'now'],
        ];
        
        $searchParams['body']['aggs']['lastupdatetime'] = $this->_addRangeFacet('lastUpdateTime','lastUpdateTime',$ranges);
            
        // init time label array
        $timeLabel = array();
        $timeLabel [$lastday] = $this->_getService('Translate')->translateInWorkingLanguage('Search.Facets.Label.Date.Day', 'Past 24H');
        $timeLabel [$lastweek] = $this->_getService('Translate')->translateInWorkingLanguage('Search.Facets.Label.Date.Week', 'Past week');
        $timeLabel [$lastmonth] = $this->_getService('Translate')->translateInWorkingLanguage('Search.Facets.Label.Date.Month', 'Past month');
        $timeLabel [$lastyear] = $this->_getService('Translate')->translateInWorkingLanguage('Search.Facets.Label.Date.Year', 'Past year');

        // Define taxonomy facets    
        foreach ($taxonomies as $taxonomy) {
            $vocabulary = $taxonomy ['id'];

            if ($this->_isFacetDisplayed($vocabulary)) {

            	$searchParams['body']['aggs'][$vocabulary] = $this->_addTermsFacet($vocabulary,'taxonomy.' . $taxonomy ['id'],'_count','desc',10);

            }
        }

        // Define the fields facets     
        foreach ($facetedFields as $field) {

            if ($field ['useAsVariation']) {
                $fieldName = 'productProperties.variations.' . $field ['name'];
            } else {

                if (!$field ['localizable']) {
                    $fieldName = $field ['name'];
                } else {
                    $fieldName = $field ['name'] . '_' . $currentLocale;
                }
            }

            if ($this->_isFacetDisplayed($field ['name'])) {

            	$searchParams['body']['aggs'][$field ['name']] = $this->_addTermsFacet($field ['name'],$field ['name'],'_count','desc',10);
            	
             }
        }

        // Add size and from to paginate results       
        if (isset($this->_params['start']) && isset($this->_params['limit'])) {
         	$searchParams['body']['size'] = $this->_params ['limit'];
        	$searchParams['body']['from'] = $this->_params ['start'];          
        } else {
            if (is_numeric($this->_params ['pagesize'])) {
            	$searchParams['body']['size'] = $this->_params ['pagesize'];
            	$searchParams['body']['from'] = $this->_params ['pager'] * $this->_params ['pagesize'];
            }
        }

        // add sort
        $searchParams['body']['sort'] = [
        	[$this->_params ['orderby'] => ['order' => strtolower($this->_params ['orderbyDirection']), 'ignore_unmapped' => true] ]   	
        ];   
        
        // retrieve all stored fields       
        $searchParams['body']['fields'] = ['*'];
        
        // run query
        switch ($option) {
            case 'content' :
            	$searchParams['index'] = $this->getIndexNameFromConfig('contentIndex');
                break;
            case 'dam' :
                $searchParams['index'] = $this->getIndexNameFromConfig('damIndex');
                break;
            case 'user' :
                $searchParams['index'] = $this->getIndexNameFromConfig('userIndex');
                break;
            case 'geo' :
                if (isset($geoPrecision)) $geoAgreggation['aggs']['hash']['geohash_grid']['precision'] = $geoPrecision;
                $searchParams['body']['aggs']['agf'] = $geoAgreggation;
                $searchParams['body']['aggs']['agf']['filter'] = $globalFilter;                               
                $searchParams['index'] = $this->getIndexNameFromConfig('contentIndex');
                break;
            case 'all' :
            	$searchParams['index'] = $this->getIndexNameFromConfig('contentIndex') . ','. $this->getIndexNameFromConfig('damIndex') . ',' . $this->getIndexNameFromConfig('userIndex');
                break;
        }

        // For geosearch dynamically set searchMode depending on the number of results       
        if ($option == 'geo' && self::$_isFrontEnd) {
        	$countSearchParams = $searchParams;
            $countSearchParams['body']['size'] = 0;
            unset($countSearchParams['body']['aggs']);
            unset($countSearchParams['body']['sort']);
            $elasticResultSet = $this->_client->search($countSearchParams);
            $noResults = $elasticResultSet['hits']['total'];
            if ($noResults > $this->_params['limit']) {
                $this->_params['searchMode'] = 'aggregate';
            } else {
                $this->_params['searchMode'] = 'default';
            }
        }

        // Get resultset
        switch ($this->_params['searchMode']) {
            case 'default': // default mode with results
                $elasticResultSet = $this->_client->search($searchParams);
                break;
            case 'aggregate': // no results needed
            	$searchParams['body']['size'] = 0;
                $elasticResultSet = $this->_client->search($searchParams);
                break;
            case 'count': // Only count
            	$searchParams['body']['size'] = 0;
            	unset($searchParams['body']['aggs']);
            	unset($searchParams['body']['sort']);
                $elasticResultSet = $this->_client->search($searchParams);
                return $elasticResultSet['hits']['total'];
                break;
        }

        // For geosearch get aggregation buckets        
        if ($option == 'geo') {
            $result ['Aggregations'] = $elasticResultSet['aggregations']['agf']['hash'];

            foreach ($result ['Aggregations']['buckets'] as $key => $bucket) {
                $point = $this->geoHashDecode($bucket['key']);
                $result ['Aggregations']['buckets'][$key] += $point;
            }
        }

        // Update data        
        $resultsList = $elasticResultSet['hits']['hits'];
        $result ['total'] = $elasticResultSet['hits']['total'];
        $result ['query'] = $this->_params ['query'];
        $userWriteWorkspaces = $this->_getService('CurrentUser')->getWriteWorkspaces();
        $userCanWriteContents = $this->_getService('Acl')->hasAccess('write.ui.contents');
        $userCanWriteDam = $this->_getService('Acl')->hasAccess('write.ui.dam');

        $writeWorkspaceArray = $this->_getService('CurrentUser')->getWriteWorkspaces();

        foreach ($resultsList as $resultItem) {
            $data = $resultItem['fields'];

            $resultData ['id'] = $resultItem['_id'];
            $resultData ['typeId'] = $resultItem['_type'];
            $score = $resultItem['_score'];
            if (!is_float($score))
                $score = 1;
            $resultData ['score'] = round($score * 100);
            $resultData ['authorName'] = isset ($data ['createUser.fullName'][0]) ? $data ['createUser.fullName'][0] : null;
            $resultData ['author'] = isset ($data ['createUser.id'][0]) ? $data ['createUser.id'][0] : null;
            $resultData ['version'] = isset ($data ['version'][0]) ? $data ['version'][0] : null;
            $resultData ['photo'] = isset ($data ['photo'][0] ) ? $data ['photo'][0] : null;
            $resultData ['objectType'] = $data ['objectType'][0];
            unset ($data ['objectType']);
            unset ($data ['photo']);

            if (isset ($data ['availableLanguages'][0] )) {
                if (!is_array($data ['availableLanguages'][0])) {
                    $resultData ['availableLanguages'] = array(
                        $data ['availableLanguages'][0]
                    );
                } else {
                    $resultData['availableLanguages'] = $data ['availableLanguages'][0];
                }
            }

            switch ($resultData ['objectType']) {
                case 'content' :
                    if (isset ($data ['i18n.' . $currentLocale . '.fields.text'][0])) {
                        $resultData ['title'] = $data ['i18n.' . $currentLocale . '.fields.text'][0];
                        if ($withSummary) {
                            $resultData ['summary'] = (isset ($data ['i18n.' . $currentLocale . '.fields.summary'][0])) ? $data ['i18n.' . $currentLocale . '.fields.summary'][0] : '';
                        }
                    } else {
                        $resultData ['title'] = $data ['text'][0] ;
                    }
                    $contentType = $this->_getContentType($data ['contentType'][0] );
                    if (!$userCanWriteContents || $contentType ['readOnly']) {
                        $resultData ['readOnly'] = true; 
                        } elseif ( !in_array('global', $userWriteWorkspaces) ) {
                        $resultData ['readOnly'] = true;
                    }
                    $resultData ['type'] = $contentType ['type'];
                    break;
                case 'dam' :
                    if (isset ($data ['i18n.' . $currentLocale . '.fields.title'][0])) {
                        $resultData ['title'] = $data ['i18n.' . $currentLocale . '.fields.title'][0];
                    } else {
                        $resultData ['title'] = $data ['text'][0] ;
                    }
                    $damType = $this->_getDamType($data ['damType'][0] );
                    if (!$userCanWriteDam || $damType ['readOnly']) {
                        $resultData ['readOnly'] = true;
                    } elseif (!in_array('global', $userWriteWorkspaces)) {
                        $resultData ['readOnly'] = true;
                    }
                    $resultData ['type'] = $damType ['type'];
                    break;
                case 'user' :

                    if (isset ($data ['fields.name'][0] )) {
                        $resultData ['name'] = $data ['fields.name'][0] ;
                    } else {
                        $resultData ['name'] = $data ['email'][0] ;
                    }
                    $resultData ['title'] = $resultData ['name'];
                    $userType = $this->_getUserType($data ['userType'][0] );
                    $resultData ['type'] = $userType ['type'];
                    break;
            }
            

            // ensure that date is formated as timestamp while handled as date
            // type for ES
            $data ['lastUpdateTime'] = strtotime($data ['lastUpdateTime'][0]) ;

            // Set read only
            if (!isset ($data ['writeWorkspace'][0] ) or in_array($data ['writeWorkspace'][0] , $writeWorkspaceArray)) {
                $resultData ['readOnly'] = false;
            } else {
                $resultData ['readOnly'] = true;
            }

            $result ['data'] [] = array_merge($resultData, $data);
        }

        // Add label to Facets, hide empty facets,        
        $elasticFacetsTemp = $elasticResultSet['aggregations'];

        $elasticFacets = array();
        if ((is_array($this->_displayedFacets)) && (!empty ($this->_displayedFacets)) && (!is_string($this->_displayedFacets [0]))) {
            foreach ($this->_displayedFacets as $requestedFacet) {
                foreach ($elasticFacetsTemp as $id => $obtainedFacet) {
                    if ($id == $requestedFacet ['name']) {
                        $elasticFacets [$id] = $obtainedFacet;
                    }
                }
            }
        } else {
            $elasticFacets = $elasticFacetsTemp;
        }
        $result ['facets'] = array();

        foreach ($elasticFacets as $id => $facet) {

        	if (isset($facet['aggregation'])) {
	            $temp = ( array )$facet['aggregation'];
	
	            $renderFacet = true;
	            if (!empty ($temp)) {
	                $temp ['id'] = $id;
	                switch ($id) {
	                	
	                    case 'navigation' :
	
	                        $temp ['label'] = $this->_getService('Translate')->translate('Search.Facets.Label.Navigation', 'Navigation');
	                        if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
	                            foreach ($temp ['buckets'] as $key => $value) {
	                                $termItem = $this->_getService('TaxonomyTerms')->getTerm($value ['key'], 'navigation');
	                                $temp ['terms'] [$key] ['label'] = $termItem ['Navigation'];
	                            }
	                        } else {
	                            $renderFacet = false;
	                        }
	                        break;
	
	                    case 'damType' :
	
	                        $temp ['label'] = $this->_getService('Translate')->translate('Search.Facets.Label.MediaType', 'Media type');
	                        if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
	                            foreach ($temp ['buckets'] as $key => $value) {
	                                $termItem = $this->_getDamType($value ['key']);
	                                if ($termItem && isset ($termItem ['type'])) {
	                                	$temp ['terms'] [$key] ['term'] = $value ['key'];
	                                    $temp ['terms'] [$key] ['label'] = $termItem ['type'];
	                                    $temp['terms'] [$key] ['count'] = $value['doc_count'];
	                                }
	                            }
	                        } else {
	                            $renderFacet = false;
	                        }
	                        break;
	
	                    case 'objectType' :
	
	                        $temp ['label'] = $this->_getService('Translate')->translate('Search.Facets.Label.DataType', 'Data type');
	                        foreach ($temp ['buckets'] as $key => $value) {
	                        	$temp ['terms'] [$key] ['term'] = $value ['key'];
	                            $temp ['terms'] [$key] ['label'] = $this->_getService('Translate')->translate('Search.Facets.Label.'.strtoupper($value ['key']), strtoupper($value ['key']));
	                            $temp['terms'] [$key] ['count'] = $value['doc_count'];
	                        }
	                        break;
	
	                    case 'type' :
	
	                        $temp ['label'] = $this->_getService('Translate')->translate('Search.Facets.Label.ContentType', 'Content type');
	                        if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
	                            foreach ($temp ['buckets'] as $key => $value) {
	                            	$temp ['terms'] [$key] ['term'] = $value ['key'];
	                                $termItem = $this->_getContentType($value ['key']);
	                                $temp ['terms'] [$key] ['label'] = $termItem ['type'];
	                                $temp['terms'] [$key] ['count'] = $value['doc_count'];
	                            }
	                        } else {
	                            $renderFacet = false;
	                        }
	                        break;
	
	                    case 'userType' :
	
	                        $temp ['label'] = $this->_getService('Translate')->translate('Search.Facets.Label.UserType', 'User type');
	                        if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
	                            foreach ($temp ['buckets'] as $key => $value) {
	                            	$temp ['terms'] [$key] ['term'] = $value ['key'];
	                                $termItem = $this->_getUserType($value ['key']);
	                                $temp ['terms'] [$key] ['label'] = $termItem ['type'];
	                                $temp['terms'] [$key] ['count'] = $value['doc_count'];
	                            }
	                        } else {
	                            $renderFacet = false;
	                        }
	                        break;
	
	                    case 'author' :
	
	                        $temp ['label'] = $this->_getService('Translate')->translate('Search.Facets.Label.Author', 'Author');
	                        if ($this->_facetDisplayMode == 'checkbox' or (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0)) {
	                            $collection = $this->_getService('Users');
	                            foreach ($temp ['buckets'] as $key => $value) {
	                            	if ($value ['key'] != 'rubedo') {
		                            	$temp ['terms'] [$key] ['term'] = $value ['key'];
		                              	$termItem = $collection->findById($value ['key']);
		                              	$temp ['terms'] [$key] ['label'] = $termItem ['name'];
		                                $temp['terms'] [$key] ['count'] = $value['doc_count'];
	                            	}
	                            }
	                        } else {
	                            $renderFacet = false;
	                        }
	                        break;
	
	                    case 'userName' :
	
	                        $temp ['label'] = $this->_getService('Translate')->translate('Search.Facets.Label.UserName', 'User Name');
	                        foreach ($temp ['buckets'] as $key => $value) {
	                        	$temp ['terms'] [$key] ['term'] = $value ['key'];
	                            $temp ['terms'] [$key] ['label'] = strtoupper($value ['key']);
	                            $temp['terms'] [$key] ['count'] = $value['doc_count'];
	                        }
	
	                        break;
	
	                    case 'lastupdatetime' :
	
	                        $temp ['label'] = $this->_getService('Translate')->translate('Search.Facets.Label.ModificationDate', 'Modification date');
	                        if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
	                        	
	                        	$temp ['_type'] = 'range';
	                        	$temp ['ranges'] = array_values($temp ['buckets']);
	                        	
	                            foreach ($temp ['buckets'] as $key => $value) {
	                                $rangeCount = $value ['doc_count'];
	                                // unset facet when count = 0 or total results
	                                // count when display mode is not set to
	                                // checkbox
	                                if ($this->_facetDisplayMode == 'checkbox' or ($rangeCount > 0 and $rangeCount <= $result ['total'])) {
	                                	$temp ['ranges'] [$key] ['label'] = $timeLabel [( string )($value ['from'])];
	                                    $temp ['ranges'] [$key] ['count'] = $rangeCount;
	                                    unset( $temp ['ranges'] [$key] ['doc_count']);
	                                } else {
	                                    unset ($temp ['ranges'] [$key]);
	                                }
	                            }
	                        } else {
	                            $renderFacet = false;
	                        }
	
	                        break;
	
	                    default :
	                        $regex = '/^[0-9a-z]{24}$/';
	                        if (preg_match($regex, $id)) { // Taxonomy facet use
	                            // mongoID
	                            $vocabularyItem = $this->_getService('Taxonomy')->findById($id);
	                            $temp ['label'] = $vocabularyItem ['name'];
	                            if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
	                                foreach ($temp ['buckets'] as $key => $value) {
	                                	$temp ['terms'] [$key] ['term'] = $value ['key'];
	                                	$temp['terms'] [$key] ['count'] = $value['doc_count'];
	                                    $termItem = $this->_getService('TaxonomyTerms')->findById($value ['key']);
	                                    if ($termItem) {
	                                        $temp ['terms'] [$key] ['label'] = $termItem ['text'];
	                                    } else {
	                                        unset($temp ['terms'] [$key]);
	                                    }
	                                }
	                            } else {
	                                $renderFacet = false;
	                            }
	                        } else {
	                            // faceted field
	                            $intermediaryVal = $this->searchLabel($facetedFields, 'name', $id);	
	                            $temp ['label'] = $intermediaryVal [0] ['label'];
	
	                            if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
	                                foreach ($temp ['buckets'] as $key => $value) {
	                                	$temp ['terms'] [$key] ['term'] = $value ['key'];
	                                	$temp['terms'] [$key] ['count'] = $value['doc_count'];
	                                    $temp ['terms'] [$key] ['label'] = $value ['key'];
	                                }
	                            }
	                        }
	                        break;
	                }
	                if ($renderFacet) {
	                	unset ($temp['buckets']);
	                	unset ($temp['doc_count_error_upper_bound']);
	                	unset ($temp['sum_other_doc_count']);
	                    $result ['facets'] [] = $temp;
	                }
	            }
            }				
        }

        // Add label to filters
        $result ['activeFacets'] = array();
        if (is_array($this->_filters)) {
            foreach ($this->_filters as $id => $termId) {
                switch ($id) {

                    case 'damType' :
                        $temp = array(
                            'id' => $id,
                            'label' => $this->_getService('Translate')->translate('Search.Facets.Label.MediaType', 'Media type')
                        );
                        foreach ($termId as $term) {
                            $termItem = $this->_getDamType($term);
                            $temp ['terms'] [] = array(
                                'term' => $term,
                                'label' => $termItem ['type']
                            );
                        }

                        break;

                    case 'type' :
                        $temp = array(
                            'id' => $id,
                            'label' => $this->_getService('Translate')->translate('Search.Facets.Label.ContentType', 'Content type')
                        );
                        foreach ($termId as $term) {
                            $termItem = $this->_getContentType($term);
                            $temp ['terms'] [] = array(
                                'term' => $term,
                                'label' => $termItem ['type']
                            );
                        }

                        break;

                    case 'userType' :
                        $temp = array(
                            'id' => $id,
                            'label' => $this->_getService('Translate')->translate('Search.Facets.Label.UserType', 'User type')
                        );
                        foreach ($termId as $term) {
                            $termItem = $this->_getUserType($term);
                            $temp ['terms'] [] = array(
                                'term' => $term,
                                'label' => $termItem ['type']
                            );
                        }

                        break;

                    case 'author' :
                        $temp = array(
                            'id' => $id,
                            'label' => $this->_getService('Translate')->translate('Search.Facets.Label.Author', 'Author')
                        );
                        foreach ($termId as $term) {
                            $termItem = $this->_getService('Users')->findById($term);
                            $temp ['terms'] [] = array(
                                'term' => $term,
                                'label' => $termItem ['name']
                            );
                        }

                        break;

                    case 'userName' :
                        $temp = array(
                            'id' => $id,
                            'label' => $this->_getService('Translate')->translate('Search.Facets.Label.UserName', 'User Name')
                        );
                        foreach ($termId as $term) {
                            $temp ['terms'] [] = array(
                                'term' => $term,
                                'label' => strtoupper($term)
                            );
                        }

                        break;

                    case 'lastupdatetime' :
                        $temp = array(
                            'id' => 'lastupdatetime',
                            'label' => 'Date',
                            'terms' => array(
                                array(
                                    'term' => $termId,
                                    'label' => $timeLabel [( string ) $termId]
                                )
                            )
                        );

                        break;

                    case 'query' :
                        $temp = array(
                            'id' => $id,
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
                            'id' => $id,
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
                            'id' => $id,
                            'label' => 'Workspace',
                            'terms' => array(
                                array(
                                    'term' => $termId,
                                    'label' => $termId
                                )
                            )
                        );
                        break;
                    case 'navigation' :
                    default :
                        $regex = '/^[0-9a-z]{24}$/';
                        if (preg_match($regex, $id)) { // Taxonomy facet use
                            // mongoID
                            $vocabularyItem = $this->_getService('Taxonomy')->findById($id);

                            $temp = array(
                                'id' => $id,
                                'label' => $vocabularyItem ['name']
                            );

                            foreach ($termId as $term) {
                                $termItem = $this->_getService('TaxonomyTerms')->findById($term);
                                $temp ['terms'] [] = array(
                                    'term' => $term,
                                    'label' => $termItem ['text']
                                );
                            }
                        } else {
                            // faceted field
                            $temp = array(
                                'id' => $id,
                                'label' => $id
                            );
                            foreach ($termId as $term) {
                                $temp ['terms'] [] = array(
                                    'term' => $term,
                                    'label' => $term
                                );
                            }
                        }

                        break;
                }

                $result ['activeFacets'] [] = $temp;
            }
        }
        

        return ($result);
    }

    /**
     * get autocomplete suggestion
     *
     * @param array $params
     *            search parameters : query
     * @return array
     */
    public function suggest(array $params)
    {

        // init response
        $response = array();

        // get params
        $this->_params = $params;

        // get current user language
        $currentLocale = $this->_getService('CurrentLocalization')->getCurrentLocalization();

        // query
        $query = array(
            'autocomplete' => array(
                'text' => $this->_params ['query'],
                'completion' => array(
                    'field' => 'autocomplete_' . $currentLocale
                )
            )
        );

        $nonlocalizedquery = array(
            'autocomplete' => array(
                'text' => $this->_params ['query'],
                'completion' => array(
                    'field' => 'autocomplete_nonlocalized'
                )
            )
        );

        // Get search client
        $client = $this->_client;

        // get suggest from content
        $path = $this->getIndexNameFromConfig('contentIndex') . '/_suggest';
        $suggestion = $client->request($path, 'GET', $query);
        $responseArray = $suggestion->getData()['autocomplete'][0]['options'];

        // get suggest from dam
        $path = $this->getIndexNameFromConfig('damIndex') . '/_suggest';
        $suggestion = $client->request($path, 'GET', $query);
        if (isset ($suggestion->getData()['autocomplete'][0]['options'])) {
            $responseArray = array_merge($responseArray, $suggestion->getData()['autocomplete'][0]['options']);
        }

        // get suggest from user
        $path = $this->getIndexNameFromConfig('userIndex') . '/_suggest';
        $suggestion = $client->request($path, 'GET', $nonlocalizedquery);
        if (isset ($suggestion->getData()['autocomplete'][0]['options'])) {
            $responseArray = array_merge($responseArray, $suggestion->getData()['autocomplete'][0]['options']);
        }

        foreach ($responseArray as $suggest) {
            $response [] = $suggest;
        }
        return $response;
    }

    /**
     *
     * @param field_type $_isFrontEnd
     */
    public static function setIsFrontEnd($_isFrontEnd)
    {
        DataSearch::$_isFrontEnd = $_isFrontEnd;
    }

    protected function searchLabel($array, $key, $value)
    {
        $results = array();

        if (is_array($array)) {
            if (isset ($array [$key]) && $array [$key] == $value)
                $results [] = $array;

            foreach ($array as $subarray)
                $results = array_merge($results, $this->searchLabel($subarray, $key, $value));
        }

        return $results;
    }

    protected function get_distance_m($lat1, $lng1, $lat2, $lng2)
    {
        $earth_radius = 6378137;
        $rlo1 = deg2rad($lng1);
        $rla1 = deg2rad($lat1);
        $rlo2 = deg2rad($lng2);
        $rla2 = deg2rad($lat2);
        $dlo = ($rlo2 - $rlo1) / 2;
        $dla = ($rla2 - $rla1) / 2;
        $a = (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin($dlo));
        $d = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earth_radius * $d);
    }

    /**
     * @param string $hash a geohash
     * @author algorithm based on code by Alexander Songe <a@songe.me>
     * @see https://github.com/asonge/php-geohash/issues/1
     */
    private function geoHashDecode($hash)
    {
        $ll = array();
        $minlat = -90;
        $maxlat = 90;
        $minlon = -180;
        $maxlon = 180;
        $latE = 90;
        $lonE = 180;
        for ($i = 0, $c = strlen($hash); $i < $c; $i++) {
            $v = strpos($this->table, $hash[$i]);
            if (1 & $i) {
                if (16 & $v) $minlat = ($minlat + $maxlat) / 2; else $maxlat = ($minlat + $maxlat) / 2;
                if (8 & $v) $minlon = ($minlon + $maxlon) / 2; else $maxlon = ($minlon + $maxlon) / 2;
                if (4 & $v) $minlat = ($minlat + $maxlat) / 2; else $maxlat = ($minlat + $maxlat) / 2;
                if (2 & $v) $minlon = ($minlon + $maxlon) / 2; else $maxlon = ($minlon + $maxlon) / 2;
                if (1 & $v) $minlat = ($minlat + $maxlat) / 2; else $maxlat = ($minlat + $maxlat) / 2;
                $latE /= 8;
                $lonE /= 4;
            } else {
                if (16 & $v) $minlon = ($minlon + $maxlon) / 2; else $maxlon = ($minlon + $maxlon) / 2;
                if (8 & $v) $minlat = ($minlat + $maxlat) / 2; else $maxlat = ($minlat + $maxlat) / 2;
                if (4 & $v) $minlon = ($minlon + $maxlon) / 2; else $maxlon = ($minlon + $maxlon) / 2;
                if (2 & $v) $minlat = ($minlat + $maxlat) / 2; else $maxlat = ($minlat + $maxlat) / 2;
                if (1 & $v) $minlon = ($minlon + $maxlon) / 2; else $maxlon = ($minlon + $maxlon) / 2;
                $latE /= 4;
                $lonE /= 8;
            }
        }
        $ll['minlat'] = $minlat;
        $ll['minlon'] = $minlon;
        $ll['maxlat'] = $maxlat;
        $ll['maxlon'] = $maxlon;
        $ll['medlat'] = ($minlat + $maxlat) / 2;
        $ll['medlon'] = ($minlon + $maxlon) / 2;
        return $ll;
    }

}

