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

use Rubedo\Services\Manager;

/**
 * Shared search context
 *
 * @author dfanchon
 *
 * @category Rubedo
 */
class SearchContext
{
    public static $_facetOperators = [];
    public static $_globalFilterList = [];
    public static $_filters = [];
    public static $_geoAgreggation = [];
    public static $_facetDisplayMode = null;
    public static $_displayedFacets = [];
    public static $_isFrontEnd = false;
    public static $_aggs = [];
    public static $_facetedFields = [];
    public static $_services = [];
    public static $_seenItems = null;

    public static function resetContext()
    {
        self::$_facetOperators = [];
        self::$_globalFilterList = [];
        self::$_filters = [];
        self::$_geoAgreggation = [];
        self::$_facetDisplayMode = null;
        self::$_displayedFacets = [];
        self::$_isFrontEnd = false;
        self::$_aggs = [];
        self::$_facetedFields = [];
    }

    public static function getFacetOperators()
    {
        return self::$_facetOperators;
    }

    public static function setFacetOperators($value)
    {
        return self::$_facetOperators = $value;
    }

    public static function setFacetOperator($key, $value)
    {
        return self::$_facetOperators[$key] = $value;
    }

    public static function getGlobalFilterList()
    {
        return self::$_globalFilterList;
    }

    public static function addGlobalFilterList($key, $value)
    {
        return self::$_globalFilterList[$key] = $value;
    }

    public static function getFilters()
    {
        return self::$_filters;
    }

    public static function addFilters($key, $value)
    {
        if (!isset(self::$_filters[$key])) {
            self::$_filters[$key] = $value;
        } else {
            self::$_filters[$key] = array_merge(self::$_filters[$key],$value);
        }
        return self::$_filters[$key];
    }

    public static function getFacetDisplayMode()
    {
        return self::$_facetDisplayMode;
    }

    public static function setFacetDisplayMode($value)
    {
        return self::$_facetDisplayMode = $value;
    }

    public static function getAggs()
    {
        return self::$_aggs;
    }

    public static function addAggs($key, $value)
    {
        return self::$_aggs[$key] = $value;
    }

    public static function getDisplayedFacets()
    {
        return self::$_displayedFacets;
    }

    public static function setDisplayedFacets($value)
    {
        return self::$_displayedFacets = $value;
    }

    public static function getIsFrontEnd()
    {
        return self::$_isFrontEnd;
    }

    public static function setIsFrontEnd($value)
    {
        return self::$_isFrontEnd = $value;
    }

    public static function getFacetedFields()
    {
        return self::$_facetedFields;
    }

    public static function setFacetedFields($value)
    {
        return self::$_facetedFields = $value;
    }

    public static function getSeenItems($fingerprint, $historyDepth = null, $historySize = null)
    {
        if (is_null(self::$_seenItems)) {
            $viewStream = Manager::getService('ElasticViewStream');
            self::$_seenItems = $viewStream->getSignificantItems($fingerprint,$historyDepth,$historySize);
        }

        return self::$_seenItems;
    }

    public static function searchLabel($id)
    {
        foreach(self::$_facetedFields as $field) {
                if ($field['name']==$id) {
                    return $field;
                }
        }
        return false;
    }

    public static function getRangeLabel($from, $to, $currency = '€')
    {
        $from = (int) $from;
        $to = (int) $to;
        if (isset($from) && $from != '*') {
            if (isset($to) && $to != '*') {
                $label = Manager::getService('Translate')->translate('Search.Facets.Label.RangeFrom', 'De').' '.$from.' '.$currency.' '.Manager::getService('Translate')->translate('Search.Facets.Label.RangeTo', 'à').' '.$to.' '.$currency;
            } else {
                $label = Manager::getService('Translate')->translate('Search.Facets.Label.RangeGreaterThan', 'Plus de').' '.$from.' '.$currency;
            }
        } else {
            if (isset($to) && $to != '*') {
                $label = Manager::getService('Translate')->translate('Search.Facets.Label.RangeLessThan', 'Moins que').' '.$to.' '.$currency;
            }
        }

        return $label;
    }

