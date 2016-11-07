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
 * Aggregation constructor for Elasticsearch queries.
 *
 * @author dfanchon
 *
 * @category Rubedo
 */
class DataAggregations
{
    protected $taxoTermsCache = [];
    /**
     * Aggregation dispatcher.
     *
     * @param string $facetName
     * @param string $fieldName
     * @param string $orderField
     * @param string $orderDirection
     * @param int $size
     *
     * @return array
     */
    public function addAggregation($facetName, $fieldName = null, $orderField = '_count', $orderDirection = 'desc', $size = 100)
    {
        if (self::isFacetDisplayed($facetName)) {
            switch ($facetName) {
                case 'lastupdatetime':
                    return self::addDateRangeFacet($facetName, $fieldName);
                    break;
                case 'inStock':
                    $ranges = [
                        ['to' => 0],
                        ['from' => 1],
                    ];

                    return self::addRangeFacet($facetName, $fieldName, $ranges);
                    break;
                case 'price':
                    $ranges = [
                        ['from' => 0, 'to' => 5],
                        ['from' => 6, 'to' => 15],
                        ['from' => 16, 'to' => 25],
                        ['from' => 26, 'to' => 50],
                        ['from' => 51],
                    ];

                    return self::addRangeFacet($facetName, $fieldName, $ranges);
                    break;
                default:
                    return self::addTermsFacet($facetName, $fieldName, $orderField, $orderDirection, $size);
                    break;
            }
        }
    }

    /**
     * Terms aggregation.
     *
     * @param string $facetName
     * @param string $fieldName
     * @param string $orderField
     * @param string $orderDirection
     * @param int $size
     *
     * @return array
     */
    public function addTermsFacet($facetName, $fieldName = null, $orderField = '_count', $orderDirection = 'desc', $size = 100)
    {

        // Set default value for fieldName
        if (is_null($fieldName)) {
            $fieldName = $facetName;
        }

        // Exclude active Facets for this vocabulary
        $exclude = self::_excludeActiveFacets($facetName);

        // Apply filters from other facets
        $facetFilter = self::_getFacetFilter($facetName);

        // Build facet
        $agg = [
            'filter' => $facetFilter,
            'aggs' => [
                'aggregation' => [
                    'terms' => [
                        'field' => $fieldName,
                        'size' => $size,
                        'order' => [$orderField => $orderDirection],
                    ],
                ],
            ],
        ];

        if ($exclude != ['']) {
            $agg['aggs']['aggregation']['terms']['exclude'] = $exclude;
        }

        SearchContext::addAggs($facetName, $agg);

        return $agg;
    }

    /**
     * Date range aggregation (default for lastupdatetime ).
     *
     * @param string $facetName
     * @param string $fieldName
     * @param array $ranges
     *
     * @return array
     */
    protected function addDateRangeFacet($facetName, $fieldName = null, $ranges = null)
    {
        $d = SearchContext::getService('CurrentTime')->getCurrentTime();

        // Default ranges
        if (is_null($ranges)) {
            $lastday = (string) mktime(0, 0, 0, date('m', $d), date('d', $d) - 1, date('Y', $d)) * 1000;
            $lastweek = (string) mktime(0, 0, 0, date('m', $d), date('d', $d) - 7, date('Y', $d)) * 1000;
            $lastmonth = (string) mktime(0, 0, 0, date('m', $d) - 1, date('d', $d), date('Y', $d)) * 1000;
            $lastyear = (string) mktime(0, 0, 0, date('m', $d), date('d', $d), date('Y', $d) - 1) * 1000;

            $ranges = [
                ['from' => $lastday],
                ['from' => $lastweek],
                ['from' => $lastmonth],
                ['from' => $lastyear],
            ];
        }

        // Set default value for fieldName
        if (is_null($fieldName)) {
            $fieldName = $facetName;
        }

        // Apply filters from other facets
        $facetFilter = self::_getFacetFilter($facetName);

        // Build facet
        $agg = [
            'filter' => $facetFilter,
            'aggs' => [
                'aggregation' => [
                    'date_range' => [
                        'field' => $fieldName,
                        'ranges' => $ranges,
                    ],
                ],
            ],
        ];

        SearchContext::addAggs($facetName, $agg);

        return $agg;
    }

    /**
     * Range aggregation.
     *
     * @param string $facetName
     * @param string $fieldName
     * @param array $ranges
     *
     * @return array
     */
    protected function addRangeFacet($facetName, $fieldName = null, $ranges = null)
    {

        // Set default value for fieldName
        if (is_null($fieldName)) {
            $fieldName = $facetName;
        }

        // Apply filters from other facets
        $facetFilter = self::_getFacetFilter($facetName);

        // Build facet
        $agg = [
            'filter' => $facetFilter,
            'aggs' => [
                'aggregation' => [
                    'range' => [
                        'field' => $fieldName,
                        'ranges' => $ranges,
                    ],
                ],
            ],
        ];

        SearchContext::addAggs($facetName, $agg);

        return $agg;
    }

