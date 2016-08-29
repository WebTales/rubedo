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
        'Browser version' => 'browserVersion',
        'City' => 'city',
        'Country' => 'country',
        'Os' => 'os',
        'Referering Domain' => 'refereringDomain',
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
        $indexMask =  implode("-",explode("-",$this->_indexName,-1))."-*";
        $params = [
            'index' => $indexMask,
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
    public function getDateHistogramAgg($startDate, $endDate, $interval, $events)
    {
        $indexMask =  implode("-",explode("-",$this->_indexName,-1))."-*";
        $params = [
            'index' => $indexMask,
            'type' => self::$_type,
            'size' => 0,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['range' => [
                                'date' => [
                                    'gte' => $startDate,
                                    'lte' => $endDate
                                ]
                            ]],
                            ['terms' => ['event' => $events]],
                        ]
                    ]
                ],
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
        $results = $this->_client->search($params);
        return isset($results['aggregations']['events']['buckets']) ? $results['aggregations']['events']['buckets'] : [];
    }

}
