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
use Rubedo\Services\Manager;

/**
 * Query filters constructor for Elasticsearch queries
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataFilters
{

    public $_globalFilterList = [];
    public $_filters;
    public $_setFilter = false;
    public $_params;
    public $_facetOperators;
    public $geoAgreggation;

    /**
     * Add term filter to Query
     *
     * @param string $name
     *            filter name
     *            string $field
     *            field to apply filter
     * @return array
     */
    public function _addTermFilter($name, $field)
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

    public function setLocaleFilter(array $values)
    {
        $filter = [
        	'or' => [
        		['missing' => ['field' => 'availableLanguages']],
	    		['terms' => ['availableLanguages' => $values]]
        	]
        ];
        //$this->_globalFilterList ['availableLanguages'] = $filter;
        $this->_setFilter = true;
    }

    /**
     * Build facet filter from name
     *
     * @param string $name
     *            filter name
     * @return array or null
     */
    public function _getFacetFilter($name)
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

    public function _addWorkspaceFilter() {
        $workspacesFilter = array();
        foreach ($readWorkspaceArray as $wsTerm) {
            $workspacesFilter['or'][] = [
                'term' => ['target' => $wsTerm]
            ];
        }

        $this->_globalFilterList ['target'] = $workspacesFilter;
        $this->_setFilter = true;
    }

    public function _addProductFilter() {
        $isProductFilter = [
            'term' => ['isProduct' => true]
        ];
        $this->_globalFilterList ['isProduct']=$isProductFilter;
    }

    public function _addFrontEndFilter() {
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

    public function _addGeoFilter() {
        $contentTypeList = Manager::getService('ContentTypes')->getGeolocatedContentTypes();
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

        $this->geoAgreggation = [
            'aggs' => [
                'hash' => [
                    'geohash_grid' => [
                        'field' => 'fields.position.location.coordinates'
                    ]
                ]
            ]
        ];
    }

    public function _addLastUpdateFilter() {
        $rangeFrom = substr($this->_params ['lastupdatetime'],0,24);
        $rangeTo = substr($this->_params ['lastupdatetime'],25, strlen($this->_params ['lastupdatetime'])-25);
        $range = [];
        if ($rangeFrom && $rangeFrom != '*') $range['gte'] = $rangeFrom;
        if ($rangeTo && $rangeTo != '*') $range['lte'] = $rangeTo;
        $filter = [
            'range' => [
                'lastUpdateTime' => $range
            ]
        ];
        $this->_globalFilterList ['lastupdatetime'] = $filter;
        $this->_filters ['lastupdatetime'] = $this->_params ['lastupdatetime'];
        $this->_setFilter = true;
    }

    public function _addPriceFilter() {
        $splitPriceRange = explode('-',$this->_params ['price']);

        $rangeFrom = ($splitPriceRange[0]!='*') ? $splitPriceRange[0] : false;
        $rangeTo = ($splitPriceRange[1]!='*') ? $splitPriceRange[1] : false;
        $range = [];
        if ($rangeFrom) $range['gte'] = (int) $rangeFrom;
        if ($rangeTo) $range['lte'] = (int) $rangeTo;
        $filter = [
            'range' => [
                'productProperties.variations.price' => $range
            ]
        ];
        $this->_globalFilterList ['price'] = $filter;
        $this->_filters ['price'] = $this->_params ['price'];
        $this->_setFilter = true;
    }

    public function _addInStockFilter() {
        $range = [];
        $range['gte'] = 1;
        $filter = [
            'range' => [
                'productProperties.variations.stock' => $range
            ]
        ];
        $this->_globalFilterList ['inStock'] = $filter;
        $this->_filters ['inStock'] = $this->_params ['inStock'];
        $this->_setFilter = true;
    }

    public function _addGeoBoxFilter() {
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
}