    /**
     * geohash_grid aggregation.
     *
     * @param int $geoPrecision

     * @return array
     */
    public function addGeoFacet($fieldName, $geoPrecision)
    {

        // Apply filters from other facets
        $facetFilter = self::_getFacetFilter('agf');

        // Build facet
        $agg = [
            'filter' => $facetFilter,
            'aggs' => [
                'hash' => [
                    'geohash_grid' => [
                        'field' => $fieldName,
                        'precision' => $geoPrecision,
                    ],
                ],
            ],
        ];

        SearchContext::addAggs('agf', $agg);

        return $agg;
    }

    /**
     * Is displayed Facet ?
     *
     * @param string $name
     *                     facet name
     *
     * @return bool
     */
    public function isFacetDisplayed($name)
    {
        $isFrontEnd = SearchContext::getIsFrontEnd();
        $displayedFacets = SearchContext::getDisplayedFacets();
        if (!$isFrontEnd or $displayedFacets == array(
                'all',
            ) or in_array($name, $displayedFacets) or in_array(array(
                'name' => $name,
                'operator' => 'AND',
            ), $displayedFacets) or in_array(array(
                'name' => $name,
                'operator' => 'OR',
            ), $displayedFacets)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add filter to exclude active facets from aggregations.
     *
     * @param string $facetName
     *
     * @return array
     */
    protected function _excludeActiveFacets($facetName)
    {
        $exclude = [''];
        $filters = SearchContext::getFilters();
        $facetDisplayMode = SearchContext::getFacetDisplayMode();
        if ($facetDisplayMode != 'checkbox' and isset($filters [$facetName])) {
            $exclude = $filters [$facetName];
        }

        return $exclude;
    }

    /**
     * Get filter from global filter list.
     *
     * @param string $name
     *
     * @return array
     */
    protected function _getFacetFilter($name)
    {
        // get mode for this facet
        $operatorList = SearchContext::getFacetOperators();
        $operator = isset($operatorList[$name]) ? $operatorList[$name] : 'and';
        $globalFilterList = SearchContext::getGlobalFilterList();
        if (!empty($globalFilterList)) {
            $facetFilter = array();
            $result = false;
            foreach ($globalFilterList as $key => $filter) {
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
     * Add labels to facet.
     *
     * @param string $id    Facet Id
     * @param array  $facet Facet data
     * @param int    $total
     *
     * @return array
     */
    public function formatFacet($id, $facet, $total)
    {
        $temp = (array) $facet['aggregation'];
        $renderFacet = true;
        if (!empty($temp)) {
            $temp ['id'] = $id;
            switch ($id) {

                case 'navigation' :

                    $temp ['label'] = SearchContext::getService('Translate')->translate('Search.Facets.Label.Navigation', 'Navigation');
                    if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
                        foreach ($temp ['buckets'] as $key => $value) {
                            $temp ['terms'] [$key] ['term'] = $value ['key'];
                            $termItem = SearchContext::getService('TaxonomyTerms')->getTerm($value ['key'], 'navigation');
                            $temp ['terms'] [$key] ['label'] = $termItem ['Navigation'];
                            $temp['terms'] [$key] ['count'] = $value['doc_count'];
                        }
                    } else {
                        $renderFacet = false;
                    }
                    break;

                case 'damType' :

                    $temp ['label'] = SearchContext::getService('Translate')->translate('Search.Facets.Label.MediaType', 'Media type');
                    if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
                        foreach ($temp ['buckets'] as $key => $value) {
                            $termItem = SearchContext::getService('DamTypes')->findById($value ['key']);
                            if ($termItem && isset($termItem ['type'])) {
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

                    $temp ['label'] = SearchContext::getService('Translate')->translate('Search.Facets.Label.DataType', 'Data type');
                    foreach ($temp ['buckets'] as $key => $value) {
                        $temp ['terms'] [$key] ['term'] = $value ['key'];
                        $temp ['terms'] [$key] ['label'] = SearchContext::getService('Translate')->translate('Search.Facets.Label.'.strtoupper($value ['key']), strtoupper($value ['key']));
                        $temp['terms'] [$key] ['count'] = $value['doc_count'];
                    }
                    break;

                case 'type' :

                    $temp ['label'] = SearchContext::getService('Translate')->translate('Search.Facets.Label.ContentType', 'Content type');
                    if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
                        foreach ($temp ['buckets'] as $key => $value) {
                            $temp ['terms'] [$key] ['term'] = $value ['key'];
                            $termItem = SearchContext::getService('ContentTypes')->findById($value ['key']);
                            $temp ['terms'] [$key] ['label'] = $termItem ['type'];
                            $temp['terms'] [$key] ['count'] = $value['doc_count'];
                        }
                    } else {
                        $renderFacet = false;
                    }
                    break;

                case 'userType' :

                    $temp ['label'] = SearchContext::getService('Translate')->translate('Search.Facets.Label.UserType', 'User type');
                    if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
                        foreach ($temp ['buckets'] as $key => $value) {
                            $temp ['terms'] [$key] ['term'] = $value ['key'];
                            $termItem = SearchContext::getService('UserTypes')->findById($value ['key']);
                            $temp ['terms'] [$key] ['label'] = $termItem ['type'];
                            $temp['terms'] [$key] ['count'] = $value['doc_count'];
                        }
                    } else {
                        $renderFacet = false;
                    }
                    break;

                case 'author' :
                    $temp ['label'] = SearchContext::getService('Translate')->translate('Search.Facets.Label.Author', 'Author');
                    $facetDisplayMode = SearchContext::getFacetDisplayMode();
                    if ($facetDisplayMode == 'checkbox' or (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0)) {
                        $collection = SearchContext::getService('Users');
                        foreach ($temp ['buckets'] as $key => $value) {
                            if ($value ['key'] != 'rubedo') {
                                $termItem = $collection->findById($value ['key']);
                                $temp ['terms'][] = [
                                    'term' => $value ['key'],
                                    'label' => $termItem ['name'],
                                    'count' => $value['doc_count'],
                                 ];
                            }
                        }
                    } else {
                        $renderFacet = false;
                    }
                    break;

                case 'userName' :

                    $temp ['label'] = SearchContext::getService('Translate')->translate('Search.Facets.Label.UserName', 'User Name');
                    foreach ($temp ['buckets'] as $key => $value) {
                        $temp ['terms'] [$key] ['term'] = $value ['key'];
                        $temp ['terms'] [$key] ['label'] = strtoupper($value ['key']);
                        $temp['terms'] [$key] ['count'] = $value['doc_count'];
                    }

                    break;

                case 'lastupdatetime' :

                    $temp ['label'] = SearchContext::getService('Translate')->translate('Search.Facets.Label.ModificationDate', 'Modification date');
                    $timeLabel = SearchContext::getTimeLabel();
                    if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
                        $temp ['_type'] = 'range';
                        $temp ['ranges'] = array_values($temp ['buckets']);

                        foreach ($temp ['buckets'] as $key => $value) {
                            $rangeCount = $value ['doc_count'];
                            // unset facet when count = 0 or total results
                            // count when display mode is not set to
                            // checkbox
                            $facetDisplayMode = SearchContext::getFacetDisplayMode();
                            if ($facetDisplayMode == 'checkbox' or ($rangeCount > 0 and $rangeCount <= $total)) {
                                $temp ['ranges'] [$key] ['label'] = isset($timeLabel [$value ['from']]) ? $timeLabel [$value ['from']] : 'Time label';
                                $temp ['ranges'] [$key] ['count'] = $rangeCount;
                                unset($temp ['ranges'] [$key] ['doc_count']);
                            } else {
                                unset($temp ['ranges'] [$key]);
                            }
                        }
                        $temp ['ranges'] = array_values($temp ['ranges']);
                    } else {
                        $renderFacet = false;
                    }
                    break;

                case 'price' :

                    $temp ['label'] = SearchContext::getService('Translate')->translate('Search.Facets.Label.Price', 'Price');
                    if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
                        $temp ['_type'] = 'range';
                        $temp ['ranges'] = array_values($temp ['buckets']);

                        foreach ($temp ['buckets'] as $key => $value) {
                            $rangeCount = $value ['doc_count'];
                            // unset facet when count = 0 or total results
                            // count when display mode is not set to
                            // checkbox
                            $facetDisplayMode = SearchContext::getFacetDisplayMode();
                            if ($facetDisplayMode == 'checkbox' or ($rangeCount > 0 and $rangeCount <= $total)) {
                                $from = isset($value['from']) ? $value['from'] : null;
                                $to = isset($value['to']) ? $value['to'] : null;
                                $temp ['ranges'] [$key] ['label'] = SearchContext::getRangeLabel($from, $to);
                                $temp ['ranges'] [$key] ['count'] = $rangeCount;
                                unset($temp ['ranges'] [$key] ['doc_count']);
                            } else {
                                unset($temp ['ranges'] [$key]);
                            }
                        }
                        $temp ['ranges'] = array_values($temp ['ranges']);
                    } else {
                        $renderFacet = false;
                    }
                    break;

                case 'inStock' :

                    $temp ['label'] = SearchContext::getService('Translate')->translate('Search.Facets.Label.InStock', 'In stock');
                    if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
                        $temp ['_type'] = 'range';
                        $temp ['ranges'] = array_values($temp ['buckets']);

                        foreach ($temp ['buckets'] as $key => $value) {
                            $rangeCount = $value ['doc_count'];
                            // unset facet when count = 0 or total results
                            // count when display mode is not set to
                            // checkbox
                            if ($rangeCount > 0) {
                                $from = isset($value['from']) ? $value['from'] : null;
                                $to = isset($value['to']) ? $value['to'] : null;
                                $temp ['ranges'] [$key] ['label'] = 'Yes';
                                $temp ['ranges'] [$key] ['count'] = $rangeCount;
                                unset($temp ['ranges'] [$key] ['doc_count']);
                            } else {
                                unset($temp ['ranges'] [$key]);
                            }
                        }
                        $temp ['ranges'] = array_values($temp ['ranges']);
                    } else {
                        $renderFacet = false;
                    }
                    break;
                default :
                    $regex = '/^[0-9a-z]{24}$/';
                    if (preg_match($regex, $id)) { // Taxonomy facet use
                        // mongoID
                        $vocabularyItem = SearchContext::getService('Taxonomy')->findById($id);
                        $temp ['label'] = $vocabularyItem ['name'];
                        if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
                            foreach ($temp ['buckets'] as $key => $value) {
                                $termItem = $this->getTaxoTerm($id,$value ['key']);
                                if ($termItem) {
                                    $temp ['terms'] [] = [
                                            'label' => $termItem ['text'],
                                            'term' => $value ['key'],
                                            'count' => $value['doc_count'],
                                        ];
                                }
                            }
                            $temp ['terms'] = array_values($temp ['terms']);
                        } else {
                            $renderFacet = false;
                        }
                    } else {
                        // faceted field
                        $facetedField = SearchContext::searchLabel($id);
                        if ($facetedField) {
                            $temp ['label'] = $facetedField ['label'];

                            if (array_key_exists('buckets', $temp) and count($temp ['buckets']) > 0) {
                                foreach ($temp ['buckets'] as $key => $value) {
                                    $temp ['terms'] [$key] ['term'] = $value ['key'];
                                    $temp['terms'] [$key] ['count'] = $value['doc_count'];
                                    switch ($facetedField ['cType']) {
                                        case 'datefield':
                                        case 'Ext.form.field.Date':
                                            $label = $value ['key_as_string'];
                                            $temp ['_type'] = 'date';
                                            break;
                                        case 'DCEField':
                                            $linkedContent = SearchContext::getService('Contents')->findById($value ['key'], true, false);
                                            $label = $linkedContent['text'];
                                            break;
                                        default:
                                            $label = $value ['key'];
                                            break;
                                    }
                                    $temp ['terms'] [$key] ['label'] = $label;
                                }
                                $temp ['terms'] = array_values($temp ['terms']);
                            }
                        } else {
                            // Default facet
                            $temp ['label'] = SearchContext::getService('Translate')->translate('Search.Facets.Label.'.$id, $id);
                            foreach ($temp ['buckets'] as $key => $value) {
                                $temp ['terms'] [$key] ['term'] = $value ['key'];
                                $temp ['terms'] [$key] ['label'] = $value ['key'];
                                $temp['terms'] [$key] ['count'] = $value['doc_count'];
                            }
                            break;
                        }
                    }
                    break;
            }
            if ($renderFacet) {
                unset($temp['buckets']);
                unset($temp['doc_count_error_upper_bound']);
                unset($temp['sum_other_doc_count']);

                return $temp;
            } else {
                return;
            }
        }
    }

    protected function getTaxoTerm($taxoId,$termId){
        if(!isset($this->taxoTermsCache[$taxoId])){
            $terms=SearchContext::getService('TaxonomyTerms')->findByVocabulary($taxoId);
            if(!isset($terms["data"])&&!is_array($terms["data"])){
                return null;
            }
            $this->taxoTermsCache[$taxoId]=[];
            foreach($terms["data"] as $term){
                $this->taxoTermsCache[$taxoId][$term["id"]]=$term;
            }
        }
        return isset($this->taxoTermsCache[$taxoId][$termId]) ? $this->taxoTermsCache[$taxoId][$termId] : null;
    }
}
