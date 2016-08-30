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
 * Filter constructor for Elasticsearch queries.
 *
 * @author dfanchon
 *
 * @category Rubedo
 */
class DataFilters
{
    /**
     * Filter dispatcher.
     *
     * @param string $facetName
     * @param string $fieldName
     * @param string $orderField
     * @param string $orderDirection
     * @param int $size
     *
     * @return array
     */
    public function addFilter($name, $value = null, $field = null)
    {
        switch ($name) {
            case 'query':
                return self::addQueryFilter($value);
                break;
            case 'frontEndFilters':
                return self::addCustomFrontEndFilter($value);
                break;
            case 'workspaceFilter':
                return self::addWorkspaceFilter($value);
                break;
            case 'frontend':
                return self::addFrontEndFilter();
                break;
            case 'geo':
                return self::addGeoFilter();
                break;
            case 'lastupdatetime':
                return self::addLastUpdateFilter($value);
                break;
            case 'inStock':
                return self::addInStockFilter($value);
                break;
            case 'price':
                return self::addPriceFilter($value);
                break;
            case 'isProduct':
                return self::addProductFilter();
                break;
            case 'geoBoxFilter':
                return self::addGeoBoxFilter($value);
                break;
            case 'locale':
                return self::addLocaleFilter($value);
                break;
            case 'excludeItemsFilter':
                return self::addSeenItemsFilter($value);
                break;
            default:
                return self::addTermsFilter($name, $field, $value);
                break;
        }
    }

    /**
     * Query filter.
     *
     * @param string $value query
     *
     * @return array
     */
    public function addQueryFilter($value)
    {
        if ($value != '') {
            SearchContext::addFilters('query', $value);
        }
    }

    /**
     * Custom front end filter.
     *
     * @param array $value
     *
     * @return array
     */
    public function addCustomFrontEndFilter($value)
    {
        if ($value != '') {
            SearchContext::addGlobalFilterList('frontEndFilters', $value);
        }
    }

    /**
     * Terms filter.
     *
     * @param string $field
     * @param array  $values
     * @param string $operator
     *
     * @return array
     */
    public function addTermsFilter($name, $field, $values)
    {
        if (!is_array($values)) {
            $values = [$values];
        }
        $operator = $this->getOperator($name);
        $filterEmpty = true;
        $filter = [];
        switch ($operator) {
            case 'or' :
                $termFilter = [
                    'terms' => [
                        $field => $values,
                    ],
                ];
                $filter['or'][] = $termFilter;
                $filterEmpty = false;
                break;
            case 'and' :
            default :
                foreach ($values as $type) {
                    $termFilter = [
                        'term' => [
                            $field => $type,
                        ],
                    ];
                    $filter['and'][] = $termFilter;
                    $filterEmpty = false;
                }
                break;
        }
        if (!$filterEmpty) {
            SearchContext::addGlobalFilterList($name, $filter);
            SearchContext::addFilters($name, $values);

            return $filter;
        } else {
            return;
        }
    }

    /**
     * Locale filter.
     *
     * @param string $field
     * @param array  $values
     * @param string $operator
     *
     * @return array
     */
    public function addLocaleFilter(array $values)
    {
        $filter = [
            'or' => [
                ['missing' => ['field' => 'availableLanguages']],
                ['terms' => ['availableLanguages' => $values]],
            ],
        ];
        SearchContext::addGlobalFilterList('availableLanguages', $filter);

        return $filter;
    }

    /**
     * Date range filter.
     *
     * @param string $filterName
     * @param string $fieldName
     * @param string $startDate
     * @param string $endDate
     *
     * @return array
     */
    public function addDateRangeFilter($filterName, $fieldName, $startDate, $endDate)
    {
        $filter = [
            'range' => [
                $fieldName => [
                    'gte' => $startDate,
                    'lte' => $endDate
                ]
            ],
        ];
        SearchContext::addGlobalFilterList($filterName, $filter);

        return $filter;
    }

