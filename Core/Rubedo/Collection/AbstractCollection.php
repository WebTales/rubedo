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
     * Indexes of the collection
     * 
     * should be an array of index.
     * An index should be an array('keys'=>array,'options'=>array) which define fields and options of the index
     * 
     * @var array
     */
    protected $_indexes = array();
    
    /**
     * name of the collection
     *
     * @var string
     */
    protected $_collectionName;

    /**
     * data access service
     *
     * @var \Rubedo\Mongo\DataAccess
     */
    protected $_dataService;

    /**
     * description of content data structure
     *
     * @var array
     */
    protected $_model = array();
	
	protected $_errors = array();
    
    /**
     * If true, no request should be filtered by user access rights
     * 
     * @var boolean
     */
    protected static $_isUserFilterDisabled = false;
    
    
    /**
     * store already found objects
     * 
     * @var array
     */
   protected static $_fetchedObjects = array();

    protected function _init ()
    {
        // init the data access service
        $this->_dataService = Manager::getService('MongoDataAccess');
        $this->_dataService->init($this->_collectionName);
    }

    public function __construct ()
    {
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
    public function getList ($filters = null, $sort = null, $start = null, $limit = null)
    {        	
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
                                '$regex' => $this->_dataService->getRegex('/.*' . $value["value"] . '.*/i')
                            )
                        ));
                    } elseif (isset($value["operator"])) {
                    	if($value['value']==array() || $value['value']=="" || !isset($value['value'])){
                    		continue;
                    	}
						
                		$this->_dataService->addFilter(array(
                        $value["property"] => array(
                            $value["operator"] => $value["value"]
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
     * return a list with its parent-line
     *
     * @param array $filters            
     * @return array:
     */
    public function getListWithAncestors ($filters = null)
    {
        $returnArray = array();
        $listResult = $this->getList($filters);
        $list = $listResult['data'];
        foreach ($list as $item) {
            $returnArray = $this->_addParentToArray($returnArray, $item);
        }
        $listResult['count'] = count($returnArray);
        $listResult['data'] = array_values($returnArray);
        return $listResult;
    }

    /**
     * add parent-line of an item to an array
     *
     * @param array $array            
     * @param array $item            
     * @param int $max            
     * @return array
     */
    protected function _addParentToArray ($array, $item, $max = 5)
    {
        if (isset($array[$item['id']])) {
            return $array;
        }
        $array[$item['id']] = $item;
        if ($item['parentId'] == 'root') {
            return $array;
        }
        if (isset($array[$item['parentId']])) {
            return $array;
        }
        
        $parentItem = Manager::getService('Groups')->findById($item['parentId']);
        
        if ($parentItem) {
            $array[$parentItem['id']] = $parentItem;
            $array = $this->_addParentToArray($array, $parentItem, $max - 1);
        }
        
        return $array;
    }

    /**
     * Find an item given by its literral ID
     *
     * @param string $contentId    
     * @param boolean $forceReload should we ensure reading up-to-date content        
     * @return array
     */
    public function findById ($contentId,$forceReload = false)
    {
        $contentId = (string) $contentId;
        $className = (string) get_class($this);
        if(!isset(self::$_fetchedObjects[$className])){
            self::$_fetchedObjects[$className] = array();
        }
        if($forceReload || !isset(self::$_fetchedObjects[$className][$contentId])){
            self::$_fetchedObjects[$className][$contentId] = $this->_dataService->findById($contentId);
        }
        return self::$_fetchedObjects[$className][$contentId];
    }

    /**
     * Find an item given by its name (find only one if many)
     *
     * @param string $name            
     * @return array
     */
    public function findByName ($name)
    {
        return $this->_dataService->findByName($name);
    }

    /**
     * Do a findone request
     *
     * @deprecated
     *
     *
     *
     * @param array $value
     *            search condition
     * @return array
     */
    public function findOne ($value)
    {
        return $this->_dataService->findOne($value);
    }

    /**
     *
     * @deprecated
     *
     *
     *
     * @param unknown $filter            
     * @param unknown $fieldRule            
     * @return MongoCursor
     */
    public function customFind ($filter = array(), $fieldRule = array())
    {
        return $this->_dataService->customFind($filter, $fieldRule);
    }

    /**
     * Update an objet in the current collection
     *
     * Shouldn't be used if doing a simple update action
     *
     * @deprecated
     *
     *
     *
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
        return $this->_dataService->customUpdate($data, $updateCond, $options);
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
    public function create (array $obj, $options = array('safe'=>true))
    {
       	$this->_filterInputData($obj);
		
        unset($obj['readOnly']);
        return $this->_dataService->create($obj, $options);
    }

    /**
     * Return validated data from input data based on collection rules
     *
     * @param array $obj            
     * @return array:
     */
    protected function _filterInputData (array $obj, array $model = null)
    {			
		if($model == null) {
			$model = $this->_model;
		}
			
		foreach($model as $key => $value){
			//If the configuration is not specified for the current field
			if(isset($value['domain']) && isset($value['required'])){
				if(isset($obj[$key])){
					switch ($value['domain']) {
						
						/**
						 * Case with a list domain
						 * 
						 * Check if the elements of the object array correspond with the model
						 */
						case 'list':
							if(isset($value['items']) && isset($value['items']['domain']) && isset($value['items']['required'])) {
								if($this->_isValid($obj[$key], $value['domain'])) {
									if(count($obj[$key]) > 0) {
										foreach ($obj[$key] as $subKey => $subValue) {
											if($value['items']['domain'] != "list" && $value['items']['domain'] != "array") {
												if(!$this->_isValid($subValue, $value['items']['domain'])) {
													$this->_errors[$key][$subKey] = '"'.$subValue.'" doesn\'t correspond with the domain "'.$value['domain'].'"';
												}
											} else {
												if($value['items']['domain'] == "list"){
													if(isset($value['items']['items']['domain']) && isset($value['items']['items']['required'])){
														$this->_filterInputData(array('key' => $subValue), array('key' => $value['items']['items']));
													} else {
														$this->_filterInputData($subValue, $value['items']['items']);
													}
												} else {
													$this->_filterInputData($subValue, $value['items']['items']);
												}
											}
										}
									} else {
										if($value['items']['required'] == true) {
											$this->_errors[$key] = 'this field is required';
										} else {
											continue;
										}
									}
								} else {
									$this->_errors[$key] = 'doesn\'t correspond with the domain "'.$value['domain'].'"';
								}
							} else {
								continue;
							}
							break;
						
						/**
						 * Case with an array domain
						 * 
						 * Recall _filterInputData function with the object array and it's model
						 */
						case 'array':
							if(isset($value['items']) && count($value['items']) > 0) {
								if($this->_isValid($obj[$key], $value['domain'])) {
									if(count($obj[$key]) > 0) {
										$this->_filterInputData($obj[$key], $value['items']);
									} else {
										if($value['items']['required'] == true) {
											$this->_errors[$key] = 'this field is required';
										} else {
											continue;
										}
									}
								} else {
									$this->_errors[$key] = 'doesn\'t correspond with the domain "'.$value['domain'].'"';
								}
							} else {
								continue;
							}
							break;
						
						/**
						 * Case with a simple domain
						 * 
						 * Just check if the current object value correspond with the model
						 */
						default :
							if(!$this->_isValid($obj[$key], $value['domain'])) {
								$this->_errors[$key] = '"'.$obj[$key].'" doesn\'t correspond with the domain "'.$value['domain'].'"';
							}
							break;
					}
				} else {
					if(isset($value['items']) && $value['items']['required'] == true) {
						$this->_errors[$key] = 'this field is required';
					} else {
						continue;
					}
				}
			}
		}

		return $obj;
	}  

    /**
     * Is the data a valid input for the domain
     *
     * @param mixed $data            
     * @param string $domain            
     * @throws Exception
     * @return boolean
     */
    protected function _isValid ($data, $domain)
    {
        $domainClassName = 'Rubedo\\Domains\\D' . ucfirst($domain);
        if (! class_exists($domainClassName)) {
            throw new \Rubedo\Exceptions\User('domain not defined :' . (string) $domain);
        }
        return $domainClassName::isValid($data);
    }

    /**
     * getter of the model
     *
     * @return array
     */
    public function getModel ()
    {
        return $this->_model;
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
    public function update (array $obj, $options = array('safe'=>true))
    {
        unset($obj['readOnly']);
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
    public function destroy (array $obj, $options = array('safe'=>true))
    {
        return $this->_dataService->destroy($obj, $options);
    }
    
    /*
     * (non-PHPdoc) @see
     * \Rubedo\Interfaces\Collection\IAbstractCollection::count()
     */
    public function count ($filters = null)
    {
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
                                '$regex' => $this->_dataService->getRegex('/.*' . $value["value"] . '.*/i')
                            )
                        ));
                    } elseif (isset($value["operator"])) {
                        $this->_dataService->addFilter(array(
                            $value["property"] => array(
                                $value["operator"] => $value["value"]
                            )
                        ));
                    }
            }
        }
        return $this->_dataService->count();
    }

    /**
     *
     * @deprecated
     *
     *
     *
     * @param unknown $deleteCond            
     * @param unknown $options            
     * @return Ambigous <boolean, multitype:>
     */
    public function customDelete ($deleteCond, $options = array('safe'=>true))
    {
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
    public function readChild ($parentId, $filters = null, $sort = null)
    {
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
                    } elseif (isset($value["operator"])) {
                        $this->_dataService->addFilter(array(
                            $value["property"] => array(
                                $value["operator"] => $value["value"]
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
            $this->_dataService->addSort(array(
                "orderValue" => 1
            ));
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
        if (! isset($item['parentId'])) {
            return array();
        }
        if ($item['parentId'] == 'root') {
            return array();
        }
        if ($limit <= 0) {
            return array();
        }
        $parentItem = $this->findById($item['parentId']);
        $returnArray = $this->getAncestors($parentItem, $limit - 1);
        $returnArray[] = $parentItem;
        return $returnArray;
    }

    public function fetchAllChildren ($parentId, $filters = null, $sort = null, $limit = 10)
    {
        $returnArray = array();
        $children = $this->readChild($parentId, $filters, $sort); // Read child
                                                                  // of
                                                                  // the
                                                                  // parentId
        foreach ($children as $value) { // for each child returned before if
                                        // they can have children (leaf===false)
                                        // do another read child.
            $returnArray[] = $value;
            if ($value['leaf'] === false && $limit > 0) {
                $returnArray = array_merge($returnArray, $this->readChild($value['id'], $filters, $sort, $limit - 1));
            }
        }
        return $returnArray;
    }
    
    public function readTree(){
        return $this->_dataService->readTree();
    }

    public function drop ()
    {
        $result = $this->_dataService->drop();
        if ($result['ok']) {
            return true;
        } else {
            return false;
        }
    }
    
	/**
     * @return the $_isUserFilterDisabled
     */
    public static final function isUserFilterDisabled ()
    {
        return self::$_isUserFilterDisabled;
    }

	/**
     * @param boolean $_isUserFilterDisabled
     * @return boolean previous value of the param
     */
    public static final function disableUserFilter ($_isUserFilterDisabled=true)
    {
        $oldValue = self::$_isUserFilterDisabled;
        self::$_isUserFilterDisabled = $_isUserFilterDisabled;
        return $oldValue;
    }
    
	/**
	 *  (non-PHPdoc)
     * @see \Rubedo\Interfaces\Collection\IAbstractCollection::checkIndexes()
     */
    public function checkIndexes ()
    {
       $result = true;
        foreach ($this->_indexes as $index){
            $result = $result && $this->_dataService->checkIndex($index['keys']);
        }
        return $result;
        
    }

	/**
	 *  (non-PHPdoc)
     * @see \Rubedo\Interfaces\Collection\IAbstractCollection::ensureIndexes()
     */
    public function ensureIndexes ()
    {
        $result = true;
        foreach ($this->_indexes as $index){
            $result = $result && $this->_dataService->ensureIndex($index['keys'],isset($index['options'])?$index['options']:array());
        }
        return $result;        
    }


    
    
    
}
	
