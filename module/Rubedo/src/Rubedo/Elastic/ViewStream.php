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
 * Service to handle contentview indexing and searching.
 *
 * @author dfanchon
 *
 * @category Rubedo
 */
class ViewStream extends DataAbstract
{

    protected static $_index = 'insights';
    protected static $_type = 'contentview';
    protected static $_defaultHistoryDuration = 30;
    protected static $_defaultHistorySize = 50;

	/**
     * Mapping.
     */
    protected static $_mapping = [
        'timestamp' => [
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
        'itemId' => [
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes',
        ]
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
     * @param bool $bulk
     *
     * @return array
     */
    public function index($data, $bulk = false)
    {
        if (!isset($data['timestamp'])) {
            $data['timestamp'] = time() * 1000;
        }
        $params = [
            'index' => $this->_indexName,
            'type' => self::$_type,
            'body' => $data,
        ];
        $this->_client->index($params);
        $this->_client->indices()->refresh(['index' => $this->_indexName]);
    }

    /**
     * Get significant items for a given fingerprint and period
     *
     * @param string $fingerprint
     * @param int $historyDuration
     * @return array
     */
    public function getSignificantItems($fingerprint, $historyDuration = null, $size = null)
    {
        $duration = isset($historyDuration) ? $historyDuration : self::$_defaultHistoryDuration;
        $size = isset($size) ? $size : self::$_defaultHistorySize;
        $durationMask = sprintf("now-%dd/d", $duration);
        $indexMask =  implode("-",explode("-",$this->_indexName,-1))."-*";
        $params = [
            'index' => $indexMask,
            'type' => self::$_type,
            'size' => 0,
            'body' => [
                "query" => [
                    "constant_score" => [
                        "filter" => [
                            "bool" => [
                                "must" => [
                                    ["term" => ["fingerprint" => $fingerprint]],
                                    ["range" => ["timestamp" => ["gte" => $durationMask]]]
                                ]
                            ]
                        ]
                    ]
                ],
                "aggs" => [
                    "significantViews" => [
                        "significant_terms" => [
                            "field" => "itemId",
                            "min_doc_count" => 1,
                            'size' => $size,
                        ]
                    ]
                ]
            ]
        ];
        $results = $this->_client->search($params);
        $significantItems = [];
        foreach($results["aggregations"]["significantViews"]["buckets"] as $item) {
            list($typeId,$itemId) = explode("-", $item["key"]);
            $significantItems[] = [
                "_type" => $typeId,
                "_id" => $itemId
            ];
        }
        return $significantItems;
    }

    /**
     * Get recommended items for a given fingerprint and views history
     *
     * @param string $fingerprint
     * @param array $viewsHistory
     *
     * @return array
     */
    public function getRecommendedItems($fingerprint, $viewsHistory)
    {
        $params = [
            'index' => $this->_indexName,
            'type' => self::$_type,
            'body' => [
                "query" => [
                    "term" => [
                        "fingerprint" => $fingerprint
                    ]
                ],
                "aggs" => [
                    "significantViews" => [
                        "significant_terms" => [
                            "field" => "contentId"
                        ]
                    ]
                ]
            ]
        ];
        $results = $this->_client->search($params);
        $significantContentIds = [];
        foreach($results['hits']['hits'] as $key => $result) {
            $significantContentIds[] = $result['_source']['contentId'];
        }
        return $significantContentIds;
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

}
