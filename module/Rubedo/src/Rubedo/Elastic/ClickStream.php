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

/**
 * Service to handle clickstream indexing and searching.
 *
 * @author dfanchon
 *
 * @category Rubedo
 */
class ClickStream extends DataAbstract
{
    protected static $_index = 'insights';
    protected static $_type = 'clickstream';
    protected static $_indexMask;

    /**
     * Mapping.
     */
    protected static $_mapping = [
        'objectType' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        '@timestamp' => [
            'type' => 'date',
            'store' => 'yes',
        ],
        'date' => [
            'type' => 'date',
            'store' => 'yes',
        ],
        'fingerprint' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'sessionId' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'event' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'browser' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'browserVersion' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'city' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'country' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'os' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'referer' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'refereringDomain' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'region' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ],
        'screenHeight' => [
            'type' => 'integer',
            'store' => 'yes',
        ],
        'screenWidth' => [
            'type' => 'integer',
            'store' => 'yes',
        ],
        'geoip' => [
            'type' => 'geo_point',
            'store' => 'yes',
        ],
    ];


    /**
     * Facets.
     */
    protected static $_facets = [
        'Event' => 'event',
        'Browser' => 'browser',
        'Browser Version' => 'browserVersion',
        'City' => 'city',
        'Country' => 'country',
        'OS' => 'os',
        'Refering Domain' => 'refereringDomain',
        'Region' => 'region',
        'Screen Height' => 'screenHeight',
        'Screen Width' => 'screenWidth'
    ];

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // Get index name
        $today = date('Y.m.d');
        $dataAccess = $this->_getService('MongoDataAccess');
        $defaultDB = $dataAccess::getDefaultDb();
        $defaultDB = mb_convert_case($defaultDB, MB_CASE_LOWER, 'UTF-8');
        $this->_indexName = $defaultDB.'-'.self::$_index.'-'.$today;
        parent::init();
        // Create type and mapping if necessary
        $params = [
                'index' => $this->_indexName,
                'type' => self::$_type,
        ];
        if (!$this->_client->indices()->existsType($params)) {
            $this->putMapping(self::$_type, self::$_mapping);
        }
        self::$_indexMask = implode("-",explode("-",$this->_indexName,-1))."-*";
    }

    /**
     * Index.
     *
     * @param obj  $data content data
     *
     * @return array
     */
    public function index($data)
    {
        // Add timestamp if needed
        if (!isset($data['timestamp'])) {
            $data['timestamp'] = time() * 1000;
        }
        $data['objectType'] = 'event';
        // Add content to clickstream index
        $params = [
            'index' => $this->_indexName,
            'type' => self::$_type,
            'body' => $data,
        ];
        $this->_client->index($params);

        $this->_client->indices()->refresh(['index' => $this->_indexName]);
    }

    /**
     * Delete existing content from index.
     *
     * @param string $typeId
     *                       content type id
     * @param string $id
     *                       content id
     */
    public function delete($typeId, $id)
    {
        $params = [
            'index' => $this->_indexName,
            'type' => $typeId,
            'id' => $id,
        ];
        $this->_client->delete($params);
    }

    /**
     * Get event list
     *
     * @return array
     */
    public function getEventList()
    {
        $params = [
            'index' => self::$_indexMask,
            'type' => self::$_type,
            'size' => 0,
            'body' => [
                'query' => [
                    'match_all' => []
                ],
                'aggs' => [
                    'events' => [
                        'terms' => [
                            'field' => 'event',
                            'min_doc_count' => 0,
                            'size' => 1000,
                        ]
                    ]
                ]
            ]
        ];
        $results = $this->_client->search($params);
        if (isset($results['aggregations']['events']['buckets'])) {
            return array_column($results['aggregations']['events']['buckets'],'key');
        } else {
            return [];
        }
    }

    /**
     * Get facet list
     *
     * @return array
     */
    public function getFacetList()
    {
        return self::$_facets;
    }

    /**
     * Date histogram aggregations for events
     *
     * @return array
     */
    public function getDateHistogramAgg($startDate, $endDate, $interval, $filters = [])
    {
        $params = [
            'index' => self::$_indexMask,
            'type' => self::$_type,
            'size' => 0,
            'body' => [
                'aggs' => [
                    'events' => [
                        'terms' => [
                            'field' => 'event',
                        ],
                        'aggs' => [
                            'dateHistogram' => [
                                'date_histogram' => [
                                    'field' => 'date',
                                    'interval' => $interval
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        // Reset search context
        SearchContext::resetContext();
        // Set facet operator for events
        SearchContext::setFacetOperator('event','or');
        // Build date filter
        $filterFactory = new DataFilters();
        $filterFactory->addDateRangeFilter('date', 'date', $startDate, $endDate);
        // Build facets filters
        foreach (self::$_facets as $key => $field) {
            if (array_key_exists($field, $filters)) {
                $filterFactory->addFilter($field, $filters[$field], $field);
            }
        }
        // Set filters
        $globalFilter = [];
        $globalFilterList = SearchContext::getGlobalFilterList();
        if (!empty($globalFilterList)) {
            foreach ($globalFilterList as $filter) {
                $globalFilter['and'][] = $filter;
            }
            $params['body']['query']['filtered']['filter'] = $globalFilter;
        }
        // Build facets
        $facetFactory = new DataAggregations();
        foreach (self::$_facets as $key => $field) {
            $facetFactory->addAggregation($field, $field);
        }
        // Set facets
        $params['body']['aggs'] = array_merge($params['body']['aggs'], SearchContext::getAggs());

        $results = $this->_client->search($params);
        return isset($results['aggregations']) ? $results['aggregations'] : [];
    }

    /**
     * Get a given facet for a given event
     *
     * @return array
     */
    public function getEventFacet($startDate, $endDate, $facet, $event)
    {
        $params = [
            'index' => self::$_indexMask,
            'type' => self::$_type,
            'size' => 0,
            'body' => [
                'query' => [
                    'filtered' => [
                        'filter' => [
                            'and' => [
                                ['range' => [
                                    'date' => [
                                        'gte' => $startDate,
                                        'lte' => $endDate
                                    ]
                                ]],
                                ['term' => ['event' => $event]],
                            ]
                        ]
                    ]
                ],
                'aggs' => [
                    $facet => [
                        'terms' => [
                            'field' => $facet,
                        ]
                    ]
                ]
            ]
        ];
        $results = $this->_client->search($params);
        return isset($results['aggregations'][$facet]['buckets']) ? $results['aggregations'][$facet]['buckets'] : [];
    }

    /**
     * Date histogram aggregations for events
     *
     * @return array
     */
    public function getGeoAgg($startDate, $endDate, $filters = [])
    {
        $params = [
            'index' => self::$_indexMask,
            'type' => self::$_type,
            'size' => 0
        ];
        // Reset search context
        SearchContext::resetContext();
        // Set facet operator for events
        SearchContext::setFacetOperator('event','or');
        // Build date filter
        $filterFactory = new DataFilters();
        $filterFactory->addDateRangeFilter('date', 'date', $startDate, $endDate);
        // Build geo filter
        $coordinates = [
            'inflat' => $filters['inflat'],
            'suplat' => $filters['suplat'],
            'inflon' => $filters['inflon'],
            'suplon' => $filters['suplon'],
        ];
        list($geoBoundingBoxFilter, $geoPrecision) = $filterFactory->addFilter('geoBoxFilter', $coordinates, 'geoip');
        $params['body']['aggs']['hash']['geohash_grid'] = [
            'field' => 'geoip',
            'precision' => $geoPrecision
        ];
        // Build facets filters
        foreach (self::$_facets as $field) {
            if (array_key_exists($field, $filters)) {
                $filterFactory->addFilter($field, $filters[$field], $field);
            }
        }
        // Set filters
        $globalFilter = [];
        $globalFilterList = SearchContext::getGlobalFilterList();
        if (!empty($globalFilterList)) {
            foreach ($globalFilterList as $filter) {
                $globalFilter['and'][] = $filter;
            }
            $params['body']['query']['filtered']['filter'] = $globalFilter;
        }
        // Build facets
        $facetFactory = new DataAggregations();
        foreach (self::$_facets as $name => $field) {
            $facetFactory->addAggregation($field, $field);
        }
        // Set facets
        $params['body']['aggs'] = array_merge($params['body']['aggs'], SearchContext::getAggs());
        // Run query
        $results = $this->_client->search($params);
        // Add geographic info
        foreach ($results ['aggregations']['hash']['buckets'] as $key => $bucket) {
            $point = SearchContext::geoHashDecode($bucket['key']);
            $results ['aggregations']['hash']['buckets'][$key] += $point;
        }
        return isset($results['aggregations']) ? $results['aggregations'] : [];
    }

}
