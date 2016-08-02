<?php

/**
 * Rubedo -- ECM solution
 * Copyright (c) 2016, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr.
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 *
 * @copyright  Copyright (c) 2012-2016 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace Rubedo\Elastic;

use Zend\Json\Json;

/**
 * Class implementing the Rubedo API to Elastic Search using elastic API.
 *
 * @author dfanchon
 *
 * @category Rubedo
 */
class DataSearch extends DataAbstract
{
    /**
     * Is the context a front office rendering ?
     *
     * @var bool
     */
    protected static $_isFrontEnd;
    private $_fieldsToRemove = [];

    /**
     * ES search.
     *
     * @param array $params search parameters : query, type, damtype,
     *                      lang, author, date, taxonomy, target, pager, orderby, pagesize
     *
     * @return array
     */
    public function search(array $params, $option = 'all', $withSummary = true)
    {
        $fingerprint = isset($params['fingerprint']) ? $params['fingerprint'] : null;
        $isMagic = isset($params['isMagic']) ? $params['isMagic'] : false;

        // reset search context
        SearchContext::resetContext();

        // get facet display mode
        $facetDisplayMode = isset($params ['block-config'] ['displayMode']) ? $params ['block-config'] ['displayMode'] : 'standard';

        // save mode in search context
        SearchContext::setFacetDisplayMode($facetDisplayMode);

        $displayedFacets = [];

        // front-end search
        if ((self::$_isFrontEnd)) {

            // save font-end search context
            SearchContext::setIsFrontEnd(true);

            // get list of displayed Facets
            $displayedFacets = isset($params ['block-config'] ['displayedFacets']) ? $params ['block-config'] ['displayedFacets'] : [];

            if (is_string($displayedFacets)) {
                if ((empty($displayedFacets)) || ($displayedFacets == "['all']")) {
                    $displayedFacets = [
                        'all',
                    ];
                } else {
                    $displayedFacets = Json::decode($displayedFacets, Json::TYPE_ARRAY);
                }
            }

            // get current user language
            $currentLocale = $this->_getService('CurrentLocalization')->getCurrentLocalization();

            // get site localization strategy
            $localizationStrategy = $this->_getService('Taxonomy')->getLocalizationStrategy();

            // get locale fall back
            $fallBackLocale = $this->_getService('Taxonomy')->getFallbackLocale();

            // if there is any facet to display, get overrides
            if (!empty($displayedFacets)) {
                $facetOperators = [];

                // check if facetOverrides exists
                $facetOverrides = isset($params ['block-config'] ['facetOverrides']) ? (Json::decode($params ['block-config'] ['facetOverrides'], Json::TYPE_ARRAY)) : [];

                if (!empty($facetOverrides)) { // This code is only for 2.0.x backward compatibility

                    foreach ($facetOverrides as $facet) {
                        if ($displayedFacets == ['all'] or in_array($facet ['id'], $displayedFacets)) {
                            if ($facet ['id'] == 'contentType') {
                                $facet ['id'] = 'type';
                            }
                            $facetOperators [$facet ['id']] = strtolower($facet ['facetOperator']);
                        }
                    }
                } else {

                    // if all facets are displayed
                    if ($displayedFacets == ['all']) {
                        // get facets operators from all taxonomies
                        $taxonomyList = $this->_getService('Taxonomy')->getList();

                        foreach ($taxonomyList ['data'] as $taxonomy) {
                            $facetOperators [$taxonomy ['id']] = isset($taxonomy ['facetOperator']) ? strtolower($taxonomy ['facetOperator']) : 'and';
                        }
                    } else {
                        // otherwise get facets operators from displayed facets only
                        foreach ($displayedFacets as $facet) {

                            // Get facet operator from block
                            if ($facet ['operator']) {
                                $facetOperators [$facet ['name']] = strtolower($facet ['operator']);
                            } else {
                                // Get default facet operator from taxonomy if not present in block configuration
                                if (preg_match('/[\dabcdef]{24}/', $facet ['name']) == 1 || $facet ['name'] == 'navigation') {
                                    $taxonomy = $this->_getService('Taxonomy')->findById($facet ['name']);
                                    if ($taxonomy) {
                                        $facetOperators [$facet ['name']] = isset($taxonomy ['facetOperator']) ? strtolower($taxonomy ['facetOperator']) : 'and';
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

        // save displayed facets in search context
        SearchContext::setDisplayedFacets($displayedFacets);

        // Get taxonomies
        $taxonomyList = $this->_getService('Taxonomy')->getList();
        $taxonomies = $taxonomyList ['data'];

        // Get faceted fields from contents, users and dam
        $contentFacetedFields = $this->_getService('ContentTypes')->getFacetedFields();
        $userFacetedFields = $this->_getService('DamTypes')->getFacetedFields();
        $damFacetedFields = $this->_getService('UserTypes')->getFacetedFields();
        $facetedFields = array_merge($contentFacetedFields, $userFacetedFields, $damFacetedFields);
        foreach ($facetedFields as $facetedField) {
            // get default facet operator from faceted field if not present in block configuration
            if (!isset($facetOperators [$facetedField ['name']])) {
                $facetOperators [$facetedField ['name']] = $facetedField ['facetOperator'];
            }
        }
        SearchContext::setFacetedFields($facetedFields);
        SearchContext::setFacetOperators($facetOperators);
        $result = [];
        $result ['data'] = [];

        // Default parameters
        $defaultVars = [
            'query' => '',
            'pager' => 0,
            'orderby' => '_score',
            'orderbyDirection' => 'desc',
            'pagesize' => 25,
            'searchMode' => 'default',
        ];
        $searchParams = [];

        // set default options
        if (!array_key_exists('lang', $params)) {
            $session = $this->_getService('Session');
            $params ['lang'] = $session->get('lang', 'fr');
        }

        foreach ($defaultVars as $varKey => $varValue) {
            if (!array_key_exists($varKey, $params)) {
                $params [$varKey] = $varValue;
            }
        }

        $params ['query'] = strip_tags($params ['query']);

        // Build filters
        $filterFactory = new DataFilters();

        // System filters
        $systemFilters = [
            'query' => '',
            'frontEndFilters' => '',
            'objectType' => 'objectType',
            'type' => 'contentType',
            'damType' => 'damType',
            'userType' => 'userType',
            'author' => 'createUser.id',
            'userName' => 'first_letter',
            'lastupdatetime' => 'lastUpdateTime',
            'price' => 'price',
            'inStock' => 'inStock',
            'isProduct' => 'isProduct',
        ];

        foreach ($systemFilters as $name => $field) {
            if (array_key_exists($name, $params)) {
                $filterFactory->addFilter($name, $params[$name], $field);
            }
        }

        // Frontend filters, for contents only: online, start and end publication date
        if ((self::$_isFrontEnd) && ($option != 'user') && ($option != 'dam')) {
            $filterFactory->addFilter('frontend');
        }

        // Read Workspaces
        $readWorkspaceArray = $this->_getService('CurrentUser')->getReadWorkspaces();
        if (($option != 'user') && (!in_array('all', $readWorkspaceArray)) && (!empty($readWorkspaceArray))) {
            $filterFactory->addFilter('workspaceFilter', $readWorkspaceArray);
        }

        // add filter for geo search on content types with 'position' field
        if ($option == 'geo') {
            list($geoFilter, $geoAgreggation) = $filterFactory->addFilter('geo');
        }

        // Geolocation
        if (isset($params ['inflat']) && isset($params ['suplat']) && isset($params ['inflon']) && isset($params ['suplon'])) {
            $coordinates = [
                'inflat' => $params ['inflat'],
                'suplat' => $params ['suplat'],
                'inflon' => $params ['inflon'],
                'suplon' => $params ['suplon'],
            ];
            list($geoBoundingBoxFilter, $geoPrecision) = $filterFactory->addFilter('geoBoxFilter', $coordinates);
        }

        // Taxonomies
        foreach ($taxonomies as $taxonomy) {
            $vocabulary = $taxonomy ['id'];

            if (array_key_exists($vocabulary, $params)) {
                // transform param to array if single value
                if (!is_array($params [$vocabulary])) {
                    $params [$vocabulary] = [
                        $params [$vocabulary]
                    ];
                }
                foreach ($params [$vocabulary] as $term) {
                    $filterFactory->addTermsFilter($vocabulary, 'taxonomy.'.$vocabulary, $term);
                }
            }
        }

        // Faceted Fields
        foreach ($facetedFields as $field) {
            if (array_key_exists(urlencode($field ['name']), $params)) {
                if ($field ['useAsVariation']) {
                    $fieldName = 'productProperties.variations.'.$field ['name'];
                } else {
                    if ($field['localizable']) {
                        $fieldName = 'i18n.'.$currentLocale.'.fields.'.$field ['name'];
                    } else {
                        $fieldName = 'fields.'.$field ['name'];
                    }
                }
                $filterFactory->addTermsFilter($field ['name'], $fieldName, $params[urlencode($field ['name'])]);
            }
        }

        // Localization
        if ($option != 'user') {
            switch ($localizationStrategy) {
                case 'backOffice' :
                    $elasticQueryString = [
                        'fields' => [
                            'all_'.$currentLocale,
                            '_all^0.1',
                        ],
                    ];
                    break;
                case 'onlyOne' :
                    $filterFactory->addFilter('locale', [$currentLocale]);
                    $elasticQueryString = [
                        'fields' => [
                            'all_'.$currentLocale,
                            'all_nonlocalized',
                            '_all',
                        ],
                    ];
                    break;

                case 'fallback' :
                default :
                    $filterFactory->addFilter('locale', [$currentLocale, $fallBackLocale]);
                    if ($currentLocale != $fallBackLocale) {
                        $elasticQueryString = [
                            'fields' => [
                                'all_'.$currentLocale,
                                'all_'.$fallBackLocale.'^0.1',
                                'all_nonlocalized^0.1',
                                '_all',
                               ],
                        ];
                    } else {
                        $elasticQueryString = [
                            'fields' => [
                                'all_'.$currentLocale,
                                'all_nonlocalized',
                                '_all',
                            ],
                        ];
                    }
                    break;
            }
        } else {

            // user search do not use localization
            $elasticQueryString = [
                'fields' => [
                    'all_nonlocalized',
                ],
            ];
        }

        // Query string
        if ($params ['query'] != '') {
            $elasticQueryString['query'] = $params ['query'];
        } else {
            $elasticQueryString['query'] = '*';
        }

        if (!$isMagic or $params ['query'] != '') {
            $searchParams['body']['query']['filtered']['query']['query_string'] = $elasticQueryString;
        } else {
            $historyDepth = isset($params['historyDepth']) ? $params['historyDepth'] : null;
            $historySize = isset($params['historySize']) ? $params['historySize'] : null;
            $significantItems = SearchContext::getSeenItems($fingerprint, $historyDepth, $historySize);
            if (!empty($significantItems)) {
                $searchParams['body']['query']['filtered']['query']['more_like_this'] = [
                    'fields' => ['taxonomy.*'],
                    'docs' => $significantItems,
                    'min_term_freq' => 1,
                ];
            } else {
                $searchParams['body']['query']['filtered']['query']['query_string'] = $elasticQueryString;
            }
        }

        // Apply filter to query and aggregations
        $globalFilter = [];
        $globalFilterList = SearchContext::getGlobalFilterList();
        if (!empty($globalFilterList)) {
            foreach ($globalFilterList as $filter) {
                $globalFilter['and'][] = $filter;
            }
            $searchParams['body']['query']['filtered']['filter'] = $globalFilter;
        }

        // Build facets
        $facetFactory = new DataAggregations();

        // Add system facets
        $systemFacets = [
            'objectType' => 'objectType',
            'type' => 'contentType',
            'damType' => 'damType',
            'userType' => 'userType',
            'author' => 'createUser.id',
            'userName' => 'first_letter',
            'lastupdatetime' => 'lastUpdateTime',
            'inStock' => 'productProperties.variations.stock',
            'price' => 'productProperties.variations.price',
        ];

        foreach ($systemFacets as $name => $field) {
            $facetFactory->addAggregation($name, $field);
        }

        // Define taxonomy facets
        foreach ($taxonomies as $taxonomy) {
            $vocabulary = $taxonomy ['id'];

            if ($facetFactory->isFacetDisplayed($vocabulary)) {
                $facetFactory->addTermsFacet($vocabulary, 'taxonomy.'.$taxonomy ['id'], '_count', 'desc', 100);
            }
        }

        // Define the fields facets
        foreach ($facetedFields as $field) {
            if ($facetFactory->isFacetDisplayed($field ['name'])) {
                if ($field ['useAsVariation']) {
                    $fieldName = 'productProperties.variations.'.$field ['name'];
                } else {
                    if (!$field ['localizable']) {
                        $fieldName = 'fields.'.$field ['name'];
                    } else {
                        $fieldName = 'i18n.'.$currentLocale.'.fields.'.$field ['name'];
                    }
                }
                $facetFactory->addTermsFacet($field ['name'], $fieldName, '_count', 'desc');
            }
        }

        // Geosearch
        if ($option == 'geo') {
            $facetFactory->addGeoFacet($geoPrecision);
        }

        // Add aggs to search params
        $searchParams['body']['aggs'] = SearchContext::getAggs();

        // Add size and from to paginate results
        if (isset($params['start']) && isset($params['limit'])) {
            $searchParams['body']['size'] = $params ['limit'];
            $searchParams['body']['from'] = $params ['start'];
        } else {
            if (is_numeric($params ['pagesize'])) {
                $searchParams['body']['size'] = $params ['pagesize'];
                $searchParams['body']['from'] = $params ['pager'] * $params ['pagesize'];
            }
        }

        // add sort
        if (!isset($params ['customSort'])) {
            // order by field value
            $searchParams['body']['sort'] = [
                [$params ['orderby'] => ['order' => strtolower($params ['orderbyDirection']), 'ignore_unmapped' => true]],
            ];
        } else {
            // custom sort object
            $searchParams['body']['sort'] = $params ['customSort'];
        }

        // retrieve all stored fields
        $searchParams['body']['fields'] = ['*'];

        // run query
        switch ($option) {
            case 'content' :
            case 'geo':
                $searchParams['index'] = $this->getIndexNameFromConfig('contentIndex');
                break;
            case 'dam' :
                $searchParams['index'] = $this->getIndexNameFromConfig('damIndex');
                break;
            case 'user' :
                $searchParams['index'] = $this->getIndexNameFromConfig('userIndex');
                break;
            case 'all' :
                $searchParams['index'] = $this->getIndexNameFromConfig('contentIndex').','.$this->getIndexNameFromConfig('damIndex').','.$this->getIndexNameFromConfig('userIndex');
                break;
        }

        $searchParams['_source'] = true;

        // For geosearch dynamically set searchMode depending on the number of results
        if ($option == 'geo' && self::$_isFrontEnd) {
            $countSearchParams = $searchParams;
            $countSearchParams['body']['size'] = 0;
            unset($countSearchParams['body']['aggs']);
            unset($countSearchParams['body']['sort']);
            $elasticResultSet = $this->_client->search($countSearchParams);
            $noResults = $elasticResultSet['hits']['total'];
            if ($noResults > $params['limit']) {
                $params['searchMode'] = 'aggregate';
            } else {
                $params['searchMode'] = 'default';
            }
        }

        // Perform search and get resultset
        switch ($params['searchMode']) {
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
                $point = SearchContext::geoHashDecode($bucket['key']);
                $result ['Aggregations']['buckets'][$key] += $point;
            }
        }

        // Update data
        $resultsList = $elasticResultSet['hits']['hits'];

        $result ['total'] = $elasticResultSet['hits']['total'];
        $result ['query'] = $params ['query'];
        $userWriteWorkspaces = $this->_getService('CurrentUser')->getWriteWorkspaces();
        $userCanWriteContents = $this->_getService('Acl')->hasAccess('write.ui.contents');
        $userCanWriteDam = $this->_getService('Acl')->hasAccess('write.ui.dam');

        foreach ($resultsList as $resultItem) {
            $data = isset($resultItem['_source']['fields']) ? array_merge($resultItem['_source']['fields'], $resultItem['fields']) : $resultItem['fields'];
            $resultData = [
                'id' => $resultItem['_id'],
                'typeId' => $resultItem['_type'],
                'authorName' => isset($data ['createUser.fullName'][0]) ? $data ['createUser.fullName'][0] : null,
                'author' => isset($data ['createUser.id'][0]) ? $data ['createUser.id'][0] : null,
                'version' => isset($data ['version'][0]) ? $data ['version'][0] : null,
                'photo' => isset($data ['photo'][0]) ? $data ['photo'][0] : null,
                'objectType' => $data ['objectType'][0],
                'score' => !is_float($resultItem['_score']) ? 100 : round($resultItem['_score'] * 100),
            ];
            unset($data ['objectType']);
            unset($data ['photo']);

            if (isset($data ['availableLanguages'][0])) {
                if (!is_array($data ['availableLanguages'][0])) {
                    $resultData ['availableLanguages'] = [
                        $data ['availableLanguages'][0],
                    ];
                } else {
                    $resultData['availableLanguages'] = $data ['availableLanguages'][0];
                }
            }

            switch ($resultData ['objectType']) {
                case 'content' :
                    if (isset($data ['i18n.'.$currentLocale.'.fields.text'][0])) {
                        $resultData ['title'] = $data ['i18n.'.$currentLocale.'.fields.text'][0];
                        if ($withSummary) {
                            $resultData ['summary'] = (isset($data ['i18n.'.$currentLocale.'.fields.summary'][0])) ? $data ['i18n.'.$currentLocale.'.fields.summary'][0] : '';
                        }
                    } else {
                        $resultData ['title'] = $data ['text'][0];
                    }
                    $contentType = $this->_getType('ContentTypes', $data ['contentType'][0]);
                    $data = $this->cleanFields($contentType, $data, 'contentType');
                    $resultData ['readOnly'] = $this->getReadOnly($userCanWriteContents, $contentType ['readOnly'], $userWriteWorkspaces);
                    $resultData ['type'] = $contentType ['type'];
                    break;
                case 'dam' :
                    if (isset($data ['i18n.'.$currentLocale.'.fields.title'][0])) {
                        $resultData ['title'] = $data ['i18n.'.$currentLocale.'.fields.title'][0];
                    } else {
                        $resultData ['title'] = $data ['text'][0];
                    }
                    $damType = $this->_getType('DamTypes', $data ['damType'][0]);
                    $data = $this->cleanFields($damType, $data, 'damType');
                    $resultData ['readOnly'] = $this->getReadOnly($userCanWriteDam, $damType ['readOnly'], $userWriteWorkspaces);
                    $resultData ['type'] = $damType ['type'];
                    break;
                case 'user' :
                    if (isset($data ['fields.name'][0])) {
                        $resultData ['name'] = $data ['fields.name'][0];
                    } else {
                        $resultData ['name'] = $data ['email'][0];
                    }
                    $resultData ['title'] = $resultData ['name'];
                    $userType = $this->_getType('UserTypes', $data ['userType'][0]);
                    $data = $this->cleanFields($userType, $data, 'userType');
                    $resultData ['type'] = $userType ['type'];
                    break;
            }

            // ensure that date is formated as timestamp while handled as date
            // type for ES
            $data ['lastUpdateTime'] = strtotime($data ['lastUpdateTime'][0]);

            // Set read only
            if (!isset($data ['writeWorkspace'][0]) or in_array($data ['writeWorkspace'][0], $userWriteWorkspaces)) {
                $resultData ['readOnly'] = false;
            } else {
                $resultData ['readOnly'] = true;
            }

            $result ['data'] [] = array_merge($resultData, $data);
        }

        // Add label to Facets, hide empty facets

        $elasticFacetsTemp = $elasticResultSet['aggregations'];
        $elasticFacets = [];
        if ((is_array($displayedFacets)) && (!empty($displayedFacets)) && (!is_string($displayedFacets [0]))) {
            foreach ($displayedFacets as $requestedFacet) {
                foreach ($elasticFacetsTemp as $id => $obtainedFacet) {
                    if ($id == $requestedFacet ['name']) {
                        $elasticFacets [$id] = $obtainedFacet;
                    }
                }
            }
        } else {
            $elasticFacets = $elasticFacetsTemp;
        }

        $result ['facets'] = [];

        foreach ($elasticFacets as $id => $facet) {
            if (isset($facet['aggregation'])) {
                $temp = $facetFactory->formatFacet($id, $facet, $result['total']);
                if (!is_null($temp)) {
                    $result ['facets'] [] = $temp;
                }
            }
        }

        // Add label to filters
        $result ['activeFacets'] = [];
        $filters = SearchContext::getFilters();
        if (is_array($filters)) {
            foreach ($filters as $id => $termId) {
                $temp = $filterFactory->formatFilter($id, $termId);
                $result ['activeFacets'] [] = $temp;
            }
        }

        return $result;
    }

    /**
     * get autocomplete suggestion.
     *
     * @param array $params
     *                      search parameters : query
     *
     * @return array
     */
    public function suggest(array $params)
    {

        // init response
        $response = [];

        // get current user language
        $currentLocale = $this->_getService('CurrentLocalization')->getCurrentLocalization();

        // query
        $query = [
            'autocomplete' => [
                'text' => $params ['query'],
                'completion' => [
                    'field' => 'autocomplete_'.$currentLocale,
                ],
            ],
        ];

        $nonlocalizedquery = [
            'autocomplete' => [
                'text' => $params ['query'],
                'completion' => [
                    'field' => 'autocomplete_nonlocalized',
                ],
            ],
        ];

        // Get search client
        $client = $this->_client;

        // get suggest from content
        $path = $this->getIndexNameFromConfig('contentIndex').'/_suggest';
        $suggestion = $client->request($path, 'GET', $query);
        $responseArray = $suggestion->getData()['autocomplete'][0]['options'];

        // get suggest from dam
        $path = $this->getIndexNameFromConfig('damIndex').'/_suggest';
        $suggestion = $client->request($path, 'GET', $query);
        if (isset($suggestion->getData()['autocomplete'][0]['options'])) {
            $responseArray = array_merge($responseArray, $suggestion->getData()['autocomplete'][0]['options']);
        }

        // get suggest from user
        $path = $this->getIndexNameFromConfig('userIndex').'/_suggest';
        $suggestion = $client->request($path, 'GET', $nonlocalizedquery);
        if (isset($suggestion->getData()['autocomplete'][0]['options'])) {
            $responseArray = array_merge($responseArray, $suggestion->getData()['autocomplete'][0]['options']);
        }

        foreach ($responseArray as $suggest) {
            $response [] = $suggest;
        }

        return $response;
    }

    /**
     * @param field_type $_isFrontEnd
     */
    public static function setIsFrontEnd($_isFrontEnd)
    {
        self::$_isFrontEnd = $_isFrontEnd;
    }

    protected function cleanFields($type, $data, $typeId)
    {
        if (!isset($this->_fieldsToRemove[$typeId])) {
            $this->_fieldsToRemove[$typeId] = [];
            foreach ($type['fields'] as $field) {
                if (isset($field['config']['returnInSearch']) && $field['config']['returnInSearch'] == false) {
                    $this->_fieldsToRemove[$typeId][] = $field['config']['name'];
                }
            }
        }

        return array_diff_key($data, array_flip($this->_fieldsToRemove[$typeId]));
    }

    protected function getReadOnly($userCanWrite, $objectReadOnly, $userWriteWorkspaces)
    {
        $result = false;
        if (!$userCanWrite || $objectReadOnly) {
            $result = true;
        } elseif (!in_array('global', $userWriteWorkspaces)) {
            $result = true;
        }

        return $result;
    }
}
