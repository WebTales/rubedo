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
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Elastic;

use Rubedo\Interfaces\Elastic\IDataSearch;
use Rubedo\Services\Manager;
use Zend\Json\Json;

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
    protected $_displayedFacets = array();
    protected $_facetDisplayMode;
    private $table = "0123456789bcdefghjkmnpqrstuvwxyz";

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
            $this->contentTypesService = Manager::getService('ContentTypes');
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
            $this->damTypesService = Manager::getService('DamTypes');
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
            $this->userTypesService = Manager::getService('userTypes');
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
                //$filter = new \Elastica\Filter\Terms ();
                //$filter->setTerms($field, $this->_params [$name]);
                $filter = [
                	'terms' => [
                		$field => $this->_params [$name]
        			]
        		];
                $filterEmpty = false;
                break;
            case 'and' :
            default :
                //$filter = new \Elastica\Filter\BoolAnd ();
                foreach ($this->_params [$name] as $type) {
                    //$termFilter = new \Elastica\Filter\Term ();
                    //$termFilter->setTerm($field, $type);
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

    protected function setLocaleFilter(array $values)
    {
        $filter = new \Elastica\Filter\Terms ();
        $filter->setTerms('availableLanguages', $values);
        $this->_globalFilterList ['availableLanguages'] = $filter;
        $this->_setFilter = true;
    }

    /**
     * Build Elastica facet filter from name
     *
     * @param string $name
     *            filter name
     * @return Elastica\Filter or null
     */
    protected function _getFacetFilter($name)
    {
        // get mode for this facet
        $operator = isset ($this->_facetOperators [$name]) ? $this->_facetOperators [$name] : 'and';
        if (!empty ($this->_globalFilterList)) {
            $facetFilter = new \Elastica\Filter\BoolAnd ();
            $result = false;
            foreach ($this->_globalFilterList as $key => $filter) {
                if ($key != $name or $operator == 'and') {
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
     * Is displayed Facet ?
     *
     * @param string $name
     *            facet name
     * @return boolean
     */
    protected function _isFacetDisplayed($name)
    {
        if (!self::$_isFrontEnd or $this->_displayedFacets == array(
                "all"
            ) or in_array($name, $this->_displayedFacets) or in_array(array(
                "name" => $name,
                "operator" => "AND"
            ), $this->_displayedFacets) or in_array(array(
                "name" => $name,
                "operator" => "OR"
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
     * @see \Rubedo\Interfaces\IDataSearch::search()
     * @param
     *            s array $params search parameters : query, type, damtype,
     *            lang, author, date, taxonomy, target, pager, orderby, pagesize
     * @return Elastica\ResultSet
     */
    public function search(array $params, $option = 'all', $withSummary = true)
    {

        $taxonomyService = Manager::getService('Taxonomy');
        $taxonomyTermsService = Manager::getService('TaxonomyTerms');

        $this->_params = $params;

        $this->_facetDisplayMode = isset ($this->_params ['block-config'] ['displayMode']) ? $this->_params ['block-config'] ['displayMode'] : 'standard';

        // front-end search
        if ((self::$_isFrontEnd)) {

            // get list of displayed Facets

            $this->_displayedFacets = isset ($this->_params ['block-config'] ['displayedFacets']) ? $this->_params ['block-config'] ['displayedFacets'] : array();

            if (is_string($this->_displayedFacets)) {
                if ((empty ($this->_displayedFacets)) || ($this->_displayedFacets == "['all']")) {
                    $this->_displayedFacets = array(
                        "all"
                    );
                } else {
                    $this->_displayedFacets = Json::decode($this->_displayedFacets, Json::TYPE_ARRAY);
                }
            }

            // get current user language
            $currentLocale = Manager::getService('CurrentLocalization')->getCurrentLocalization();

            // get site localization strategy
            $localizationStrategy = $taxonomyService->getLocalizationStrategy();

            // get locale fall back
            $fallBackLocale = $taxonomyService->getFallbackLocale();

            // if there is any facet to display, get overrides
            if (!empty ($this->_displayedFacets)) {

                $this->_facetOperators = array();

                // check if facetOverrides exists

                $facetOverrides = isset ($this->_params ['block-config'] ['facetOverrides']) ? (Json::decode($this->_params ['block-config'] ['facetOverrides'], Json::TYPE_ARRAY)) : array();

                if (!empty ($facetOverrides)) { // This code is only for 2.0.x backward compatibility

                    foreach ($facetOverrides as $facet) {
                        if ($this->_displayedFacets == array(
                                "all"
                            ) or in_array($facet ['id'], $this->_displayedFacets)
                        ) {
                            if ($facet ['id'] == 'contentType')
                                $facet ['id'] = 'type';
                            $this->_facetOperators [$facet ['id']] = strtolower($facet ['facetOperator']);
                        }
                    }
                } else {

                    // if all facets are displayed

                    if ($this->_displayedFacets == array(
                            "all"
                        )
                    ) {

                        // get facets operators from all taxonomies
                        $taxonomyList = $taxonomyService->getList();

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
                                    $taxonomy = $taxonomyService->findById($facet ['name']);
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
            $localizationStrategy = "backOffice";
            $currentUser = Manager::getService('CurrentUser')->getCurrentUser();
            $currentLocale = $currentUser ["workingLanguage"];
        }

        // Get taxonomies
        $collection = Manager::getService('Taxonomy');
        $taxonomyList = $collection->getList();
        $taxonomies = $taxonomyList ['data'];

        // Get faceted fields
        $collection = Manager::getService('ContentTypes');
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
            $session = Manager::getService('Session');
            $this->_params ['lang'] = $session->get('lang', 'fr');
        }

        foreach ($defaultVars as $varKey => $varValue) {
            if (!array_key_exists($varKey, $this->_params))
                $this->_params [$varKey] = $varValue;
        }

        $this->_params ['query'] = strip_tags($this->_params ['query']);

        // Build global filter

        $this->_setFilter = false;

        //$globalFilter = new \Elastica\Filter\BoolAnd ();
        $globalFilter = array();

        // Filter on read Workspaces

        $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();

        if (($option != "user") && (!in_array('all', $readWorkspaceArray)) && (!empty ($readWorkspaceArray))) {

            //$workspacesFilter = new \Elastica\Filter\BoolOr ();
        	$workspacesFilter = array();
            foreach ($readWorkspaceArray as $wsTerm) {
                //$workspaceFilter = new \Elastica\Filter\Term ();
            	$workspaceFilter = array();
                //$workspaceFilter->setTerm('target', $wsTerm);
            	$workspaceFilter['term']['target'] = $wsTerm;
                //$workspacesFilter->addFilter($workspaceFilter);
            	$workspacesFilter['or'][] = $workspaceFilter;
            }

            $this->_globalFilterList ['target'] = $workspacesFilter;
            $this->_setFilter = true;
        }
        
        //Products filter
        if (isset($this->_params["isProduct"])&&$this->_params["isProduct"]){
            //$isProductFilter=new \Elastica\Filter\Term ();
        	$isProductFilter = array();
            //$isProductFilter->setTerm('isProduct', true);
        	$isProductFilter['term']['isProduct'] = true;
            $this->_globalFilterList ['isProduct']=$isProductFilter;
        }

        // Frontend filters, for contents only : online, start and end publication date

        if ((self::$_isFrontEnd) && ($option != "user") && ($option != "dam")) {

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

            $now = Manager::getService('CurrentTime')->getCurrentTime();

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
            $contentTypeList = Manager::getService('ContentTypes')->getGeolocatedContentTypes();
            if (!empty ($contentTypeList)) {
                //$geoFilter = new \Elastica\Filter\BoolOr ();
            	$geoFilter = array();
                foreach ($contentTypeList as $contentTypeId) {
                    //$geoTypeFilter = new \Elastica\Filter\Term ();
                    //$geoTypeFilter->setTerm('contentType', $contentTypeId);
                    $geoFilter['or'][] = [
                    	'term' => ['contentType' => $contentTypeId]
            		];
                    $geoFilter->addFilter($geoTypeFilter);
                }
                // push filter to global
                $this->_globalFilterList ['geoTypes'] = $geoFilter;
                $this->_setFilter = true;
            }
            //$geoAgreggation = new \Elastica\Aggregation\GeohashGrid('hash', 'fields.position.location.coordinates');
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

        // filter on author
        if (array_key_exists('userName', $this->_params)) {
            $this->_addFilter('userName', 'first_letter');
        }

        // filter on lastupdatetime
        if (array_key_exists('lastupdatetime', $this->_params)) {
            //$filter = new \Elastica\Filter\Range ('lastUpdateTime', array(
            //    'from' => $this->_params ['lastupdatetime']
            //));
            $filter = [
            	'range' => [
            		'endPublicationDate' => [
            			'gte' => $now
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
            			'bottomright' => [
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
                $fieldName = "productProperties.variations." . $field ['name'];
            } else {
                if (!$field ['localizable']) {
                    $fieldName = $field ['name'];
                } else {
                    $fieldName = $field ['name'] . "_" . $currentLocale;
                }
            }

            if (array_key_exists(urlencode($field ['name']), $this->_params)) {
                $this->_addFilter($field ['name'], $fieldName);
            }
        }
        
        //$elasticaQuery = new \Elastica\Query ();
        $searchParams=[];
        //$elasticaQueryString = new \Elastica\Query\QueryString ();
        
        // Setting fields from localization strategy for content or dam search
        // only

        if ($option != "user") {
            switch ($localizationStrategy) {
                case 'backOffice' :
                	$elasticaQueryString = [
                    	'fields' => [
                    		"all_" . $currentLocale,
                    		"_all^0.1"
                    	]
                	];
                    break;
                case 'onlyOne' :
                    //$this->setLocaleFilter(array(
                    //    $currentLocale
                    //));
                    $elasticaQueryString = [
                    	'fields' => [
                    		"all_" . $currentLocale,
                    		"all_nonlocalized",
                    		"_all"
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
                    	$elasticaQueryString = [
                    		'fields' => [
                    			"all_" . $currentLocale,
                    			"all_" . $fallBackLocale . "^0.1",
                    			"all_nonlocalized^0.1",
                    			"_all"
                   			]
                    	];
                    } else {
                        $elasticaQueryString = [
                        	'fields' => [
                            	"all_" . $currentLocale,
                            	"all_nonlocalized",
                        		"_all"
                        	]
                        ];
                    }
                    break;
            }
        } else {

            // user search do not use localization
            $elasticaQueryString = [
            	'fields' => [
                	"all_nonlocalized"
                ]
            ];
            
        }
        
        // add user query
        if ($this->_params ['query'] != "") {
        	$elasticaQueryString['query'] = $this->_params ['query'];
        } else {
        	$elasticaQueryString['query'] = '*';
        }

        $searchParams['body']['query']['query_string'] = $elasticaQueryString;
        
        // Apply filter to query and aggregations
        
        if (!empty ($this->_globalFilterList)) {
            foreach ($this->_globalFilterList as $filter) {
            	$globalFilter["and"][] = $filter;
            }
            $searchParams['body']['post_filter'] = $globalFilter;
        }

        // Define the objectType facet (content, dam or user)

        if ($this->_isFacetDisplayed('objectType')) {

        	// Exclude active Facets for this vocabulary
        	
        	if ($this->_facetDisplayMode != 'checkbox' and isset ($this->_filters ['objectType'])) {
        		$exclude = $this->_filters ['objectType'];
        	} else {
        		$exclude = [''];
        	}

        	$searchParams['body']['aggs']['objectType'] = [
                'terms' => [
                   	'field' => 	'objectType',
                   	'exclude' => $exclude,
                   	'size' => 1000,
                   	'order' => ['_count' => 'desc']
        		]
        	];

            // Apply filters from other facets
            //$facetFilter = $this->_getFacetFilter('objectType');
            //if (!is_null($facetFilter)) {
            //    $elasticaFacetObjectType->setFilter($facetFilter);
            //}

        }

        // Define the type facet

        if (($this->_isFacetDisplayed('contentType')) || ($this->_isFacetDisplayed('type'))) {

           	// Exclude active Facets for this vocabulary
        	
        	if ($this->_facetDisplayMode != 'checkbox' and isset ($this->_filters ['type'])) {
        		$exclude = $this->_filters ['type'];
        	} else {
        		$exclude = [''];
        	}

        	$searchParams['body']['aggs']['type'] = [
        		'terms' => [
        			'field' => 	'contentType',
        			'exclude' => $exclude,
        			'size' => 1000,
        			'order' => ['_count' => 'desc']
        		]
        	];

            // Apply filters from other facets
            //$facetFilter = $this->_getFacetFilter('type');
            //if (!is_null($facetFilter)) {
            //    $elasticaFacetType->setFilter($facetFilter);
            //}

        }
        /*
        // Define the dam type facet

        if ($this->_isFacetDisplayed('damType')) {

            $elasticaFacetDamType = new \Elastica\Facet\Terms ('damType');
            $elasticaFacetDamType->setField('damType');

            // Exclude active Facets for this vocabulary
            if ($this->_facetDisplayMode != 'checkbox' and isset ($this->_filters ['damType'])) {
                $elasticaFacetDamType->setExclude(array(
                    $this->_filters ['damType']
                ));
            }
            $elasticaFacetDamType->setSize(1000);
            $elasticaFacetDamType->setOrder('count');

            // Apply filters from other facets
            $facetFilter = $this->_getFacetFilter('damType');

            if (!is_null($facetFilter)) {
                $elasticaFacetDamType->setFilter($facetFilter);
            }

            // Add dam type facet to the search query object.
            $elasticaQuery->addFacet($elasticaFacetDamType);
        }

        // Define the user type facet

        if ($this->_isFacetDisplayed('userType')) {

            $elasticaFacetUserType = new \Elastica\Facet\Terms ('userType');
            $elasticaFacetUserType->setField('userType');

            // Exclude active Facets for this vocabulary
            if ($this->_facetDisplayMode != 'checkbox' and isset ($this->_filters ['userType'])) {
                $elasticaFacetUserType->setExclude(array(
                    $this->_filters ['userType']
                ));
            }
            $elasticaFacetUserType->setSize(1000);
            $elasticaFacetUserType->setOrder('count');

            // Apply filters from other facets
            $facetFilter = $this->_getFacetFilter('userType');

            if (!is_null($facetFilter)) {
                $elasticaFacetUserType->setFilter($facetFilter);
            }

            // Add user type facet to the search query object.
            $elasticaQuery->addFacet($elasticaFacetUserType);
        }

        // Define the author facet

        if ($this->_isFacetDisplayed('author')) {
            $elasticaFacetAuthor = new \Elastica\Facet\Terms ('author');
            $elasticaFacetAuthor->setField('createUser.id');

            // Exclude active Facets for this vocabulary
            if ($this->_facetDisplayMode != 'checkbox' and isset ($this->_filters ['author'])) {
                $elasticaFacetAuthor->setExclude(array(
                    $this->_filters ['author']
                ));
            }
            $elasticaFacetAuthor->setSize(5);
            $elasticaFacetAuthor->setOrder('count');

            // Apply filters from other facets
            $facetFilter = $this->_getFacetFilter('author');
            if (!is_null($facetFilter)) {
                $elasticaFacetAuthor->setFilter($facetFilter);
            }

            // Add that facet to the search query object.
            $elasticaQuery->addFacet($elasticaFacetAuthor);
        }

        // Define the alphabetical name facet for users

        if ($option == "user") {

            $elasticaFacetUserName = new \Elastica\Facet\Terms ('userName');
            $elasticaFacetUserName->setField('first_letter');

            $elasticaFacetUserName->setSize(25);

            // Apply filters from other facets
            $facetFilter = $this->_getFacetFilter('userName');
            if (!is_null($facetFilter)) {
                $elasticaFacetUserName->setFilter($facetFilter);
            }

            // Add that facet to the search query object.
            $elasticaQuery->addFacet($elasticaFacetUserName);
        }

        // Define the date facet.

        if ($this->_isFacetDisplayed('lastupdatetime')) {

            $elasticaFacetDate = new \Elastica\Facet\Range ('lastupdatetime');
            $elasticaFacetDate->setField('lastUpdateTime');
            $d = Manager::getService('CurrentTime')->getCurrentTime();

            // In ES 0.9, date are in microseconds
            $lastday = mktime(0, 0, 0, date('m', $d), date('d', $d) - 1, date('Y', $d)) * 1000;
            // Cast to string for 32bits systems
            $lastday = ( string )$lastday;
            $lastweek = mktime(0, 0, 0, date('m', $d), date('d', $d) - 7, date('Y', $d)) * 1000;
            $lastweek = ( string )$lastweek;
            $lastmonth = mktime(0, 0, 0, date('m', $d) - 1, date('d', $d), date('Y', $d)) * 1000;
            $lastmonth = ( string )$lastmonth;
            $lastyear = mktime(0, 0, 0, date('m', $d), date('d', $d), date('Y', $d) - 1) * 1000;
            $lastyear = ( string )$lastyear;
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

            $timeLabel [$lastday] = Manager::getService('Translate')->translateInWorkingLanguage("Search.Facets.Label.Date.Day", 'Past 24H');
            $timeLabel [$lastweek] = Manager::getService('Translate')->translateInWorkingLanguage("Search.Facets.Label.Date.Week", 'Past week');
            $timeLabel [$lastmonth] = Manager::getService('Translate')->translateInWorkingLanguage("Search.Facets.Label.Date.Month", 'Past month');
            $timeLabel [$lastyear] = Manager::getService('Translate')->translateInWorkingLanguage("Search.Facets.Label.Date.Year", 'Past year');

            $elasticaFacetDate->setRanges($ranges);

            // Apply filters from other facets
            $facetFilter = $this->_getFacetFilter('lastupdatetime');
            if (!is_null($facetFilter)) {
                $elasticaFacetDate->setFilter($facetFilter);
            }

            // Add that facet to the search query object.
            $elasticaQuery->addFacet($elasticaFacetDate);
        }

        // Define taxonomy facets
        foreach ($taxonomies as $taxonomy) {
            $vocabulary = $taxonomy ['id'];

            if ($this->_isFacetDisplayed($vocabulary)) {

                $elasticaFacetTaxonomy = new \Elastica\Facet\Terms ($vocabulary);
                $elasticaFacetTaxonomy->setField('taxonomy.' . $taxonomy ['id']);

                // Exclude active Facets for this vocabulary
                if ($this->_facetDisplayMode != 'checkbox' and isset ($this->_filters [$vocabulary])) {
                    $elasticaFacetTaxonomy->setExclude($this->_filters [$vocabulary]);
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

        // Define the fields facets
        foreach ($facetedFields as $field) {

            if ($field ['useAsVariation']) {
                $fieldName = "productProperties.variations." . $field ['name'];
            } else {

                if (!$field ['localizable']) {
                    $fieldName = $field ['name'];
                } else {
                    $fieldName = $field ['name'] . "_" . $currentLocale;
                }
            }

            if ($this->_isFacetDisplayed($field ['name'])) {

                $elasticaFacetField = new \Elastica\Facet\Terms ($field ['name']);
                $elasticaFacetField->setField("$fieldName");

                // Exclude active Facets for this vocabulary
                if ($this->_facetDisplayMode != 'checkbox' and isset ($this->_filters [$fieldName])) {
                    $elasticaFacetField->setExclude($this->_filters [$fieldName]);
                }
                $elasticaFacetField->setSize(20);
                $elasticaFacetField->setOrder('count');

                // Apply filters from other facets
                $facetFilter = $this->_getFacetFilter($fieldName);
                if (!is_null($facetFilter)) {
                    $elasticaFacetField->setFilter($facetFilter);
                }

                // Add that facet to the search query object.
                $elasticaQuery->addFacet($elasticaFacetField);
            }
        }

*/
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
        	[$this->_params ['orderby'] => ['order' => strtolower($this->_params ['orderbyDirection']), "ignore_unmapped" => true] ]   	
        ];

        $searchParams['body']['fields'] = ['*'];       
        
        // run query

        switch ($option) {
            case 'content' :
            	$searchParams['index'] = self::$_content_index['name'];
                break;
            case 'dam' :
                $searchParams['index'] = self::$_dam_index['name'];
                break;
            case 'user' :
                $searchParams['index'] = self::$_user_index['name'];
                break;
            case 'geo' :
                if (isset($geoPrecision)) $geoAgreggation->setPrecision($geoPrecision);
                $agf = new \Elastica\Aggregation\Filter('agf');
                $agf->setFilter($globalFilter);
                $agf->addAggregation($geoAgreggation);
                $elasticaQuery->addAggregation($agf);
                $search->addIndex(self::$_content_index);
                break;
            case 'all' :
            	$searchParams['index'] = self::$_content_index['name'] . ','. self::$_dam_index['name']. ',' . self::$_user_index['name'];
                break;
        }


        // For geosearch dynamically set searchMode depending on the number of results
        if ($option == 'geo' && self::$_isFrontEnd) {
            $noResults = $search->count($elasticaQuery, false);
            if ($noResults > $this->_params['limit']) {
                $this->_params['searchMode'] = 'aggregate';
            } else {
                $this->_params['searchMode'] = 'default';
            }

        }

        // Get resultset
        switch ($this->_params['searchMode']) {
            case 'default':
                $elasticResultSet = $this->_client->search($searchParams);
                break;
            case 'aggregate':
                $elasticResultSet = $search->count($elasticaQuery, true);
                break;
            case 'count':
                $elasticResultSet = $search->count($elasticaQuery, false);
                break;
        }

        // For geosearch get aggregation buckets
        if ($option == 'geo') {

            $result ['Aggregations'] = $elasticResultSet->getAggregation("agf")['hash'];

            foreach ($result ['Aggregations']['buckets'] as $key => $bucket) {
                $point = $this->geoHashDecode($bucket['key']);
                $result ['Aggregations']['buckets'][$key] += $point;
            }
        }

        // Update data
        //$resultsList = $elasticResultSet->getResults();
        $resultsList = $elasticResultSet['hits']['hits'];
        
        $result ['total'] = $elasticResultSet['hits']['total'];
        $result ['query'] = $this->_params ['query'];
        $userWriteWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
        $userCanWriteContents = Manager::getService('Acl')->hasAccess("write.ui.contents");
        $userCanWriteDam = Manager::getService('Acl')->hasAccess("write.ui.dam");

        $writeWorkspaceArray = Manager::getService('CurrentUser')->getWriteWorkspaces();

        foreach ($resultsList as $resultItem) {

            $data = $resultItem['fields'];

            $resultData ['id'] = $resultItem['_id'];
            $resultData ['typeId'] = $resultItem['_type'];
            $score = $resultItem['_score'];
            if (!is_float($score))
                $score = 1;
            $resultData ['score'] = round($score * 100);
            $resultData ['authorName'] = isset ($data ['createUser.fullName'] [0]) ? $data ['createUser.fullName'] [0] : null;
            $resultData ['author'] = isset ($data ['createUser.id'] [0]) ? $data ['createUser.id'] [0] : null;
            $resultData ['version'] = isset ($data ['version'] [0]) ? $data ['version'] [0] : null;
            $resultData ['photo'] = isset ($data ['photo'] [0]) ? $data ['photo'] [0] : null;
            $resultData ['objectType'] = $data ['objectType'] [0];
            unset ($data ['objectType']);
            unset ($data ['photo']);

            if (isset ($data ['availableLanguages'] [0])) {
                if (!is_array($data ['availableLanguages'] [0])) {
                    $resultData ['availableLanguages'] = array(
                        $data ['availableLanguages'] [0]
                    );
                } else {
                    $resultData['availableLanguages'] = $data ['availableLanguages'] [0];
                }
            }

            switch ($resultData ['objectType']) {
                case 'content' :
                    if (isset ($data ["i18n." . $currentLocale . ".fields.text"][0])) {
                        $resultData ['title'] = $data ["i18n." . $currentLocale . ".fields.text"][0];
                        if ($withSummary) {
                            $resultData ['summary'] = (isset ($data ["i18n." . $currentLocale . ".fields.summary"][0])) ? $data ["i18n." . $currentLocale . ".fields.summary"][0] : "";
                        }
                    } else {
                        $resultData ['title'] = $data ['text'] [0];
                    }
                    $contentType = $this->_getContentType($data ['contentType'] [0]);
                    if (!$userCanWriteContents || $contentType ['readOnly']) {
                        $resultData ['readOnly'] = true; 
                   // } elseif ( !in_array($data['writeWorkspace'][0], $userWriteWorkspaces) ) {
                        } elseif ( !in_array('global', $userWriteWorkspaces) ) {
                        $resultData ['readOnly'] = true;
                    }
                    $resultData ['type'] = $contentType ['type'];
                    break;
                case 'dam' :
                    if (isset ($data ["i18n." . $currentLocale . ".fields.title"][0])) {
                        $resultData ['title'] = $data ["i18n." . $currentLocale . ".fields.title"][0];
                    } else {
                        $resultData ['title'] = $data ['text'] [0];
                    }
                    $damType = $this->_getDamType($data ['damType'] [0]);
                    if (!$userCanWriteDam || $damType ['readOnly']) {
                        $resultData ['readOnly'] = true;
                    //} elseif (!in_array($data['writeWorkspace'][0], $userWriteWorkspaces)) {
                    } elseif (!in_array('global', $userWriteWorkspaces)) {
                        $resultData ['readOnly'] = true;
                    }
                    $resultData ['type'] = $damType ['type'];
                    break;
                case 'user' :

                    if (isset ($data ["fields.name"] [0])) {
                        $resultData ['name'] = $data ["fields.name"] [0];
                    } else {
                        $resultData ['name'] = $data ['email'] [0];
                    }
                    $resultData ['title'] = $resultData ['name'];
                    $userType = $this->_getUserType($data ['userType'] [0]);
                    $resultData ['type'] = $userType ['type'];
                    break;
            }
            

            // ensure that date is formated as timestamp while handled as date
            // type for ES
            $data ['lastUpdateTime'] = strtotime($data ['lastUpdateTime'] [0]);

            // Set read only

            if (!isset ($data ['writeWorkspace'] [0]) or in_array($data ['writeWorkspace'] [0], $writeWorkspaceArray)) {
                $resultData ['readOnly'] = false;
            } else {
                $resultData ['readOnly'] = true;
            }

            $result ['data'] [] = array_merge($resultData, $data);
        }

        // Add label to Facets, hide empty facets,
        
        $elasticaFacetsTemp = $elasticResultSet['aggregations'];

        $elasticaFacets = array();
        if ((is_array($this->_displayedFacets)) && (!empty ($this->_displayedFacets)) && (!is_string($this->_displayedFacets [0]))) {
            foreach ($this->_displayedFacets as $requestedFacet) {
                foreach ($elasticaFacetsTemp as $id => $obtainedFacet) {
                    if ($id == $requestedFacet ["name"]) {
                        $elasticaFacets [$id] = $obtainedFacet;
                    }
                }
            }
        } else {
            $elasticaFacets = $elasticaFacetsTemp;
        }
        $result ['facets'] = array();

        foreach ($elasticaFacets as $id => $facet) {
            $temp = ( array )$facet;
            $renderFacet = true;
            if (!empty ($temp)) {
                $temp ['id'] = $id;
                //var_dump($temp);
                switch ($id) {
                    case 'navigation' :

                        $temp ['label'] = Manager::getService('Translate')->translate("Search.Facets.Label.Navigation", 'Navigation');
                        if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
                            foreach ($temp ['buckets'] as $key => $value) {
                                $termItem = $taxonomyTermsService->getTerm($value ['key'], 'navigation');
                                $temp ['terms'] [$key] ['label'] = $termItem ["Navigation"];
                            }
                        } else {
                            $renderFacet = false;
                        }
                        break;

                    case 'damType' :

                        $temp ['label'] = Manager::getService('Translate')->translate("Search.Facets.Label.MediaType", 'Media type');
                        if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
                            foreach ($temp ['terms'] as $key => $value) {
                                $termItem = $this->_getDamType($value ['term']);
                                if ($termItem && isset ($termItem ['type'])) {
                                    $temp ['terms'] [$key] ['label'] = $termItem ['type'];
                                }
                            }
                        } else {
                            $renderFacet = false;
                        }
                        break;

                    case 'objectType' :

                        $temp ['label'] = Manager::getService('Translate')->translate("Search.Facets.Label.DataType", 'Data type');
                        foreach ($temp ['buckets'] as $key => $value) {
                        	$temp ['terms'] [$key] ['term'] = $value ['key'];
                            $temp ['terms'] [$key] ['label'] = Manager::getService('Translate')->translate("Search.Facets.Label.".strtoupper($value ["key"]), strtoupper($value ["key"]));
                            $temp['terms'] [$key] ['count'] = $value['doc_count'];
                        }
                        break;

                    case 'type' :

                        $temp ['label'] = Manager::getService('Translate')->translate("Search.Facets.Label.ContentType", 'Content type');
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

                        $temp ['label'] = Manager::getService('Translate')->translate("Search.Facets.Label.UserType", 'User type');
                        if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
                            foreach ($temp ['buckets'] as $key => $value) {

                                $termItem = $this->_getUserType($value ['key']);
                                $temp ['terms'] [$key] ['label'] = $termItem ['type'];
                            }
                        } else {
                            $renderFacet = false;
                        }
                        break;

                    case 'author' :

                        $temp ['label'] = Manager::getService('Translate')->translate("Search.Facets.Label.Author", 'Author');
                        if ($this->_facetDisplayMode == 'checkbox' or (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0)) {
                            $collection = Manager::getService('Users');
                            foreach ($temp ['buckets'] as $key => $value) {
                                $termItem = $collection->findById($value ['key']);
                                $temp ['terms'] [$key] ['label'] = $termItem ['name'];
                            }
                        } else {
                            $renderFacet = false;
                        }
                        break;

                    case 'userName' :

                        $temp ['label'] = Manager::getService('Translate')->translate("Search.Facets.Label.UserName", 'User Name');
                        foreach ($temp ['buckets'] as $key => $value) {
                            $temp ['terms'] [$key] ['label'] = strtoupper($value ["key"]);
                        }

                        break;

                    case 'lastupdatetime' :

                        $temp ['label'] = Manager::getService('Translate')->translate("Search.Facets.Label.ModificationDate", 'Modification date');
                        if (array_key_exists('ranges', $temp) and count($temp ['ranges']) > 0) {
                            foreach ($temp ['ranges'] as $key => $value) {
                                $rangeCount = $temp ['ranges'] [$key] ['count'];
                                // unset facet when count = 0 or total results
                                // count when display mode is not set to
                                // checkbox
                                if ($this->_facetDisplayMode == 'checkbox' or ($rangeCount > 0 and $rangeCount <= $result ['total'])) {
                                    $temp ['ranges'] [$key] ['label'] = $timeLabel [( string )$temp ['ranges'] [$key] ['from']];
                                } else {
                                    unset ($temp ['ranges'] [$key]);
                                }
                            }
                        } else {
                            $renderFacet = false;
                        }

                        $temp ["ranges"] = array_values($temp ["ranges"]);

                        break;

                    default :
                        $regex = '/^[0-9a-z]{24}$/';
                        if (preg_match($regex, $id)) { // Taxonomy facet use
                            // mongoID
                            $vocabularyItem = Manager::getService('Taxonomy')->findById($id);
                            $temp ['label'] = $vocabularyItem ['name'];
                            if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
                                foreach ($temp ['buckets'] as $key => $value) {
                                    $termItem = $taxonomyTermsService->findById($value ['key']);
                                    if ($termItem) {
                                        $temp ['terms'] [$key] ['label'] = $termItem ['text'];
                                    } else {
                                        unset ($temp ['buckets'] [$key]);
                                    }
                                }
                            } else {
                                $renderFacet = false;
                            }
                        } else {
                            // faceted field
                            $intermediaryVal = $this->searchLabel($facetedFields, "name", $id);
                            $temp ['label'] = $intermediaryVal [0] ['label'];

                            if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
                                foreach ($temp ['buckets'] as $key => $value) {
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

        // Add label to filters

        $result ['activeFacets'] = array();
        if (is_array($this->_filters)) {
            foreach ($this->_filters as $id => $termId) {
                switch ($id) {

                    case 'damType' :
                        $temp = array(
                            'id' => $id,
                            'label' => Manager::getService('Translate')->translate("Search.Facets.Label.MediaType", 'Media type')
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
                            'label' => Manager::getService('Translate')->translate("Search.Facets.Label.ContentType", 'Content type')
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
                            'label' => Manager::getService('Translate')->translate("Search.Facets.Label.UserType", 'User type')
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
                            'label' => Manager::getService('Translate')->translate("Search.Facets.Label.Author", 'Author')
                        );
                        foreach ($termId as $term) {
                            $termItem = Manager::getService('Users')->findById($term);
                            $temp ['terms'] [] = array(
                                'term' => $term,
                                'label' => $termItem ['name']
                            );
                        }

                        break;

                    case 'userName' :
                        $temp = array(
                            'id' => $id,
                            'label' => Manager::getService('Translate')->translate("Search.Facets.Label.UserName", 'User Name')
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
                                    'label' => $timeLabel [( string )$termId]
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
                            $vocabularyItem = Manager::getService('Taxonomy')->findById($id);

                            $temp = array(
                                'id' => $id,
                                'label' => $vocabularyItem ['name']
                            );

                            foreach ($termId as $term) {
                                $termItem = $taxonomyTermsService->findById($term);
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
        $currentLocale = Manager::getService('CurrentLocalization')->getCurrentLocalization();

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

        $path = self::$_content_index->getName() . '/_suggest';
        $suggestion = $client->request($path, 'GET', $query);
        $responseArray = $suggestion->getData()["autocomplete"][0]["options"];

        // get suggest from dam
        $path = self::$_dam_index->getName() . '/_suggest';
        $suggestion = $client->request($path, 'GET', $query);
        if (isset ($suggestion->getData()["autocomplete"][0]["options"])) {
            $responseArray = array_merge($responseArray, $suggestion->getData()["autocomplete"][0]["options"]);
        }

        // get suggest from user
        $path = self::$_user_index->getName() . '/_suggest';
        $suggestion = $client->request($path, 'GET', $nonlocalizedquery);
        if (isset ($suggestion->getData()["autocomplete"][0]["options"])) {
            $responseArray = array_merge($responseArray, $suggestion->getData()["autocomplete"][0]["options"]);
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