    public static function getService($serviceName)
    {
        if (!isset(self::$_services[$serviceName])) {
            self::$_services[$serviceName] = Manager::getService($serviceName);
        }

        return self::$_services[$serviceName];
    }

    public static function getTimeLabel()
    {
        $d = Manager::getService('CurrentTime')->getCurrentTime();
        $lastday = (string) mktime(0, 0, 0, date('m', $d), date('d', $d) - 1, date('Y', $d)) * 1000;
        $lastweek = (string) mktime(0, 0, 0, date('m', $d), date('d', $d) - 7, date('Y', $d)) * 1000;
        $lastmonth = (string) mktime(0, 0, 0, date('m', $d) - 1, date('d', $d), date('Y', $d)) * 1000;
        $lastyear = (string) mktime(0, 0, 0, date('m', $d), date('d', $d), date('Y', $d) - 1) * 1000;
        $timeLabel = [];
        $timeLabel [$lastday] = Manager::getService('Translate')->translateInWorkingLanguage('Search.Facets.Label.Date.Day', 'Past 24H');
        $timeLabel [$lastweek] = Manager::getService('Translate')->translateInWorkingLanguage('Search.Facets.Label.Date.Week', 'Past week');
        $timeLabel [$lastmonth] = Manager::getService('Translate')->translateInWorkingLanguage('Search.Facets.Label.Date.Month', 'Past month');
        $timeLabel [$lastyear] = Manager::getService('Translate')->translateInWorkingLanguage('Search.Facets.Label.Date.Year', 'Past year');

        return $timeLabel;
    }

    public static function geoHashDecode($hash)
    {
        $table = '0123456789bcdefghjkmnpqrstuvwxyz';
        $ll = [];
        $minlat = -90;
        $maxlat = 90;
        $minlon = -180;
        $maxlon = 180;
        $latE = 90;
        $lonE = 180;
        for ($i = 0, $c = strlen($hash); $i < $c; ++$i) {
            $v = strpos($table, $hash[$i]);
            if (1 & $i) {
                if (16 & $v) {
                    $minlat = ($minlat + $maxlat) / 2;
                } else {
                    $maxlat = ($minlat + $maxlat) / 2;
                }
                if (8 & $v) {
                    $minlon = ($minlon + $maxlon) / 2;
                } else {
                    $maxlon = ($minlon + $maxlon) / 2;
                }
                if (4 & $v) {
                    $minlat = ($minlat + $maxlat) / 2;
                } else {
                    $maxlat = ($minlat + $maxlat) / 2;
                }
                if (2 & $v) {
                    $minlon = ($minlon + $maxlon) / 2;
                } else {
                    $maxlon = ($minlon + $maxlon) / 2;
                }
                if (1 & $v) {
                    $minlat = ($minlat + $maxlat) / 2;
                } else {
                    $maxlat = ($minlat + $maxlat) / 2;
                }
                $latE /= 8;
                $lonE /= 4;
            } else {
                if (16 & $v) {
                    $minlon = ($minlon + $maxlon) / 2;
                } else {
                    $maxlon = ($minlon + $maxlon) / 2;
                }
                if (8 & $v) {
                    $minlat = ($minlat + $maxlat) / 2;
                } else {
                    $maxlat = ($minlat + $maxlat) / 2;
                }
                if (4 & $v) {
                    $minlon = ($minlon + $maxlon) / 2;
                } else {
                    $maxlon = ($minlon + $maxlon) / 2;
                }
                if (2 & $v) {
                    $minlat = ($minlat + $maxlat) / 2;
                } else {
                    $maxlat = ($minlat + $maxlat) / 2;
                }
                if (1 & $v) {
                    $minlon = ($minlon + $maxlon) / 2;
                } else {
                    $maxlon = ($minlon + $maxlon) / 2;
                }
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