    /**
     * Build facet filter from name.
     *
     * @param string $name
     *
     * @return array or null
     */
    public function _getFacetFilter($name)
    {
        // get mode for this facet
        $operator = isset($this->_facetOperators [$name]) ? $this->_facetOperators [$name] : 'and';
        if (!empty($this->_globalFilterList)) {
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
     * Workspace filter.
     *
     * @param array $readWorkspaceArray
     *
     * @return array
     */
    public function addWorkspaceFilter($readWorkspaceArray)
    {
        $filter = array();
        foreach ($readWorkspaceArray as $wsTerm) {
            $filter['or'][] = [
                'term' => ['target' => $wsTerm],
            ];
        }
        SearchContext::addGlobalFilterList('target', $filter);

        return $filter;
    }

    /**
     * Is product filter.
     *
     * @return array
     */
    public function addProductFilter()
    {
        $filter = [
            'term' => ['isProduct' => true],
        ];
        SearchContext::addGlobalFilterList('isProduct', $filter);

        return $filter;
    }

    /**
     * Front end filter.
     *
     * @return array
     */
    public function addFrontEndFilter()
    {
        // Only 'online' contents
        $onlineFilter = [
            'or' => [
                ['term' => [
                    'online' => true, ],
                ],
                ['missing' => [
                    'field' => 'online',
                    'existence' => true,
                    'null_value' => true,
                    ],
                ],
            ],
        ];

        //  Filter on start and end publication date
        $now = SearchContext::getService('CurrentTime')->getCurrentTime();

        // filter on start
        $beginFilter = [
            'or' => [
                ['missing' => [
                    'field' => 'startPublicationDate',
                    'existence' => true,
                    'null_value' => true,
                ]],
                ['term' => [
                    'startPublicationDate' => 0,
                ]],
                ['range' => [
                    'startPublicationDate' => [
                        'lte' => $now,
                    ],
                ]],
            ],
        ];

        // filter on end : not set or not ended
        $endFilter = [
            'or' => [
                ['missing' => [
                    'field' => 'endPublicationDate',
                    'existence' => true,
                    'null_value' => true,
                ]],
                ['term' => [
                    'endPublicationDate' => 0,
                ]],
                ['range' => [
                    'endPublicationDate' => [
                        'gte' => $now,
                    ],
                ]],
            ],
        ];

        // build complete filter
        $frontEndFilter = [
            'and' => [
                $onlineFilter,
                $beginFilter,
                $endFilter,
            ],
        ];
        SearchContext::addGlobalFilterList('frontend', $frontEndFilter);

        return $frontEndFilter;
    }

    /**
     * geohash_grid filter.
     *
     * @return array
     */
    public function addGeoFilter()
    {
        $contentTypeList = SearchContext::getService('ContentTypes')->getGeolocatedContentTypes();
        $geoFilter = [];
        if (!empty($contentTypeList)) {
            foreach ($contentTypeList as $contentTypeId) {
                $geoFilter['or'][] = [
                    'term' => ['contentType' => $contentTypeId],
                ];
            }
        }
        $geoAgreggation = [
            'aggs' => [
                'hash' => [
                    'geohash_grid' => [
                        'field' => 'fields.position.location.coordinates',
                    ],
                ],
            ],
        ];
        if (!empty($geoFilter)) {
            SearchContext::addGlobalFilterList('geoTypes', $geoFilter);
        }

        return [$geoFilter, $geoAgreggation];
    }

    /**
     * Last update time filter.
     *
     * @param string or array $lastUpdateTime
     *
     * @return array
     */
    public function addLastUpdateFilter($lastUpdateTime)
    {
        if (is_array($lastUpdateTime)) {
            $lastUpdateTime = $lastUpdateTime[0];
        }
        $rangeFrom = substr($lastUpdateTime, 0, 24);
        $rangeTo = substr($lastUpdateTime, 25, strlen($lastUpdateTime) - 25);
        $range = [];
        if ($rangeFrom && $rangeFrom != '*') {
            $range['gte'] = $rangeFrom;
        }
        if ($rangeTo && $rangeTo != '*') {
            $range['lte'] = $rangeTo;
        }
        $filter = [
            'range' => [
                'lastUpdateTime' => $range,
            ],
        ];
        SearchContext::addGlobalFilterList('lastupdatetime', $filter);
        SearchContext::addFilters('lastupdatetime', $lastUpdateTime);

        return $filter;
    }

    /**
     * Price filter.
     *
     * @param string $price: price range
     *
     * @return array
     */
    public function addPriceFilter($price)
    {
        $splitPriceRange = explode('-', $price);
        $rangeFrom = ($splitPriceRange[0] != '*') ? $splitPriceRange[0] : false;
        $rangeTo = ($splitPriceRange[1] != '*') ? $splitPriceRange[1] : false;
        $range = [];
        if ($rangeFrom) {
            $range['gte'] = (int) $rangeFrom;
        }
        if ($rangeTo) {
            $range['lte'] = (int) $rangeTo;
        }
        $filter = [
            'range' => [
                'productProperties.variations.price' => $range,
            ],
        ];
        SearchContext::addGlobalFilterList('price', $filter);
        SearchContext::addFilters('price', $price);

        return $filter;
    }

    /**
     * Stock range filter.
     *
     * @param string $value: stock range
     *
     * @return array
     */
    public function addInStockFilter($value)
    {
        $range = [];
        $range['gte'] = 1;
        $filter = [
            'range' => [
                'productProperties.variations.stock' => $range,
            ],
        ];
        SearchContext::addGlobalFilterList('inStock', $filter);
        SearchContext::addFilters('inStock', $value);

        return $filter;
    }

    /**
     * geo_bounding_box filter.
     *
     * @param array $coordinates
     *
     * @return array
     */
    public function addGeoBoxFilter($coordinates)
    {
        $geoBoundingBoxFilter = [
            'geo_bounding_box' => [
                'fields.position.location.coordinates' => [
                    'top_left' => [
                        $coordinates['inflon'] + 0,
                        $coordinates['suplat'] + 0,
                    ],
                    'bottom_right' => [
                        $coordinates['suplon'] + 0,
                        $coordinates['inflat'] + 0,
                    ],
                ],
            ],
        ];

        // set precision for geohash aggregation
        $bucketWidth = round($this->get_distance_m($coordinates['inflat'], $coordinates['inflon'], $coordinates['inflat'], $coordinates['suplon']) / 8);
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
        SearchContext::addGlobalFilterList('geo', $geoBoundingBoxFilter);

        return [$geoBoundingBoxFilter, $geoPrecision];
    }

    /**
     * Exclude ids filter.
     *
     * @param array $items
     *
     * @return array
     */
    public function excludeItemsFilter($items)
    {
        $filter = [];
        $filter['bool'] = [
            'must_not' => ['ids' => ['values' => $items]],
        ];
        SearchContext::addGlobalFilterList('excludeItems', $filter);

        return $filter;
    }

    /**
     * Format filter.
     *
     * @param string $id:     Filter id
     * @param string $termId: Term id
     *
     * @return array
     */
    public function formatFilter($id, $termId)
    {
        switch ($id) {
            case 'objectType':
                $temp = [
                    'id' => $id,
                    'label' => SearchContext::getService('Translate')->translate('Search.Facets.Label.DataType', 'Data type'),
                ];
                foreach ($termId as $term) {
                    $temp ['terms'] [] = [
                        'term' => $term,
                        'label' => SearchContext::getService('Translate')->translate('Search.Facets.Label.'.strtoupper($term), strtoupper($term)),
                    ];
                }
                break;
            case 'damType' :
                $temp = [
                    'id' => $id,
                    'label' => SearchContext::getService('Translate')->translate('Search.Facets.Label.MediaType', 'Media type'),
                ];
                foreach ($termId as $term) {
                    $termItem = SearchContext::getService('DamTypes')->findById($term);
                    $temp ['terms'] [] = [
                        'term' => $term,
                        'label' => $termItem ['type'],
                    ];
                }
                break;
            case 'type' :
                $temp = [
                    'id' => $id,
                    'label' => SearchContext::getService('Translate')->translate('Search.Facets.Label.ContentType', 'Content type'),
                ];
                foreach ($termId as $term) {
                    $termItem = SearchContext::getService('ContentTypes')->findById($term);
                    $temp ['terms'] [] = [
                        'term' => $term,
                        'label' => $termItem ['type'],
                    ];
                }
                break;
            case 'userType' :
                $temp = [
                    'id' => $id,
                    'label' => SearchContext::getService('Translate')->translate('Search.Facets.Label.UserType', 'User type'),
                ];
                foreach ($termId as $term) {
                    $termItem = SearchContext::getService('UserTypes')->findById($term);
                    $temp ['terms'] [] = [
                        'term' => $term,
                        'label' => $termItem ['type'],
                    ];
                }
                break;
            case 'author' :
                $temp = [
                    'id' => $id,
                    'label' => SearchContext::getService('Translate')->translate('Search.Facets.Label.Author', 'Author'),
                ];
                foreach ($termId as $term) {
                    $termItem = SearchContext::getService('Users')->findById($term);
                    $temp ['terms'] [] = [
                        'term' => $term,
                        'label' => $termItem ['name'],
                    ];
                }
                break;
            case 'userName' :
                $temp = [
                    'id' => $id,
                    'label' => SearchContext::getService('Translate')->translate('Search.Facets.Label.UserName', 'User Name'),
                ];
                foreach ($termId as $term) {
                    $temp ['terms'] [] = [
                        'term' => $term,
                        'label' => strtoupper($term),
                    ];
                }
                break;
            case 'lastupdatetime' :
                $timeLabel = SearchContext::getTimeLabel();
                $temp = [
                    'id' => 'lastupdatetime',
                    'label' => 'Date',
                    'terms' => [
                        [
                            'term' => $termId,
                            'label' => $timeLabel [strtotime(substr($termId, 0, 24)) * 1000],
                        ],
                    ],
                ];
                break;
            case 'query' :
                $temp = [
                    'id' => $id,
                    'label' => 'Query',
                    'terms' => [
                        [
                            'term' => $termId,
                            'label' => $termId,
                        ],
                    ],
                ];
                break;
            case 'target' :
                $temp = [
                    'id' => $id,
                    'label' => 'Target',
                    'terms' => [
                        [
                            'term' => $termId,
                            'label' => $termId,
                        ],
                    ],
                ];
                break;
            case 'workspace' :
                $temp = [
                    'id' => $id,
                    'label' => 'Workspace',
                    'terms' => [
                        [
                            'term' => $termId,
                            'label' => $termId,
                        ],
                    ],
                ];
                break;
            case 'price' :
                $priceRange = explode('-', $termId);
                $priceLabel = SearchContext::getRangeLabel($priceRange[0], $priceRange[1]);
                $temp = [
                    'id' => 'price',
                    'label' => 'InStock',
                    'terms' => [
                        [
                            'term' => $termId,
                            'label' => $priceLabel,
                        ],
                    ],
                ];
                break;
            case 'inStock' :
                $temp = [
                    'id' => 'inStock',
                    'label' => 'In stock',
                    'terms' => [
                        [
                            'term' => $termId,
                            'label' => 'Yes',
                        ],
                    ],
                ];
                break;
            case 'navigation' :
            default :
                $regex = '/^[0-9a-z]{24}$/';
                if (preg_match($regex, $id) || $id == 'navigation') { // Taxonomy facet use
                    // mongoID
                    $vocabularyItem = SearchContext::getService('Taxonomy')->findById($id);
                    $temp = [
                        'id' => $id,
                        'label' => $vocabularyItem ['name'],
                    ];

                    foreach ($termId as $term) {
                        $termItem = SearchContext::getService('TaxonomyTerms')->findById($term);
                        $temp ['terms'] [] = [
                            'term' => $term,
                            'label' => $termItem ['text'],
                        ];
                    }
                } else {
                    // faceted field

                    $temp = [
                        'id' => $id,
                        'label' => $id,
                    ];

                    $facetedField = SearchContext::searchLabel($id);

                    if (!is_array($termId)) {
                        $termId = [$termId];
                    }

                    foreach ($termId as $term) {
                        $newTerm = [
                          'term' => $term
                        ];
                        switch ($facetedField ['cType']) {
                            case 'datefield':
                            case 'Ext.form.field.Date':
                                $label = $term;
                                $newTerm['_type'] = 'date';
                                break;
                            case 'DCEField':
                                $linkedContent = SearchContext::getService('Contents')->findById($term, true, false);
                                $label = $linkedContent['text'];
                                break;
                            default:
                                $label = $term;
                                break;
                        }
                        $newTerm['label'] = $label;
                        $temp ['terms'] [] = $newTerm;
                    }
                }

                break;
        }

        return $temp;
    }

    /**
     * Calculate distance between 2 geo points.
     *
     * @param float $lat1: latitude point1
     * @param float $lng1: longitude point 1
     * @param float $lat2: latitude point 2
     * @param float $lng2: longitude point 2
     *
     * @return array
     */
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
     * Get facet operator.
     *
     * @param string $name: Facet name
     *
     * @return string
     */
    protected function getOperator($name)
    {
        $operators = SearchContext::getFacetOperators();

        return isset($operators [$name]) ? strtolower($operators [$name]) : 'and';
    }
}
