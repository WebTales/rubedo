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

use Rubedo\Interfaces\Collection\IQueries;
use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Rubedo\Content\Context;

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
            
            if (! Manager::getService('Acl')->hasAccess("write.ui.queries")) {
                $obj['readOnly'] = true;
            }
        }
        
        return $obj;
    }
    
    /**
     * Rebuild query object to be compatible with MongoDB
     *
     * @param array $obj
     *     Query object
     * @return array
     */
    protected function boToDbQuery($obj) {
        return $obj;
    }
    
    /**
     * Rebuild query object to be compatible with the back office
     *
     * @param array $obj
     *     Query object
     * @return array
     */
    protected function dbToBoQuery($obj) {     
        return $obj;
    }
    
    /**
     * Return an array of filters and sorting based on a query object of manual type
     *
     * @param array $query
     * @return array unknown
     */
    protected function _getFilterArrayForManual ($query)
    {
        $filters = Filter::factory()->addFilter(Filter::factory('InUid')->setValue($query['query']))
        ->addFilter(Filter::factory('Value')->setName('status')
            ->setValue('published'));
    
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
     * Add the filter to the query in function of the field type (date, time, number...)
     *
     * @param string $fieldType
     *     datetime, numberfield ...
     * @param string $property
     *     The name of the field
     * @param array $value
     *     Config of the field (operator and value)
     * @param string $ruleOperator
     *     Contain the mongo operator of the filter ($lt, $gt, $eq...)
     */
    protected function setFilters($fieldType, $property, $value, $ruleOperator, $filters) {
    
        //Create the filter in terms of the field type
        switch ($fieldType) {
            case "datefield":
                if (isset($value['rule']) && isset($value['value'])) {
                    $nextDate = new \DateTime($value['value']);
                    $nextDate->add(new \DateInterval('PT23H59M59S'));
                    $nextDate = (array) $nextDate;
    
                    if($property === "createTime" || $property === "lastUpdateTime") {
                        if ($ruleOperator === 'eq') {
    
                            $filters->addFilter(Filter::factory('OperatorToValue')->setName($property)
                                ->setOperator('$gt')
                                ->setValue($this->_dateService->convertToTimeStamp($value['value'])));
    
                            $filters->addFilter(Filter::factory('OperatorToValue')->setName($property)
                                ->setOperator('$lt')
                                ->setValue($this->_dateService->convertToTimeStamp($nextDate['date'])));
                        } elseif ($ruleOperator === '$gt') {
                            $filters->addFilter(Filter::factory('OperatorToValue')->setName($property)
                                ->setOperator($ruleOperator)
                                ->setValue($this->_dateService->convertToTimeStamp($nextDate['date'])));
                        } elseif ($ruleOperator === '$lte') {
                            $filters->addFilter(Filter::factory('OperatorToValue')->setName($property)
                                ->setOperator('$lte')
                                ->setValue($this->_dateService->convertToTimeStamp($nextDate['date'])));
                        } else {
                            $filters->addFilter(Filter::factory('OperatorToValue')->setName($property)
                                ->setOperator($ruleOperator)
                                ->setValue($this->_dateService->convertToTimeStamp($value['value'])));
                        }
                    } else {
                        if ($ruleOperator === 'eq') {
    
                            $filters->addFilter(Filter::factory('OperatorToValue')->setName($property)
                                ->setOperator('$gt')
                                ->setValue((string)$this->_dateService->convertToTimeStamp($value['value'])));
    
                            $filters->addFilter(Filter::factory('OperatorToValue')->setName($property)
                                ->setOperator('$lt')
                                ->setValue((string)$this->_dateService->convertToTimeStamp($nextDate['date'])));
                        } elseif ($ruleOperator === '$gt') {
                            $filters->addFilter(Filter::factory('OperatorToValue')->setName($property)
                                ->setOperator($ruleOperator)
                                ->setValue((string)$this->_dateService->convertToTimeStamp($nextDate['date'])));
                        } elseif ($ruleOperator === '$lte') {
                            $filters->addFilter(Filter::factory('OperatorToValue')->setName($property)
                                ->setOperator('$lte')
                                ->setValue((string)$this->_dateService->convertToTimeStamp($nextDate['date'])));
                        } else {
                            $filters->addFilter(Filter::factory('OperatorToValue')->setName($property)
                                ->setOperator($ruleOperator)
                                ->setValue((string)$this->_dateService->convertToTimeStamp($value['value'])));
                        }
                    }
                }
                break;
            case "numberfield":
            case "timefield":
                $filters->addFilter(Filter::factory('OperatorToValue')->setName($property)
                ->setOperator($ruleOperator)
                ->setValue($value['value']));
                break;
            default:
                throw new \Rubedo\Exceptions\Server('not implemented ' . $fieldType);
                break;
        }
    }
    
    /**
     * Return an array of filters and sorting based on a query object
     *
     * @param array $query
     * @return array unknown
     */
    protected function _getFilterArrayForQuery ($query)
    {
        if (Context::isDraft() == 'false' || Context::isDraft() == false) {
            $this->_workspace = 'live';
        } else {
            $this->_workspace = 'draft';
        }
        
        $this->_dateService = Manager::getService('Date');
        $this->_taxonomyReader = Manager::getService('TaxonomyTerms');
    
        $sort = array();
        $filters = Filter::factory();
    
        $operatorsArray = array(
            '$lt' => '<',
            '$lte' => '<=',
            '$gt' => '>',
            '$gte' => '>=',
            '$ne' => '!=',
            'eq' => '='
        );
    
        /* Add filters on TypeId and publication */
        $filters->addFilter(Filter::factory('In')->setName('typeId')
            ->setValue($query['contentTypes']));
    
        $filters->addFilter(Filter::factory('Value')->setName('status')
            ->setValue('published'));
    
        // add computed filter for vocabularies rules
        if (is_array($query['vocabularies'])) {
            if (! isset($query['vocabulariesRule'])) {
                $query['vocabulariesRule'] = 'ET';
            }
            $filters->addFilter($this->_getVocabulariesFilters($query['vocabularies'], $query['vocabulariesRule']));
        }
    
        /* Add filter on FieldRule */
        foreach ($query['fieldRules'] as $key => $value) {
            if (isset($value['field'])){
                $property=$value['field'];
            }
            else{
                $property=$key;
            }
            //Contain the type of the field (date, time, text ...)
            $fieldType = "";
    
            //Get the field name because $property looks like "fields.fieldName"
            $propertyPath = explode(".", $property);
            $propertyCount = count($propertyPath) - 1;
            $field = $propertyPath[$propertyCount];
            
            //Set the type of the field for system fields
            if($property === "createTime" || $property === "lastUpdateTime") {
                $fieldType = "datefield";
            } else {
                //Determine the type of the field in terms of the content types
                foreach ($query['contentTypes'] as $contentTypeId) {
                    //Get content type object
                    $contentType = Manager::getService("ContentTypes")->findById($contentTypeId);
    
                    //Check if the field is in the content type
                    foreach ($contentType["fields"] as $fieldConfig) {
                        if ($fieldConfig["config"]["name"] == $field) {
                            if($fieldType == "") {
                                //Define field type
                                $fieldType = $fieldConfig["cType"];
                            } elseif ($fieldType != $fieldConfig["cType"]) {
                                //We throw an exception if the field type is not the same in all content types
                                throw new \Rubedo\Exceptions\Server("The cType of the field must be the same in all the selected content types", "Exception98");
                            }
                        }
                    }
                }
            }
    
            //Throw an exception if the field type is not set
            if($fieldType === "") {
                throw new \Rubedo\Exceptions\Server("The server is unable to determine the cType of the field", "Exception99");
            }
    
            if(!isset($value["rule"])) {
                $value["rule"] = null;
            }
    
            //Get the  operator in the array
            $ruleOperator = array_search($value['rule'], $operatorsArray);
    
            //Set the filter of the query
            if (isset($value['value'])) {
                $this->setFilters($fieldType, $property, $value, $ruleOperator, $filters);
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
        if (($vocabulariesRule == 'OU')||($vocabulariesRule == 'OR')) {
            $filters = Filter::factory('Or');
        } else {
            $filters = Filter::factory('And');
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
        if ($key == 'navigation') {
            foreach ($value['terms'] as &$term) {
                if ($term == "currentPage") {
                    $currentPage = Manager::getService('PageContent')->getCurrentPage();
                    if (! $currentPage) {
                        throw new \Rubedo\Exceptions\Server('Current page is not defined.', "Exception49");
                    }
                    $term = $currentPage;
                }
            }
        }
    
        if(!isset($value["rule"])) {
            $value["rule"] = null;
        }
    
        if (is_array($value['rule'])) {
            $rule = array_pop($value['rule']);
        } else {
            $rule = $value['rule'];
        }
        switch ($rule) {
            case 'allRec':
                // verify all branches => at least one of each branch
                $filters = Filter::factory('And');
    
                // Definie each sub conditions
                foreach ($value['terms'] as $child) {
                    $terms = $this->_taxonomyReader->fetchAllChildren($child);
                    $termsArray = array(
                        $child
                    );
                    foreach ($terms as $taxonomyTerms) {
                        $termsArray[] = $taxonomyTerms["id"];
                    }
                    // some of a branch
                    $filters->addFilter(Filter::factory('In')->setName($this->_workspace . '.taxonomy.' . $key)
                        ->setValue($termsArray));
                }
    
                break;
            case 'all': // include all terms
                $filters = Filter::factory('OperatorToValue')->setName($this->_workspace . '.taxonomy.' . $key)
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
                $filters = Filter::factory('In')->setName($this->_workspace . '.taxonomy.' . $key)->setValue($value['terms']);
                break;
            case 'notRec':
                foreach ($value['terms'] as $child) {
                    $terms = $this->_taxonomyReader->fetchAllChildren($child);
                    foreach ($terms as $taxonomyTerms) {
                        $value['terms'][] = $taxonomyTerms["id"];
                    }
                }
            case 'not': // include all terms
                $filters = Filter::factory('NotIn')->setName($this->_workspace . '.taxonomy.' . $key)->setValue($value['terms']);
    
                break;
            default:
                Throw new \Rubedo\Exceptions\Server('Rule "%1$s" not implemented.', "Exception50", $rule);
                break;
        }
    
        return $filters;
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
     * @return boolean multitype:
     */
    public function getQueryById ($id = null)
    {
        if ($id === null) {
            return false;
        }
        
        $query = $this->findById($id);
        
        //Edit the query to be compatible with the BO
        $query = $this->dbToBoQuery($query);
        
        if ($query) {
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
        
        //Edit the query to be compatible with the BO
        $query = $this->dbToBoQuery($query);
        
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
     * Update a query
     *
     * @param array $obj
     *            query object
     * @param array $options            
     * @return array
     */
    public function update (array $obj, $options = array()) {
        //Rebuild query object to be compatible with MongoDB
        $obj = $this->boToDbQuery($obj);
        
        //Update the query with the new values
        //return parent::update($obj, $options);
        unset($obj['readOnly']);
        $result = $this->_dataService->update($obj, $options);
        if ($result['success']) {
            $result['data'] = $this->_addReadableProperty($result['data']);
            $result['data'] = $this->dbToBoQuery($result['data']);
        }
        
        return $result;
    }
    
    /**
     * Find an item given by its literral ID
     *
     * @param string $contentId            
     * @param boolean $forceReload
     *            should we ensure reading up-to-date content
     * @return array
     */
    public function findById ($contentId, $forceReload = false) {
        $result = parent::findById($contentId, $forceReload);
        
        //Edit the query to be compatible with the BO
        $result = $this->dbToBoQuery($result);
        
        return $result;
    }
    
    /**
     * Find an item given by its name (find only one if many)
     *
     * @param string $name
     * @return array
     */
    public function findByName ($name) {
        $query = parent::findByName($name);
        
        return $this->dbToBoQuery($query);
    }
    
    /**
     * Do a findone request
     *
     * @param \WebTales\MongoFilters\IFilter $value
     *            search condition
     * @return array
     */
    public function findOne (\WebTales\MongoFilters\IFilter $value) {
        $query = parent::findOne($value);
        
        return $this->dbToBoQuery($query);
    }
    
    /**
     * Do a find request on the current collection
     *
     * @param array $filters
     *            filter the list with mongo syntax
     * @param array $sort
     *            sort the list with mongo syntax
     * @return array
     */
    public function getList (\WebTales\MongoFilters\IFilter $filters = null, $sort = null, $start = null, $limit = null) {
        $list = parent::getList($filters, $sort, $start, $limit);
        
        foreach ($list["data"] as $key => $value) {
            $list["data"][$key] = $this->dbToBoQuery($value);
        }
        
        return $list;
    }
}