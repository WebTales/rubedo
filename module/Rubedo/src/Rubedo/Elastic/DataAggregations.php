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
use Rubedo\Elastic\DataAggregations;

/**
 * Class implementing the Rubedo API to Elastic Search using elastic API
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataAggregations
{

    /**
     * Is the context a front office rendering ?
     *
     * @var boolean
     */
    protected static $_isFrontEnd;
    protected $_globalFilterList = array();
    //protected $_filters;
    //protected $_setFilter;
    protected $_params;
    protected $_facetOperators;
    protected $_displayedFacets = array();
    protected $_facetDisplayMode;
    protected $contentTypesArray = array();
    private $table = '0123456789bcdefghjkmnpqrstuvwxyz';

    protected $filters;


    /**
     * Add facet to Query
     *
     * @param 	string $facetName
     *        	string $fieldName
     *        	string $orderField
     *        	string $orderDirection
     * @return 	array
     */

    protected function _addTermsFacet($facetName, $fieldName = null, $orderField = '_count', $orderDirection = 'desc', $size = 100) {

    	// Set default value for fieldName
    	If (is_null($fieldName)) $fieldName = $facetName;

    	// Exclude active Facets for this vocabulary
    	$exclude = $this->_excludeActiveFacets($facetName);

    	// Apply filters from other facets
    	$facetFilter = $_getFacetFilter($facetName);

    	// Build facet
    	$result = [
    		'filter' => $facetFilter,
    		'aggs' => [
    			'aggregation' => [
    				'terms' => [
    					'field' => 	$fieldName,
    					'size' => $size,
    					'order' => [$orderField => $orderDirection]
    				]
    			]
    		]
    	];

    	if ($exclude!=['']) $result['aggs']['aggregation']['terms']['exclude'] = $exclude;

    	return $result;
    }

    protected function _addDateRangeFacet($facetName, $fieldName = null, $ranges) {

    	// Set default value for fieldName
    	If (is_null($fieldName)) $fieldName = $facetName;

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

    protected function _addDateHistogramFacet($facetName, $fieldName = null, $interval = 'day') {

    	// Set default value for fieldName
    	If (is_null($fieldName)) $fieldName = $facetName;

    	// Apply filters from other facets
    	$facetFilter = self::_getFacetFilter($facetName);

    	// Build facet
    	$result = [
    		'filter' => $facetFilter,
    		'aggs' => [
    			'aggregation' => [
    				'date_histogram' => [
    					'field' => 	$fieldName,
    					'interval' => $interval
				    ]
    			]
    		]
    	];
    	return $result;
    }

    protected function _addRangeFacet($facetName, $fieldName = null, $ranges) {

    	// Set default value for fieldName
    	If (is_null($fieldName)) $fieldName = $facetName;

    	// Apply filters from other facets
    	$facetFilter = self::_getFacetFilter($facetName);

    	// Build facet
    	$result = [
    		'filter' => $facetFilter,
    		'aggs' => [
    			'aggregation' => [
	    			'range' => [
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
    	if ($this->_facetDisplayMode != 'checkbox' and isset ($filters->_filters [$facetName])) {
    		$exclude = $filters->_filters [$facetName];
    	}
    	return $exclude;
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

    protected function getRangeLabel($from, $to, $currency = "€") {

    	$from = (int) $from;
    	$to = (int) $to;
    	if (isset($from) && $from!='*') {
    		if (isset($to) && $to!='*') {
    			$label = $this->_getService('Translate')->translate('Search.Facets.Label.RangeFrom', 'De') .' '.$from. ' '. $currency. ' '. $this->_getService('Translate')->translate('Search.Facets.Label.RangeTo', 'à').' '.$to. ' '.$currency;
    		} else {
    			$label = $this->_getService('Translate')->translate('Search.Facets.Label.RangeGreaterThan', 'Plus de') .' '.$from.' '.$currency;
    		}
    	} else {
    		if (isset($to) && $to!='*') {
    			$label = $this->_getService('Translate')->translate('Search.Facets.Label.RangeLessThan', 'Moins que') .' '.$to.' '.$currency;
    		}
    	}
    	return $label;
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
