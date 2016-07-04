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

	/**
     * Mapping.
     */
    protected static $_mapping = [
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
        'contentId' => [
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
        // Add timestamp if needed
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

        var_dump($this->getSignificantItems($data["fingerprint"]));
    }

    /**
     * Get significant items for a given fingerprint and period
     *
     * @param string $fingerprint
     * @param int $historyDuration
     * @return array
     */
    public function getSignificantItems($fingerprint, $historyDuration = null)
    {
        // Set history duration
        $duration = isset($historyDuration) ? $historyDuration : self::$_defaultHistoryDuration;

        // Get significant views
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
        var_dump($results);
        $significantItems = [];
        foreach($results['hits']['hits'] as $key => $result) {
            $significantItems[] = $result['_source']['contentId'];
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

        // Get recommended views
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
