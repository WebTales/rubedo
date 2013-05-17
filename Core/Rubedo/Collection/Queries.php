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

use Rubedo\Interfaces\Collection\IQueries, Rubedo\Services\Manager, \WebTales\MongoFilters\Filter;

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

    /**
     * Return an array of filters and sorting based on a query object of manual type
     * 
     * @param array $query
     * @return array  unknown
     */
    protected function _getFilterArrayForManual ($query)
    {                
        $filters = Filter::Factory()
                    ->addFilter(Filter::Factory('InUid')->setValue($query['query']))
                    ->addFilter(Filter::Factory('Value')->setName('status')->setValue('published'));
        
        $sort[] = array(
            'property' => 'id',
            'direction' => 'DESC'
        );
        return array(
            "filter" => $filters,
            "sort" => $sort
        );
    }

    /**
     * Return an array of filters and sorting based on a query object
     *
     * @param array $query
     * @return array  unknown
     */
    protected function _getFilterArrayForQuery ($query)
    {
        if(\Zend_Registry::isRegistered('draft')){
            if(\Zend_Registry::get('draft') !== 'false' || \Zend_Registry::get('draft') !== false){
                $this->_workspace = 'live';
            }else{
                $this->_workspace = 'draft';
            }
        }else{
            $this->_workspace = 'live';
        }
        $this->_dateService = Manager::getService('Date');
        $this->_taxonomyReader = Manager::getService('TaxonomyTerms');
        
        $sort = array();
        $filterArray = array();
        $filters = Filter::Factory();
        
        
        $operatorsArray = array(
            '$lt' => '<',
            '$lte' => '<=',
            '$gt' => '>',
            '$gte' => '>=',
            '$ne' => '!=',
            'eq' => '='
        );
        
        /* Add filters on TypeId and publication */
        $filters->addFilter(Filter::Factory('In')->setName('typeId')->setValue($query['contentTypes']));
        
        $filters->addFilter(Filter::Factory('Value')->setName('status')->setValue('published'));
        
        // add computed filter for vocabularies rules
        if (is_array($query['vocabularies'])) {
            if(!isset($query['vocabulariesRule'])){
                $query['vocabulariesRule']='ET';
            }
            $filters->addFilter($this->_getVocabulariesFilters($query['vocabularies'], $query['vocabulariesRule']));
        }
        
        /* Add filter on FieldRule */
        foreach ($query['fieldRules'] as $property => $value) {
            if (isset($value['rule']) && isset($value['value'])) {
                $ruleOperator = array_search($value['rule'], $operatorsArray);
                $nextDate = new \DateTime($value['value']);
                $nextDate->add(new \DateInterval('PT23H59M59S'));
                $nextDate = (array) $nextDate;
                if ($ruleOperator === 'eq') {
                    
                    $filters->addFilter(Filter::Factory('OperatorToValue')->setName($property)
                        ->setOperator('$gt')
                        ->setValue($this->_dateService->convertToTimeStamp($value['value'])));
                    
                    $filters->addFilter(Filter::Factory('OperatorToValue')->setName($property)
                        ->setOperator('$lt')
                        ->setValue($this->_dateService->convertToTimeStamp($nextDate['date'])));
                } elseif ($ruleOperator === '$gt') {
                    $filters->addFilter(Filter::Factory('OperatorToValue')->setName($property)
                        ->setOperator($ruleOperator)
                        ->setValue($this->_dateService->convertToTimeStamp($nextDate['date'])));
                } elseif ($ruleOperator === '$lte') {
                    $filters->addFilter(Filter::Factory('OperatorToValue')->setName($property)
                        ->setOperator('$lte')
                        ->setValue($this->_dateService->convertToTimeStamp($nextDate['date'])));
                } else {
                    $filters->addFilter(Filter::Factory('OperatorToValue')->setName($property)
                        ->setOperator($ruleOperator)
                        ->setValue($this->_dateService->convertToTimeStamp($value['value'])));
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
            "filter" => $filters,
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
     * @return \WebTales\MongoFilters\IFilter
     */
    protected function _getVocabulariesFilters ($vocabularies, $vocabulariesRule = 'OU')
    {
        
        if ($vocabulariesRule == 'OU') {
            $filters = Filter::Factory('Or');
        } else {
            $filters = Filter::Factory('And');
        }
        
        foreach ($vocabularies as $key => $value) {
            if (count($value['terms']) > 0) {
                $filters->addFilter($this->_getVocabularyCondition($key, $value));
            }
        }
        
        return $filters;
    }

    /**
     * Build filter for a given vocabulary
     *
     * @param string $key
     *            vocabulary name
     * @param array $value
     *            vocabulary parameters (rule and terms)
     * @return \WebTales\MongoFilters\IFilter
     */
    protected function _getVocabularyCondition ($key, $value)
    {
        if($key == 'navigation'){
            foreach ($value['terms'] as &$term){
                if($term == "currentPage"){
                    $currentPage = Manager::getService('PageContent')->getCurrentPage();
                    if(!$currentPage){
                        throw new \Rubedo\Exceptions\Server('Pas de page courante définie.');
                    }
                    $term = $currentPage;
                }
            }
        }
        if (is_array($value['rule'])) {
            $rule = array_pop($value['rule']);
        } else {
            $rule = $value['rule'];
        }
        switch ($rule) {
            case 'allRec':
                // verify all branches => at least one of each branch
                $filters = Filter::Factory('And');
                
                //Definie each sub conditions
                foreach ($value['terms'] as $child) {
                    $terms = $this->_taxonomyReader->fetchAllChildren($child);
                    $termsArray = array(
                        $child
                    );
                    foreach ($terms as $taxonomyTerms) {
                        $termsArray[] = $taxonomyTerms["id"];
                    }
                    // some of a branch
                    $filters->addFilter(Filter::Factory('In')->setName($this->_workspace . '.taxonomy.' . $key)->setValue($termsArray));
                }

                break;
            case 'all': // include all terms
                $filters = Filter::Factory('OperatorToValue')
                            ->setName($this->_workspace . '.taxonomy.' . $key)
                            ->setOperator('$all')
                            ->setValue($value['terms']);
                break;
            case 'someRec': // just add children and do 'some' condition
                foreach ($value['terms'] as $child) {
                    $terms = $this->_taxonomyReader->fetchAllChildren($child);
                    foreach ($terms as $taxonomyTerms) {
                        $value['terms'][] = $taxonomyTerms["id"];
                    }
                }
            case 'some': // simplest one: at least on of the termes
                $filters = Filter::Factory('In')->setName($this->_workspace . '.taxonomy.' . $key)->setValue($value['terms']);
                break;
            case 'notRec':
                foreach ($value['terms'] as $child) {
                    $terms = $this->_taxonomyReader->fetchAllChildren($child);
                    foreach ($terms as $taxonomyTerms) {
                        $value['terms'][] = $taxonomyTerms["id"];
                    }
                }
            case 'not': // include all terms
                $filters = Filter::Factory('NotIn')->setName($this->_workspace . '.taxonomy.' . $key)->setValue($value['terms']);

                break;
            default:
                Throw new \Rubedo\Exceptions\Server("rule \"$rule\" not implemented.");
                break;
        }
        
        return $filters;
    }
}
