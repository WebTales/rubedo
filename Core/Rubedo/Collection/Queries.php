<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IQueries, Rubedo\Services\Manager;

/**
 * Service to handle Queries
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Queries extends AbstractCollection implements IQueries
{

    protected $_model = array(
        "name" => array(
            "domain" => "string",
            "required" => true
        ),
        "query" => array(
            "domain" => "array",
            "required" => true,
            "items" => array(
                "vocabularies" => array(
                    "domain" => "list",
                    "required" => true,
                    "items" => array(
                        "terms" => array(
                            "domain" => "list",
                            "required" => false
                        ),
                        "rule" => array(
                            "domain" => "list",
                            "required" => false
                        )
                    )
                ),
                "fieldRules" => array(
                    "domain" => "list",
                    "required" => true,
                    "items" => array(
                        "domain" => "string",
                        "required" => false
                    )
                ),
                "contentTypes" => array(
                    "domain" => "list",
                    "required" => true,
                    "items" => array(
                        "domain" => "string",
                        "required" => false
                    )
                ),
                "vocabulariesRule" => array(
                    "domain" => "string",
                    "required" => true
                ),
                "queryName" => array(
                    "domain" => "string",
                    "required" => true
                )
            )
        ),
        "averageDuration" => array(
            "domain" => "integer",
            "required" => true
        ),
        "count" => array(
            "domain" => "integer",
            "required" => true
        ),
        "usage" => array(
            "domain" => "list",
            "required" => true,
            "items" => array(
                "domain" => "string",
                "required" => true
            )
        ),
        "type" => array(
            "domain" => "string",
            "required" => true
        )
    );

    protected $_indexes = array(
        array(
            'keys' => array(
                'type' => 1
            )
        )
    );

    public function __construct ()
    {
        $this->_collectionName = 'Queries';
        parent::__construct();
    }

    /**
     * Return an array of filter and sort params for the query given by its ID
     *
     * result is formatted
     * array(
     * "filter" => $filterArray,
     * "sort" => $sort
     * )
     *
     * @param string $id            
     * @return array
     */
    public function getFilterArrayById ($id = null)
    {
        if ($id === null) {
            return false;
        }
        $query = $this->findById($id);
        if ($query) {
            return $this->getFilterArrayByQuery($query);
        } else {
            return false;
        }
    }

    /**
     * Return an array of filter and sort params for the given query
     *
     * result is formatted
     * array(
     * "filter" => $filterArray,
     * "sort" => $sort
     * )
     *
     * @param array $query            
     * @return array
     */
    public function getFilterArrayByQuery ($query = null)
    {
        $this->_dateService = Manager::getService('Date');
        $this->_dataReader = Manager::getService('Contents');
        $this->_typeReader = Manager::getService('ContentTypes');
        $this->_taxonomyReader = Manager::getService('TaxonomyTerms');
        
        if ($query === null) {
            return false;
        }
        $queryType = $query['type'];

        $sort = array();
        $operatorsArray = array(
            '$lt' => '<',
            '$lte' => '<=',
            '$gt' => '>',
            '$gte' => '>=',
            '$ne' => '!=',
            'eq' => '='
        );
        if (isset($query['query']) && $query['type'] != 'manual') {
            $query = $query['query'];
            /* Add filters on TypeId and publication */
            $filterArray[] = array(
                'operator' => '$in',
                'property' => 'typeId',
                'value' => $query['contentTypes']
            );
            $filterArray[] = array(
                'property' => 'status',
                'value' => 'published'
            );
            
            /* Add filter on taxonomy */
            foreach ($query['vocabularies'] as $key => $value) {
                if (isset($value['rule'])) {
                    if ($value['rule'] == "some") {
                        $taxOperator = '$in';
                    } elseif ($value['rule'] == "all") {
                        $taxOperator = '$all';
                    } elseif ($value['rule'] == "someRec") {
                        if (count($value['terms']) > 0) {
                            foreach ($value['terms'] as $child) {
                                $terms = $this->_taxonomyReader->fetchAllChildren($child);
                                foreach ($terms as $taxonomyTerms) {
                                    $value['terms'][] = $taxonomyTerms["id"];
                                }
                            }
                        }
                        $taxOperator = '$in';
                    } else {
                        $taxOperator = '$in';
                    }
                } else {
                    $taxOperator = '$in';
                }
                if (count($value['terms']) > 0) {
                    $filterArray[] = array(
                        'operator' => $taxOperator,
                        'property' => 'taxonomy.' . $key,
                        'value' => $value['terms']
                    );
                }
            }
            /* Add filter on FieldRule */
            foreach ($query['fieldRules'] as $property => $value) {
                if (isset($value['rule']) && isset($value['value'])) {
                    $ruleOperator = array_search($value['rule'], $operatorsArray);
                    $nextDate = new \DateTime($value['value']);
                    $nextDate->add(new \DateInterval('PT23H59M59S'));
                    $nextDate = (array) $nextDate;
                    if ($ruleOperator === 'eq') {
                        $filterArray[] = array(
                            'operator' => '$gt',
                            'property' => $property,
                            'value' => $this->_dateService->convertToTimeStamp($value['value'])
                        );
                        $filterArray[] = array(
                            'operator' => '$lt',
                            'property' => $property,
                            'value' => $this->_dateService->convertToTimeStamp($nextDate['date'])
                        );
                    } elseif ($ruleOperator === '$gt') {
                        $filterArray[] = array(
                            'operator' => $ruleOperator,
                            'property' => $property,
                            'value' => $this->_dateService->convertToTimeStamp($nextDate['date'])
                        );
                    } elseif ($ruleOperator === '$lte') {
                        $filterArray[] = array(
                            'operator' => $ruleOperator,
                            'property' => $property,
                            'value' => $this->_dateService->convertToTimeStamp($nextDate['date'])
                        );
                    } else {
                        $filterArray[] = array(
                            'operator' => $ruleOperator,
                            'property' => $property,
                            'value' => $this->_dateService->convertToTimeStamp($value['value'])
                        );
                    }
                }
                /*
                 * Add Sort
                 */
                if (isset($value['sort'])) {
                    $sort[] = array(
                        'property' => $property,
                        'direction' => $value['sort']
                    );
                } else {
                    $sort[] = array(
                        'property' => 'id',
                        'direction' => 'DESC'
                    );
                }
            }
        } else {
            $filterArray[] = array(
                'operator' => '$in',
                'property' => 'id',
                'value' => $query['query']
            );
            $filterArray[] = array(
                'property' => 'status',
                'value' => 'published'
            );
            $sort[] = array(
                'property' => 'id',
                'direction' => 'DESC'
            );
        }
        $returnArray = array(
            "filter" => $filterArray,
            "sort" => $sort,
            "queryType" => $queryType
        );
        return $returnArray;
    }
}
