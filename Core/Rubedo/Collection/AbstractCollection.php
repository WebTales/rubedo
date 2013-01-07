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

use Rubedo\Interfaces\Collection\IAbstractCollection;
use Rubedo\Services\Manager;

/**
 * Class implementing the API to MongoDB
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
abstract class AbstractCollection implements IAbstractCollection
{
    /**
     * name of the collection
     *
     * @var string
     */
    protected $_collectionName;

    /**
     * data access service
     *
     * @var\Rubedo\Mongo\DataAccess
     */
    protected $_dataService;
    
    protected function _init() {
        // init the data access service
        $this->_dataService = Manager::getService('MongoDataAccess');
        $this->_dataService->init($this->_collectionName);
    }

    public function __construct() {
        $this->_init();
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
    public function getList($filters = null, $sort = null, $start = null, $limit = null) {
        if (isset($filters)) {
            foreach ($filters as $value) {
                if ((!(isset($value["operator"]))) || ($value["operator"] == "eq")) {
                    $this->_dataService->addFilter(array($value["property"] => $value["value"]));
                } else if ($value["operator"] == 'like') {
                    $this->_dataService->addFilter(array($value["property"] => array('$regex' => $this->_dataService->getRegex('/.*' . $value["value"] . '.*/i'))));
                } elseif (isset($value["operator"])) {
                    $this->_dataService->addFilter(array($value["property"] => array($value["operator"] => $value["value"])));
                }

            }
        }
        if (isset($sort)) {
            foreach ($sort as $value) {
                
                $this->_dataService->addSort(array(
                    $value["property"] => strtolower($value["direction"])
                ));
            }
        }
        if (isset($start)) {
            $this->_dataService->setFirstResult($start);
        }
        if (isset($limit)) {
            $this->_dataService->setNumberOfResults($limit);
        }

        $dataValues = $this->_dataService->read();
        
        return $dataValues;
    }

    /**
     * Find an item given by its literral ID
     * 
     * @param string $contentId            
     * @return array
     */
    public function findById($contentId) {
        return $this->_dataService->findById($contentId);
    }

    /**
     * Find an item given by its name (find only one if many)
     * 
     * @param string $name            
     * @return array
     */
    public function findByName($name) {
        return $this->_dataService->findByName($name);
    }

    /**
     * Do a findone request
     *
     * @deprecated
     * @param array $value
     *            search condition
     * @return array
     */
    public function findOne ($value) {
    	return $this->_dataService->findOne($value);
    }

    /**
     * @deprecated
     * @param unknown $filter
     * @param unknown $fieldRule
     * @return MongoCursor
     */
    public function customFind ($filter = array(), $fieldRule = array()){
	return $this->_dataService->customFind($filter,$fieldRule);			
    }
    
    /**
     * Update an objet in the current collection
     *
     * Shouldn't be used if doing a simple update action
     *
     * @deprecated
     * @see \Rubedo\Interfaces\IDataAccess::customUpdate
     * @param array $data
     *            data to update
     * @param array $updateCond
     *            array of condition to determine what should be updated
     * @param array $options
     * @return array
     */
    public function customUpdate (array $data, array $updateCond, $options = array('safe'=>true))
    {
        return $this->_dataService->customUpdate ($data, $updateCond, $options);
    }

    /**
     * Create an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::create
     * @param array $obj
     *            data object
     * @param array $options
     * @return array
     */
    public function create(array $obj, $options = array('safe'=>true)) {
        return $this->_dataService->create($obj, $options);
    }
    
    /**
     * Return validated data from input data based on collection rules
     *
     * @param array $obj
     * @return array:
     */
    protected function _filterInputData (array $obj)
    {
        return $obj;
    }

    /**
     * Update an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::update
     * @param array $obj
     *            data object
     * @param array $options
     * @return array
     */
    public function update(array $obj, $options = array('safe'=>true)) {
        return $this->_dataService->update($obj, $options);
    }

    /**
     * Delete objets in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::destroy
     * @param array $obj
     *            data object
     * @param array $options
     * @return array
     */
    public function destroy(array $obj, $options = array('safe'=>true)) {
        return $this->_dataService->destroy($obj, $options);
    }
    
    
    /* (non-PHPdoc)
     * @see \Rubedo\Interfaces\Collection\IAbstractCollection::count()
     */
    public function count($filters = null) {
        if (isset($filters)) {
            foreach ($filters as $value) {
                if ((!(isset($value["operator"]))) || ($value["operator"] == "eq")) {
                    $this->_dataService->addFilter(array($value["property"] => $value["value"]));
                } else if ($value["operator"] == 'like') {
                    $this->_dataService->addFilter(array($value["property"] => array('$regex' => $this->_dataService->getRegex('/.*' . $value["value"] . '.*/i'))));
                } elseif (isset($value["operator"])) {
                    $this->_dataService->addFilter(array($value["property"] => array($value["operator"] => $value["value"])));
                }

            }
        }
        return $this->_dataService->count();
    }

    /**
     * 
     * @deprecated
     * @param unknown $deleteCond
     * @param unknown $options
     * @return Ambigous <boolean, multitype:>
     */
    public function customDelete($deleteCond, $options = array('safe'=>true)) {
        return $this->_dataService->customDelete($deleteCond, $options);
    }

    /**
     * Find child of a node tree
     * 
     * @param string $parentId
     *            id of the parent node
     * @param array $filters
     *            array of data filters (mongo syntax)
     * @param array $sort
     *            array of data sorts (mongo syntax)
     * @return array children array
     */
    public function readChild($parentId, $filters = null, $sort = null) {
        if (isset($filters)) {
            foreach ($filters as $value) {
                if ((! (isset($value["operator"]))) || ($value["operator"] == "eq")) {
                    $this->_dataService->addFilter(array(
                        $value["property"] => $value["value"]
                    ));
                } else 
                    if ($value["operator"] == 'like') {
                        $this->_dataService->addFilter(array(
                            $value["property"] => array(
                                '$regex' => new \MongoRegex('/.*' . $value["value"] . '.*/i')
                            )
                        ));
                    }
            }
        }
        
        if (isset($sort)) {
            foreach ($sort as $value) {
                $this->_dataService->addSort(array(
                    $value["property"] => strtolower($value["direction"])
                ));
            }
        } else {
            $this->_dataService->addSort(array("orderValue" => 1));
        }
        
        return $this->_dataService->readChild($parentId);
    }

    /**
     * Return the array of ancestors for a given item
     * 
     * @param array $item
     *            object whose ancestors we're looking for
     * @param number $limit
     *            max number of ancestors to be found
     * @return array array of ancestors
     */
    public function getAncestors ($item, $limit = 10)
    {
        if ($item['parentId'] == 'root') {
            return array();
        }
        if ($limit <= 0) {
            return array();
        }
        $parentItem = $this->findById($item['parentId']);
        $returnArray = $this->getAncestors($parentItem,$limit - 1);
        $returnArray[] = $parentItem;
        return $returnArray;
        
    }

    public function fetchAllChildren($parentId, $filters = null, $sort = null,$limit=10){
    	$returnArray = array();	
   	$children=$this->readChild($parentId,$filters,$sort); //Read child of the parentId
	foreach ($children as $value) { // for each child returned before if they can have children (leaf===false) do another read child.
		$returnArray[] = $value;
		if($value['leaf']===false && $limit > 0){
			$returnArray = array_merge($returnArray,$this->readChild($value['id'],$filters,$sort,$limit-1));
		}
	}
	return $returnArray;
    }
    
    
    public function drop ()
    {
        $result = $this->_dataService->drop();
        if($result['ok']){
            return true;
        }else{
            return false;
        }
    }

}
	
