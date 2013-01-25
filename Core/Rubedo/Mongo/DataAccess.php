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
namespace Rubedo\Mongo;

use Rubedo\Interfaces\Mongo\IDataAccess;

/**
 * Class implementing the API to MongoDB
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class DataAccess implements IDataAccess
{

    /**
     * Default value of the connection string
     *
     * Used by the constructor if no specific params
     *
     * @var string
     */
    protected static $_defaultMongo;

    /**
     * Default value of the database name
     *
     * Used by the constructor if no specific params
     *
     * @var string
     */
    protected static $_defaultDb;

    /**
     * List of adapters in order not to instanciate more than once each DB
     * connection
     *
     * @var array
     */
    protected static $_adapterArray = array();

    /**
     * List of db in order not to instanciate more than once each DB object
     *
     * @var array
     */
    protected static $_dbArray = array();

    /**
     * List of db in order not to instanciate more than once each Collection
     * object
     *
     * @var array
     */
    protected static $_collectionArray = array();

    /**
     * MongoDB Connection
     *
     * @var \Mongo
     */
    protected $_adapter;

    /**
     * Object which represent the mongoDB Collection
     *
     * @var \MongoCollection
     */
    protected $_collection;

    /**
     * Object which represent the mongoDB database
     *
     * @var \MongoDB
     */
    protected $_dbName;

    /**
     * Filter condition to be used when reading
     *
     * @var array
     */
    protected $_filterArray = array();

    /**
     * Sort condition to be used when reading
     *
     * @var array
     */
    protected $_sortArray = array();

    /**
     * Number of the first result
     *
     * @var integer
     */
    protected $_firstResult = 0;

    /**
     * Number of results
     *
     * @var integer
     */
    protected $_numberOfResults = 0;

    /**
     * Fields used when reading
     *
     * @var array
     */
    protected $_fieldList = array();

    /**
     * Fields used when reading
     *
     * @var array
     */
    protected $_excludeFieldList = array();

    /**
     * Getter of the DB connection string
     *
     * @return string DB connection String
     */
    public static function getDefaultMongo ()
    {
        return static::$_defaultMongo;
    }

    /**
     * temp data for tree view
     *
     * @var array
     */
    protected $_lostChildren = array();

    /**
     * Initialize a data service handler to read or write in a MongoDb
     * Collection
     *
     * @param string $collection
     *            name of the DB
     * @param string $dbName
     *            name of the DB
     * @param string $mongo
     *            connection string to the DB server
     */
    public function init ($collection, $dbName = null, $mongo = null)
    {
        if (is_null($mongo)) {
            $mongo = self::$_defaultMongo;
        }
        
        if (is_null($dbName)) {
            $dbName = self::$_defaultDb;
        }
        
        if (gettype($mongo) !== 'string') {
            throw new \Exception('$mongo should be a string');
        }
        if (gettype($dbName) !== 'string') {
            throw new \Exception('$db should be a string');
        }
        if (gettype($collection) !== 'string') {
            throw new \Exception('$collection should be a string');
        }
        $this->_collection = $this->_getCollection($collection, $dbName, $mongo);
    }

    /**
     * Getter of Mongo adapter : should only connect once for each mongoDB
     * server
     *
     * @param string $mongo
     *            mongoDB connection string
     * @return \Mongo
     */
    protected function _getAdapter ($mongo)
    {
        if (isset(self::$_adapterArray[$mongo]) && self::$_adapterArray[$mongo] instanceof \Mongo) {
            return self::$_adapterArray[$mongo];
        } else {
            $adapter = new \Mongo($mongo);
            self::$_adapterArray[$mongo] = $adapter;
            return $adapter;
        }
    }

    /**
     * Getter of MongoDB object : should only be instanciated once for each DB
     *
     * @param string $dbName            
     * @param string $mongo            
     * @return \MongoDB
     */
    protected function _getDB ($dbName, $mongo)
    {
        if (isset(self::$_dbArray[$mongo . '_' . $dbName]) && self::$_dbArray[$mongo . '_' . $dbName] instanceof \MongoDB) {
            return self::$_dbArray[$mongo . '_' . $dbName];
        } else {
            $this->_adapter = $this->_getAdapter($mongo);
            $db = $this->_adapter->$dbName;
            self::$_dbArray[$mongo . '_' . $dbName] = $db;
            return $db;
        }
    }

    /**
     * Getter of MongoDB collection : should only be instanciated once for each
     * collection
     *
     * @param string $collection            
     * @param string $dbName            
     * @param string $mongo            
     * @return \MongoCollection
     */
    protected function _getCollection ($collection, $dbName, $mongo)
    {
        if (isset(self::$_collectionArray[$mongo . '_' . $dbName . '_' . $collection]) && self::$_collectionArray[$mongo . '_' . $dbName . '_' . $collection] instanceof \MongoCollection) {
            return self::$_collectionArray[$mongo . '_' . $dbName . '_' . $collection];
        } else {
            $this->_dbName = $this->_getDB($dbName, $mongo);
            $collection = $this->_dbName->$collection;
            self::$_collectionArray[$mongo . '_' . $dbName . '_' . $collection] = $collection;
            return $collection;
        }
    }

    /**
     * Set the main MongoDB connection string
     *
     * @param string $mongo            
     * @throws \Exception
     */
    public static function setDefaultMongo ($mongo)
    {
        if (gettype($mongo) !== 'string') {
            throw new \Exception('$mongo should be a string');
        }
        self::$_defaultMongo = $mongo;
    }

    /**
     * Set the main Database name
     *
     * @param string $dbName            
     * @throws \Exception
     */
    public static function setDefaultDb ($dbName)
    {
        if (gettype($dbName) !== 'string') {
            throw new \Exception('$dbName should be a string');
        }
        self::$_defaultDb = $dbName;
    }

    /**
     * Do a find request on the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::read()
     * @return array
     */
    public function read ()
    {
        // get the UI parameters
        $filter = $this->getFilterArray();
        $sort = $this->getSortArray();
        $firstResult = $this->getFirstResult();
        $numberOfResults = $this->getNumberOfResults();
        $includedFields = $this->getFieldList();
        $excludedFields = $this->getExcludeFieldList();
        
        // merge the two fields array to obtain only one array with all the
        // conditions
        if (! empty($includedFields) && ! empty($excludedFields)) {
            $fieldRule = $includedFields;
        } else {
            $fieldRule = array_merge($includedFields, $excludedFields);
        }
        
        // get the cursor
        $cursor = $this->_collection->find($filter, $fieldRule);
        $nbItems = $cursor->count();
        
        // apply sort, paging, filter
        $cursor->sort($sort);
        $cursor->skip($firstResult);
        $cursor->limit($numberOfResults);
        
        // switch from cursor to actual array
        if($cursor->count() > 0){
            $data = iterator_to_array($cursor);
        }else{
            $data = array();
        }
        
        
        // iterate throught data to convert ID to string and add version nulmber
        // if none
        foreach ($data as &$value) {
            $value['id'] = (string) $value['_id'];
            unset($value['_id']);
            if (! isset($value['version'])) {
                $value['version'] = 1;
            }
        }
        
        // return data as simple array with no keys
        $datas = array_values($data);
        $returnArray = array(
            "data" => $datas,
            'count' => $nbItems
        );
        return $returnArray;
    }

    /**
     * Recursive function for deleteChildren
     *
     * @param $parent is
     *            an array with the data of the parent
     * @return bool
     */
    protected function _deleteChild ($parent)
    {
        
        // Get the childrens of the current parent
        $childrensArray = $this->readChild($parent['id']);
        
        // Delete all the childrens
        if (! is_array($childrensArray)) {
            throw new \Rubedo\Exceptions\DataAccess('Should be an array');
        }
        
        foreach ($childrensArray as $key => $value) {
            self::_deleteChild($value);
        }
        
        // Delete the parent
        $returnArray = $this->destroy($parent, true);
        
        if (! $returnArray['success']) {
            $this->getResponse()->setHttpResponseCode(500);
        }
        
        return $returnArray;
    }

    /**
     * Do a find request on the current collection and return content as tree
     *
     * @see \Rubedo\Interfaces\IDataAccess::readTree()
     * @return array
     */
    public function readTree ()
    {
        $dataStore = $this->read();
        $dataStore = $dataStore['data'];
        
        $this->_lostChildren = array();
        $rootAlreadyFound = false;
        
        foreach ($dataStore as $record) {
            $id = $record['id'];
            if (isset($record['parentId']) && $record['parentId'] != 'root') {
                $parentId = $record['parentId'];
                $this->_lostChildren[$parentId][$id] = $record;
            } else {
                $rootRecord = $record;
                if ($rootAlreadyFound) {
                    throw new \Rubedo\Exceptions\DataAccess('More than one root node found');
                } else {
                    $rootAlreadyFound = true;
                }
            }
        }
        
        if (isset($rootRecord)) {
            $result = $this->_appendChild($rootRecord);
        } else {
            $result = array();
        }
        
        return $result;
    }

    /**
     * recursive function to rebuild tree from flat data store
     *
     * @param array $record
     *            root record of the tree
     * @return array complete tree array
     */
    protected function _appendChild (array $record)
    {
        $id = $record['id'];
        $record['children'] = array();
        if (isset($this->_lostChildren[$id])) {
            $children = $this->_lostChildren[$id];
            foreach ($children as $child) {
                $record['children'][] = $this->_appendChild($child);
            }
        }
        unset($record['parentId']);
        return $record;
    }

    /**
     * Find child of a node tree
     *
     * @param $parentId id
     *            of the parent node
     * @return array children array
     */
    public function readChild ($parentId)
    {
        // get the UI parameters
        $filter = $this->getFilterArray();
        $sort = $this->getSortArray();
        $includedFields = $this->getFieldList();
        $excludedFields = $this->getExcludeFieldList();
        
        // merge the two fields array to obtain only one array with all the
        // conditions
        if (! empty($includedFields) && ! empty($excludedFields)) {
            $fieldRule = $includedFields;
        } else {
            $fieldRule = array_merge($includedFields, $excludedFields);
        }
        
        // get the cursor
        if (empty($filter)) {
            $cursor = $this->_collection->find(array(
                'parentId' => $parentId
            ), $fieldRule);
        } else {
            $cursor = $this->_collection->find(array(
                'parentId' => $parentId,
                '$and' => array(
                    $filter
                )
            ), $fieldRule);
        }
        
        // apply sort, paging, filter
        $cursor->sort($sort);
        
        // switch from cursor to actual array
        $data = iterator_to_array($cursor);
        
        // iterate throught data to convert ID to string and add version nulmber
        // if none
        foreach ($data as &$value) {
            $value['id'] = (string) $value['_id'];
            unset($value['_id']);
            if (! isset($value['version'])) {
                $value['version'] = 1;
            }
        }
        
        // return data as simple array with no keys
        $response = array_values($data);
        
        return $response;
    }

    /**
     * Do a findone request on the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::findOne()
     * @param array $value
     *            search condition
     * @return array
     */
    public function findOne ($value)
    {
        // get the UI parameters
        $includedFields = $this->getFieldList();
        $excludedFields = $this->getExcludeFieldList();
        
        // merge the two fields array to obtain only one array with all the
        // conditions
        if (! empty($includedFields) && ! empty($excludedFields)) {
            $fieldRule = $includedFields;
        } else {
            $fieldRule = array_merge($includedFields, $excludedFields);
        }
        
        $value = array_merge($value, $this->getFilterArray());
        
        $data = $this->_collection->findOne($value, $fieldRule);
        if ($data === null) {
            return null;
        }
        $data['id'] = (string) $data['_id'];
        unset($data['_id']);
        
        return $data;
    }

    /**
     * Find an item given by its literral ID
     *
     * @param string $contentId            
     * @return array
     */
    public function findById ($contentId)
    {
        return $this->findOne(array(
            '_id' => $this->getId($contentId)
        ));
    }

    /**
     * Find an item given by its name (find only one if many)
     *
     * @param string $name            
     * @return array
     */
    public function findByName ($name)
    {
        return $this->findOne(array(
            'text' => $name
        ));
    }

    /**
     * Create an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::create
     * @param array $obj
     *            data object
     * @param bool $options
     *            should we wait for a server response
     * @return array
     */
    public function create (array $obj, $options = array('safe'=>true))
    {
        $obj['version'] = 1;
        
        $currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
        $currentUser = $currentUserService->getCurrentUserSummary();
        $obj['lastUpdateUser'] = $currentUser;
        $obj['createUser'] = $currentUser;
        
        $currentTimeService = \Rubedo\Services\Manager::getService('CurrentTime');
        $currentTime = $currentTimeService->getCurrentTime();
        
        $obj['createTime'] = $currentTime;
        $obj['lastUpdateTime'] = $currentTime;
        
        $resultArray = $this->_collection->insert($obj, $options);
        
        if ($resultArray['ok'] == 1) {
            $obj['id'] = (string) $obj['_id'];
            unset($obj['_id']);
            $returnArray = array(
                'success' => true,
                "data" => $obj
            );
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => $resultArray["err"]
            );
        }
        
        return $returnArray;
    }

    /**
     * Update an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::update
     * @param array $obj
     *            data object
     * @param bool $options
     *            should we wait for a server response
     * @return array
     */
    public function update (array $obj, $options = array('safe'=>true))
    {
        $id = $obj['id'];
        unset($obj['id']);
        if (! isset($obj['version'])) {
            throw new \Rubedo\Exceptions\DataAccess('can\'t update an object without a version number.');
        }
        
        $oldVersion = $obj['version'];
        $obj['version'] = $obj['version'] + 1;
        
        $currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
        $currentUser = $currentUserService->getCurrentUserSummary();
        $obj['lastUpdateUser'] = $currentUser;
        
        $currentTimeService = \Rubedo\Services\Manager::getService('CurrentTime');
        $currentTime = $currentTimeService->getCurrentTime();
        $obj['lastUpdateTime'] = $currentTime;
        
        $mongoID = $this->getId($id);
        
        $updateArray = array();
        foreach ($obj as $key => $value) {
            if (in_array($key, array(
                'createUser',
                'createTime'
            ))) {
                unset($obj[$key]);
            }
        }
        
        $updateCondition = array(
            '_id' => $mongoID,
            'version' => $oldVersion
        );
        
        if (is_array($this->_filterArray)) {
            $updateCondition = array_merge($this->_filterArray, $updateCondition);
        }
        
        $resultArray = $this->_collection->update($updateCondition, array(
            '$set' => $obj
        ), array(
            "safe" => $options
        ));
        
        $obj = $this->findById($mongoID);
        
        if ($resultArray['ok'] == 1) {
            if ($resultArray['updatedExisting'] == true) {
                $obj['id'] = $id;
                unset($obj['_id']);
                
                $returnArray = array(
                    'success' => true,
                    "data" => $obj
                );
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'no record had been updated'
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => $resultArray["err"]
            );
        }
        
        return $returnArray;
    }

    /**
     * Delete objets in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::destroy
     * @param array $obj
     *            data object
     * @param bool $options
     *            should we wait for a server response
     * @return array
     */
    public function destroy (array $obj, $options = array('safe'=>true))
    {
        $id = $obj['id'];
        if (! isset($obj['version'])) {
            throw new \Rubedo\Exceptions\DataAccess('can\'t destroy an object without a version number.');
        }
        $version = $obj['version'];
        $mongoID = $this->getId($id);
        
        $updateCondition = array(
            '_id' => $mongoID,
            'version' => $version
        );
        
        if (is_array($this->_filterArray)) {
            $updateCondition = array_merge($this->_filterArray, $updateCondition);
        }
        
        $resultArray = $this->_collection->remove($updateCondition, $options);
        if ($resultArray['ok'] == 1) {
            if ($resultArray['n'] == 1) {
                $returnArray = array(
                    'success' => true
                );
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'no record had been deleted'
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => $resultArray["err"]
            );
        }
        
        return $returnArray;
    }

    /**
     * Delete the childrens of the parent given in parameter
     *
     * @param $data contain
     *            the datas of the parent in database
     * @return array with the result of the operation
     */
    public function deleteChild ($data)
    {
        $parentId = $data['id'];
        $error = false;
        
        // Get the childrens of the current parent
        $childrensArray = $this->readChild($parentId);
        
        if (! is_array($childrensArray)) {
            throw new \Rubedo\Exceptions\DataAccess('Should be an array');
        }
        
        // Delete all the childrens
        foreach ($childrensArray as $key => $value) {
            $result = $this->_deleteChild($value);
            if ($result['success'] == false) {
                $error = true;
            }
        }
        
        // Delete the parent
        if ($error == false) {
            $returnArray = $this->destroy($data, true);
        } else {
            $returnArray = array(
                'success' => false,
                'msg' => 'An error occured during the deletion'
            );
        }
        
        return $returnArray;
    }

    /**
     * Drop The current Collection
     *
     * @deprecated
     *
     *
     *
     *
     */
    public function drop ()
    {
        return $this->_collection->drop();
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Interfaces\Mongo\IDataAccess::count()
     */
    public function count ()
    {
        $filter = $this->getFilterArray();
        return $this->_collection->count($filter);
    }

    /**
     * Add a filter condition to the service
     *
     * Filter should be
     * array('field'=>'value')
     * or
     * array('field'=>array('operator'=>value))
     *
     * @param array $filter
     *            Native Mongo syntax filter array
     */
    public function addFilter (array $filter)
    {
        // check valid input
        if (count($filter) !== 1) {
            throw new \Rubedo\Exceptions\DataAccess("Invalid filter array", 1);
        }
        
        foreach ($filter as $name => $value) {
            if (! in_array(gettype($value), array(
                'array',
                'string',
                'float',
                'integer',
                'boolean'
            ))) {
                throw new \Rubedo\Exceptions\DataAccess("Invalid filter array", 1);
            }
            if (is_array($value) && count($value) !== 1) {
                throw new \Rubedo\Exceptions\DataAccess("Invalid filter array", 1);
            }
            if (is_array($value)) {
                foreach ($value as $operator => $subvalue) {
                    if (! in_array(gettype($subvalue), array(
                        'array',
                        'string',
                        'float',
                        'integer'
                    )) && ! $subvalue instanceof \MongoRegex) {
                        throw new \Rubedo\Exceptions\DataAccess("Invalid filter array", 1);
                    }
                }
            }
            if ($name === 'id') {
                $name = '_id';
                if (is_string($value)) {
                    $value = $this->getId($value);
                } elseif (is_array($value)) {
                    if (isset($value['$in'])) {
                        foreach ($value['$in'] as $key => $localId) {
                            $value['$in'][$key] = $this->getId($localId);
                        }
                    }
                    if (isset($value['$nin'])) {
                        foreach ($value['$nin'] as $key => $localId) {
                            $value['$nin'][$key] = $this->getId($localId);
                        }
                    }
                    if (isset($value['$all'])) {
                        foreach ($value['$all'] as $key => $localId) {
                            $value['$all'][$key] = $this->getId($localId);
                        }
                    }
                }
            }
            // add validated input
            $this->_filterArray[$name] = $value;
        }
    }

    /**
     * Add a OR filter condition to the service
     *
     * Filter should be an array of array('field'=>'value')
     *
     * @param array $filter
     *            Native Mongo syntax filter array
     */
    public function addOrFilter (array $condArray)
    {
        if (! isset($this->_filterArray['$or'])) {
            $this->_filterArray['$or'] = array();
        }
        
        $this->_filterArray['$or'] = array_merge($this->_filterArray['$or'], $condArray);
    }

    /**
     * Unset all filter condition to the service
     */
    public function clearFilter ()
    {
        $this->_filterArray = array();
    }

    /**
     * Return the current array of conditions.
     *
     * @return array
     */
    public function getFilterArray ()
    {
        return $this->_filterArray;
    }

    /**
     * Add a sort condition to the service
     *
     * Sort should be
     * array('field'=>'value')
     * or
     * array('field'=>array('operator'=>value))
     *
     * @param array $sort
     *            Native Mongo syntax sort array
     */
    public function addSort (array $sort)
    {
        // check valid input
        if (count($sort) !== 1) {
            throw new \Rubedo\Exceptions\DataAccess("Invalid sort array", 1);
        }
        
        foreach ($sort as $name => $value) {
            if (! in_array(gettype($value), array(
                'array',
                'string',
                'float',
                'integer'
            ))) {
                throw new \Rubedo\Exceptions\DataAccess("Invalid sort array", 1);
            }
            if (is_array($value) && count($value) !== 1) {
                throw new \Rubedo\Exceptions\DataAccess("Invalid sort array", 1);
            }
            if (is_array($value)) {
                foreach ($value as $operator => $subvalue) {
                    if (! in_array(gettype($subvalue), array(
                        'string',
                        'float',
                        'integer'
                    ))) {
                        throw new \Rubedo\Exceptions\DataAccess("Invalid sort array", 1);
                    }
                }
            }
            
            if ($value === 'asc') {
                $value = 1;
            } else 
                if ($value === 'desc') {
                    $value = - 1;
                }
            // id isn't a mongo data, _id is
            if ($name === 'id') {
                $name = '_id';
            }
            
            // add validated input
            $this->_sortArray[$name] = $value;
        }
    }

    /**
     * Unset all sort condition to the service
     */
    public function clearSort ()
    {
        $this->_sortArray = array();
    }

    /**
     * Return the current array of conditions.
     *
     * @return array
     */
    public function getSortArray ()
    {
        return $this->_sortArray;
    }

    /**
     * Set the number of the first result displayed
     *
     * @param $firstResult is
     *            the number of the first result displayed
     */
    public function setFirstResult ($firstResult)
    {
        if (gettype($firstResult) !== 'integer') {
            throw new \Rubedo\Exceptions\DataAccess("firstResult should be an integer", 1);
        }
        
        $this->_firstResult = $firstResult;
    }

    /**
     * Set the number of results displayed
     *
     * @param $numberOfResults is
     *            the number of results displayed
     */
    public function setNumberOfResults ($numberOfResults)
    {
        if (gettype($numberOfResults) !== 'integer') {
            throw new \Rubedo\Exceptions\DataAccess("numberOfResults should be an integer", 1);
        }
        
        $this->_numberOfResults = $numberOfResults;
    }

    /**
     * Set to zero the number of the first result displayed
     */
    public function clearFirstResult ()
    {
        $this->_firstResult = 0;
    }

    /**
     * Set to zero (unlimited) the number of results displayed
     */
    public function clearNumberOfResults ()
    {
        $this->_numberOfResults = 0;
    }

    /**
     * Return the current number of the first result displayed
     *
     * @return integer
     */
    public function getFirstResult ()
    {
        return $this->_firstResult;
    }

    /**
     * Return the current number of results displayed
     *
     * @return integer
     */
    public function getNumberOfResults ()
    {
        return $this->_numberOfResults;
    }

    /**
     * Add to the field list the array passed in argument
     *
     * @param array $fieldList            
     */
    public function addToFieldList (array $fieldList)
    {
        if (count($fieldList) === 0) {
            throw new \Rubedo\Exceptions\DataAccess("Invalid field list array", 1);
        }
        
        foreach ($fieldList as $value) {
            if (! is_string($value)) {
                throw new \Rubedo\Exceptions\DataAccess("This type of data in not allowed", 1);
            }
            if ($value === "id") {
                throw new \Rubedo\Exceptions\DataAccess("id field is not authorized", 1);
            }
            
            // add validated input
            $this->_fieldList[$value] = true;
        }
    }

    /**
     * Give the fields into the fieldList array
     *
     * @return array
     */
    public function getFieldList ()
    {
        return $this->_fieldList;
    }

    /**
     * Allow to remove one field in the current array
     *
     * @param array $fieldToRemove            
     */
    public function removeFromFieldList (array $fieldToRemove)
    {
        foreach ($fieldToRemove as $value) {
            if (! is_string($value)) {
                throw new \Rubedo\Exceptions\DataAccess("RemoveFromFieldList only accept string parameter", 1);
            }
            unset($this->_fieldList[$value]);
        }
    }

    /**
     * Clear the fieldList array
     */
    public function clearFieldList ()
    {
        $this->_fieldList = array();
    }

    /**
     * Add to the exclude field list the array passed in argument
     *
     * @param array $excludeFieldList            
     */
    public function addToExcludeFieldList (array $excludeFieldList)
    {
        if (count($excludeFieldList) === 0) {
            throw new \Rubedo\Exceptions\DataAccess("Invalid excluded fields list array", 1);
        }
        
        foreach ($excludeFieldList as $value) {
            if (! in_array(gettype($value), array(
                'string'
            ))) {
                throw new \Rubedo\Exceptions\DataAccess("This type of data in not allowed", 1);
            }
            if ($value === "id") {
                throw new \Rubedo\Exceptions\DataAccess("id field is not authorized", 1);
            }
            
            // add validated input
            $this->_excludeFieldList[$value] = false;
        }
    }

    /**
     * Give the fields into the excludeFieldList array
     */
    public function getExcludeFieldList ()
    {
        return $this->_excludeFieldList;
    }

    /**
     * Allow to remove one field in the current excludeFieldList array
     *
     * @param array $fieldToRemove            
     */
    public function removeFromExcludeFieldList (array $fieldToRemove)
    {
        foreach ($fieldToRemove as $value) {
            if (! is_string($value)) {
                throw new \Rubedo\Exceptions\DataAccess("RemoveFromFieldList only accept string paramter", 1);
            }
            unset($this->_excludeFieldList[$value]);
        }
    }

    /**
     * Clear the excludeFieldList array
     */
    public function clearExcludeFieldList ()
    {
        $this->_excludeFieldList = array();
    }

    public function getRegex ($expr)
    {
        return new \MongoRegex($expr);
    }

    public function getId ($idString = null)
    {
        return new \MongoId($idString);
    }

    public function getMongoDate ()
    {
        return new \MongoDate();
    }

    /**
     * Update an objet in the current collection
     *
     * Shouldn't be used if doing a simple update action
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
        $resultArray = $this->_collection->update($updateCond, $data, $options);
        if ($resultArray['ok'] == 1) {
            
            $returnArray = array(
                'success' => true
            );
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => $resultArray["err"]
            );
        }
        
        return $returnArray;
    }

    public function customFind ($filter = array(), $fieldRule = array())
    {
        // get the cursor
        $cursor = $this->_collection->find($filter, $fieldRule);
        return $cursor;
    }

    public function customDelete ($deleteCond, $options = array('safe'=>true))
    {
        return $this->_collection->remove($deleteCond, $options);
    }

    /**
     * Add index to collection
     *
     * @param string|arrau $keys            
     * @param array $options            
     */
    public function ensureIndex ($keys, $options = array())
    {
        $options['safe'] = true;
        $this->_collection->ensureIndex($keys, $options);
    }
}
