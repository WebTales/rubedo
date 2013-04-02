<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
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
    
    /**
     * Add a readOnly field to contents based on user rights
     *
     * @param array $obj
     * @return array
     */
    protected function _addReadableProperty ($obj)
    {
    	if (! self::isUserFilterDisabled()) {
    
    		if (!Manager::getService('Acl')->hasAccess("write.ui.queries")) {
    			$obj['readOnly'] = true;
    		}
    	}
    
    	return $obj;
    }

    public function __construct ()
    {
        $this->_collectionName = 'Queries';
        parent::__construct();
    }
	
    /**
     * Return a query
     * 
     * @param string $id
     * @return boolean|multitype:
     */
    public function getQueryById($id = null) {
    	if ($id === null) {
    		return false;
    	}
    	
    	$query = $this->findById($id);
    	
    	if($query) {
    		return $query;
    	} else {
    		return false;
    	}
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
     * @return array | false
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
     * @return array | false
     */
    public function getFilterArrayByQuery ($query = null)
    {
        if ($query === null) {
            return false;
        }
        
        $queryType = $query['type'];
        
        if (isset($query['query']) && $query['type'] != 'manual') {
            $returnArray = $this->_getFilterArrayForQuery($query['query']);
        } else {
            $returnArray = $this->_getFilterArrayForManual($query);
        }
        $returnArray["queryType"] = $queryType;
        return $returnArray;
    }

    protected function _getFilterArrayForManual ($query)
    {
        $filterArray = array();
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
        return array(
            "filter" => $filterArray,
            "sort" => $sort
        );
    }

    protected function _getFilterArrayForQuery ($query)
    {
        $this->_workspace = \Zend_Registry::isRegistered('draft')?(\Zend_Registry::get('draft') ? 'draft' : 'live'):'live';
        $this->_dateService = Manager::getService('Date');
        $this->_taxonomyReader = Manager::getService('TaxonomyTerms');
        
        $sort = array();
        $filterArray = array();
        
        $operatorsArray = array(
            '$lt' => '<',
            '$lte' => '<=',
            '$gt' => '>',
            '$gte' => '>=',
            '$ne' => '!=',
            'eq' => '='
        );
        
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
        
        // add computed filter for vocabularies rules
        if (is_array($query['vocabularies'])) {
            if(!isset($query['vocabulariesRule'])){
                $query['vocabulariesRule']='ET';
            }
            $filterArray[] = $this->_getVocabulariesFilters($query['vocabularies'], $query['vocabulariesRule']);
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
        
        return array(
            "filter" => $filterArray,
            "sort" => $sort
        );
    }

    /**
     * Build the taxonomy filter
     *
     * @param array $vocabularies
     *            array of rules by vocabulary
     * @param string $vocabulariesRule
     *            OU | ET rule to assemble all filters
     * @return array
     */
    protected function _getVocabulariesFilters ($vocabularies, $vocabulariesRule = 'OU')
    {
        $filterArray = array();
        foreach ($vocabularies as $key => $value) {
            if (count($value['terms']) > 0) {
                $filterArray[] = $this->_getVocabularyCondition($key, $value);
            }
        }
        if ($vocabulariesRule == 'OU') {
            $filterArray = array(
                'operator' => '$or',
                'value' => $filterArray
            );
        } else {
            $filterArray = array(
                'operator' => '$and',
                'value' => $filterArray
            );
        }
        
        return $filterArray;
    }

    /**
     * Build filter for a given vocabulary
     *
     * @param string $key
     *            vocabulary name
     * @param array $value
     *            vocabulary parameters (rule and terms)
     * @return array
     */
    protected function _getVocabularyCondition ($key, $value)
    {
        if (is_array($value['rule'])) {
            $rule = array_pop($value['rule']);
        } else {
            $rule = $value['rule'];
        }
        
        switch ($rule) {
            case 'allRec':
                $subArray = array();
                foreach ($value['terms'] as $child) {
                    $terms = $this->_taxonomyReader->fetchAllChildren($child);
                    $termsArray = array(
                        $child
                    );
                    foreach ($terms as $taxonomyTerms) {
                        $termsArray[] = $taxonomyTerms["id"];
                    }
                    // some of a branch
                    $subArray[] = array(
                        $this->_workspace . '.taxonomy.' . $key => array(
                            '$in' => $termsArray
                        )
                    );
                }
                // verify all branches => at least one of each branch
                $result = array(
                    '$and' => $subArray
                );
                break;
            case 'all': // include all terms
                $result = array(
                    $this->_workspace . '.taxonomy.' . $key => array(
                        '$all' => $value['terms']
                    )
                );
                break;
            case 'someRec': // just add children and do 'some' condition
                foreach ($value['terms'] as $child) {
                    $terms = $this->_taxonomyReader->fetchAllChildren($child);
                    foreach ($terms as $taxonomyTerms) {
                        $value['terms'][] = $taxonomyTerms["id"];
                    }
                }
            case 'some': // simplest one: at least on of the termes
            default:
                $result = array(
                    $this->_workspace . '.taxonomy.' . $key => array(
                        '$in' => $value['terms']
                    )
                );
                break;
        }
        
        return $result;
    }
}
