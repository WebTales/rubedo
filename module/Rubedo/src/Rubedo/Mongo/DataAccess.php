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
namespace Rubedo\Mongo;

use Rubedo\Interfaces\Mongo\IDataAccess, WebTales\MongoFilters\Filter;
use Zend\Debug\Debug;
use Rubedo\Services\Manager;
use Rubedo\Services\Events;

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
     * @var \WebTales\MongoFilters\CompositeFilter
     */
    protected $_filters;

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
    
    protected static $requestArray = array();

    /**
     * Getter of the DB connection string
     *
     * @return string DB connection String
     */
    public static function getDefaultMongo ()
    {
        if(!isset(static::$_defaultDb)){
            static::lazyLoadConfig();
        }
        return static::$_defaultMongo;
    }
    
    

    /**
     * temp data for tree view
     *
     * @var array
     */
    protected $_lostChildren = array();

    /**
     * @return the $_defaultDb
     */
    public static function getDefaultDb()
    {
        if(!isset(static::$_defaultDb)){
            static::lazyLoadConfig();
        }
        return DataAccess::$_defaultDb;
    }

	/**
     * init the filter with a global "and" filter
     */
    public function __construct ()
    {
        if(!isset(static::$_defaultDb)){
            static::lazyLoadConfig();
        }
        $this->_filters = Filter::factory();
    }

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
            throw new \Rubedo\Exceptions\Server('$mongo should be a string');
        }
        if (gettype($dbName) !== 'string') {
            throw new \Rubedo\Exceptions\Server('$db should be a string');
        }
        if (gettype($collection) !== 'string') {
            throw new \Rubedo\Exceptions\Server('$collection should be a string');
        }
        $this->_collection = $this->_getCollection($collection, $dbName, $mongo);
    }

    /**
     * Return the mongoDB server version
     *
     * @return string
     */
    public function getMongoServerVersion ()
    {
        $this->init('version');
        $dbInfo = $this->_dbName->command(array(
            'buildinfo' => true
        ));
        if (isset($dbInfo['version'])) {
            return $dbInfo['version'];
        }
        return null;
    }

    /**
     * Getter of Mongo adapter : should only connect once for each mongoDB
     * server
     *
     * @param string $mongo
     *            mongoDB connection string
     * @return \Mongo
     */
    public function getAdapter ($mongo)
    {
        if (isset(self::$_adapterArray[$mongo]) && self::$_adapterArray[$mongo] instanceof \MongoClient) {
            return self::$_adapterArray[$mongo];
        } else {
            $adapter = new \MongoClient($mongo);
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
            $this->_adapter = $this->getAdapter($mongo . '/' . $dbName);
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
            $collectionObj = new ProxyCollection($this->_dbName->$collection);
            self::$_collectionArray[$mongo . '_' . $dbName . '_' . $collection] = $collection;
            return $collectionObj;
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
            throw new \Rubedo\Exceptions\Server('$mongo should be a string', "Exception40", '$mongo');
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
            throw new \Rubedo\Exceptions\Server('$dbName should be a string', "Exception40", '$dbName');
        }
        self::$_defaultDb = $dbName;
    }

    /**
     * Do a find request on the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::read()
     * @return array
     */
    public function read (\WebTales\MongoFilters\IFilter $filters = null)
    {
        // get the UI parameters
        $localFilter = clone $this->getFilters();
        
        // add Read Filters
        if ($filters) {
            $localFilter->addFilter($filters);
        }
        
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
        $cursor = $this->_collection->find($localFilter->toArray(), $fieldRule);
        $nbItems = $cursor->count();
        
        // apply sort, paging, filter
        $cursor->sort($sort);
        $cursor->skip($firstResult);
        $cursor->limit($numberOfResults);
        
        // switch from cursor to actual array
        if ($cursor->count() > 0) {
            $data = iterator_to_array($cursor);
        } else {
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
        
        // Get the children of the current parent
        $childrenArray = $this->readChild($parent['id']);
        
        // Delete all the children
        if (! is_array($childrenArray)) {
            throw new \Rubedo\Exceptions\Server('$childrenArray should be an array', "Exception69", '$childrenArray');
        }
        
        foreach ($childrenArray as $value) {
            self::_deleteChild($value);
        }
        
        // Delete the parent
        $returnArray = $this->destroy($parent);
        
        return $returnArray;
    }

    /**
     * Do a find request on the current collection and return content as tree
     *
     * @see \Rubedo\Interfaces\IDataAccess::readTree()
     * @param \WebTales\MongoFilters\IFilter $filters            
     * @return array
     */
    public function readTree (\WebTales\MongoFilters\IFilter $filters = null)
    {
        $read = $this->read($filters);
        
        $dataStore = $read['data'];
        $dataStore[] = array(
            'parentId' => 'none',
            'id' => 'root'
        );
        
        $this->_lostChildren = array();
        $rootAlreadyFound = false;
        
        foreach ($dataStore as $record) {
            $id = $record['id'];
            if (isset($record['parentId']) && $record['parentId'] != 'none') {
                $parentId = $record['parentId'];
                $this->_lostChildren[$parentId][$id] = $record;
            } else {
                $rootRecord = $record;
                if ($rootAlreadyFound) {
                    throw new \Rubedo\Exceptions\Server('More than one root node found', "Exception68");
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
     * @param \WebTales\MongoFilters\IFilter $filters            
     * @return array children array
     */
    public function readChild ($parentId,\WebTales\MongoFilters\IFilter $filters = null)
    {
        // get the UI parameters
        $localFilter = clone $this->getFilters();
        if ($filters) {
            $localFilter->addFilter($filters);
        }
        
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
        
        $parentFilter = Filter::factory('Value');
        $parentFilter->setName('parentId')->setValue($parentId);
        $localFilter->addFilter($parentFilter);
        
        // get the cursor
        $cursor = $this->_collection->find($localFilter->toArray(), $fieldRule);
        
        // apply sort, paging, filter
        $cursor->sort($sort);
        
        //limit to 50 in BO for rendering until Extjs 4.2 (@todo remove when updated)
        if(!\Rubedo\Collection\AbstractCollection::getIsFrontEnd()){
            $cursor->limit(50);
        }
        
        
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
     * @param \WebTales\MongoFilters\IFilter $localFilter
     *            search condition
     * @return array
     */
    public function findOne (\WebTales\MongoFilters\IFilter $localFilter = null)
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
        $filters = clone $this->getFilters();
        if ($localFilter) {
            $filters->addFilter($localFilter);
        }
        
        $data = $this->_collection->findOne($filters->toArray(), $fieldRule);
        if ($data == null) {
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
        $filter = Filter::factory('Uid');
        $filter->setValue($contentId);
        return $this->findOne($filter);
    }

    /**
     * Find an item given by its name (find only one if many)
     *
     * @param string $name            
     * @return array
     */
    public function findByName ($name)
    {
        $filter = Filter::factory('Value');
        $filter->setValue($name)->setName('text');
        return $this->findOne($filter);
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
    public function create (array $obj, $options = array())
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
        
        try {
            if (isset($options['upsert']) && $options['upsert'] instanceof \WebTales\MongoFilters\IFilter) {
                $filter = $options['upsert'];
                $options['upsert'] = true;
                $resultArray = $this->_collection->update($filter->toArray(), $obj, $options);
            } else {
                $resultArray = $this->_collection->insert($obj, $options);
            }
        } catch (\MongoCursorException $exception) {
            if (strpos($exception->getMessage(), 'duplicate key error')) {
                throw new \Rubedo\Exceptions\User('Duplicate key error', "Exception76");
            } else {
                throw $exception;
            }
        }
        
        if ($resultArray['ok'] == 1) {
            if (isset($resultArray['updatedExisting'])) {
                $obj = $this->findOne($filter);
            } else {
                $obj['id'] = (string) $obj['_id'];
            }
            
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
    public function update (array $obj, $options = array())
    {
        $id = $obj['id'];
        unset($obj['id']);
        if (! isset($obj['version'])) {
            throw new \Rubedo\Exceptions\Access('You can not update an object without a version number.', "Exception78");
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
        
        foreach ($obj as $key => $value) {
            unset($value);
            if (in_array($key, array(
                'createUser',
                'createTime'
            ))) {
                unset($obj[$key]);
            }
        }
        
        $updateConditionId = Filter::factory('Uid');
        $updateConditionId->setValue($mongoID);
        
        $updateConditionVersion = Filter::factory('Value');
        $updateConditionVersion->setValue($oldVersion)->setName('version');
        
        $updateCondition = clone $this->getFilters();
        $updateCondition->addFilter($updateConditionId);
        $updateCondition->addFilter($updateConditionVersion);
        
        try {
            $resultArray = $this->_collection->update($updateCondition->toArray(), array(
                '$set' => $obj
            ), $options);
        } catch (\MongoCursorException $exception) {
            if (strpos($exception->getMessage(), 'duplicate key error')) {
                throw new \Rubedo\Exceptions\User('Duplicate key error', "Exception76");
            } else {
                throw $exception;
            }
        }
        
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
                    "msg" => 'Le contenu a été modifié, veuiller recharger celui-ci avant de faire cette mise à jour.'
                );
            }
        } elseif ($resultArray) {
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
     * Delete objets in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::destroy
     * @param array $obj
     *            data object
     * @param bool $options
     *            should we wait for a server response
     * @return array
     */
    public function destroy (array $obj, $options = array())
    {
        $id = $obj['id'];
        if (! isset($obj['version'])) {
            throw new \Rubedo\Exceptions\Access('You can not destroy an object without a version number.', "Exception79");
        }
        $version = $obj['version'];
        $mongoID = $this->getId($id);
        
        $updateCondition = array(
            '_id' => $mongoID,
            'version' => $version
        );
        
        $updateConditionId = Filter::factory('Uid');
        $updateConditionId->setValue($mongoID);
        
        $updateConditionVersion = Filter::factory('Value');
        $updateConditionVersion->setValue($version)->setName('version');
        
        $updateCondition = clone $this->getFilters();
        $updateCondition->addFilter($updateConditionId);
        $updateCondition->addFilter($updateConditionVersion);
        
        $resultArray = $this->_collection->remove($updateCondition->toArray(), $options);
        if ($resultArray['ok'] == 1) {
            if ($resultArray['n'] == 1) {
                $returnArray = array(
                    'success' => true
                );
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'Impossible de supprimer le contenu'
                );
            }
        } elseif ($resultArray) {
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

    /**
     * Delete the children of the parent given in parameter
     *
     * @param $data contain
     *            the datas of the parent in database
     * @return array with the result of the operation
     */
    public function deleteChild ($data)
    {
        $parentId = $data['id'];
        $error = false;
        
        // Get the children of the current parent
        $childrenArray = $this->readChild($parentId);
        
        if (! is_array($childrenArray)) {
            throw new \Rubedo\Exceptions\Server('$childrenArray should be an array', "Exception69", '$childrenArray');
        }
        
        // Delete all the children
        foreach ($childrenArray as $value) {
            $result = $this->_deleteChild($value);
            if ($result['success'] == false) {
                $error = true;
            }
        }
        
        // Delete the parent
        if ($error == false) {
            $returnArray = $this->destroy($data);
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
     */
    public function drop ()
    {
        return $this->_collection->drop();
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Interfaces\Mongo\IDataAccess::count()
     */
    public function count (\WebTales\MongoFilters\IFilter $filters = null)
    {
        $localFilter = clone $this->getFilters();
        if ($filters) {
            $localFilter->addFilter($filters);
        }
        
        return $this->_collection->count($localFilter->toArray());
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
    public function addFilter (\WebTales\MongoFilters\IFilter $filter)
    {
        $this->_filters->addFilter($filter);
    }

    /**
     * Unset all filter condition to the service
     */
    public function clearFilter ()
    {
        $this->_filters->clearFilters();
    }

    /**
     * Return the current MongoDB conditions.
     *
     * @return \WebTales\MongoFilters\IFilter
     */
    public function getFilters ()
    {
        return $this->_filters;
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
            throw new \Rubedo\Exceptions\Server("Invalid sort array", "Exception83");
        }
        
        foreach ($sort as $name => $value) {
            if (! in_array(gettype($value), array(
                'array',
                'string',
                'float',
                'integer'
            ))) {
                throw new \Rubedo\Exceptions\Server("Invalid sort array", "Exception83");
            }
            if (is_array($value) && count($value) !== 1) {
                throw new \Rubedo\Exceptions\Server("Invalid sort array", "Exception83");
            }
            if (is_array($value)) {
                foreach ($value as $subvalue) {
                    if (! in_array(gettype($subvalue), array(
                        'string',
                        'float',
                        'integer'
                    ))) {
                        throw new \Rubedo\Exceptions\Server("Invalid sort array", "Exception83");
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
            $this->_sortArray[$name] = intval($value);
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
            throw new \Rubedo\Exceptions\Server("firstResult should be an integer", "Exception84", '$firstResult');
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
        $this->_numberOfResults = intval($numberOfResults);
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
            throw new \Rubedo\Exceptions\Server("Invalid field list array", "Exception85");
        }
        
        foreach ($fieldList as $value) {
            if (! is_string($value)) {
                throw new \Rubedo\Exceptions\Server("This type of data in not allowed", "Exception86");
            }
            if ($value === "id") {
                throw new \Rubedo\Exceptions\Server("id field is not authorized", "Exception87");
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
                throw new \Rubedo\Exceptions\Server("RemoveFromFieldList only accept string parameter", "Exception88", "RemoveFromFieldList");
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
            throw new \Rubedo\Exceptions\Server("Invalid excluded fields list array", "Exception89");
        }
        
        foreach ($excludeFieldList as $value) {
            if (! in_array(gettype($value), array(
                'string'
            ))) {
                throw new \Rubedo\Exceptions\Server("This type of data in not allowed", "Exception86");
            }
            if ($value === "id") {
                throw new \Rubedo\Exceptions\Server("id field is not authorized", "Exception87");
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
                throw new \Rubedo\Exceptions\Server("RemoveFromFieldList only accept string paramter", "Exception88", "RemoveFromFieldList");
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
     * @param \WebTales\MongoFilters\IFilter $updateCond
     *            array of condition to determine what should be updated
     * @param array $options            
     * @return array
     */
    public function customUpdate (array $data,\WebTales\MongoFilters\IFilter $updateCond, $options = array())
    {
        try {
            $resultArray = $this->_collection->update($updateCond->toArray(), $data, $options);
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
        } catch (\MongoCursorException $exception) {
            if (strpos($exception->getMessage(), 'duplicate key error')) {
                throw new \Rubedo\Exceptions\User('Duplicate key error', "Exception76");
            } else {
                throw $exception;
            }
        }
    }

    public function customFind (\WebTales\MongoFilters\IFilter $filter = null, $fieldRule = array())
    {
        $filterArray = is_null($filter) ? array() : $filter->toArray();
        // get the cursor
        $cursor = $this->_collection->find($filterArray, $fieldRule);
        return $cursor;
    }

    public function customDelete (\WebTales\MongoFilters\IFilter $deleteCond, $options = array())
    {
        return $this->_collection->remove($deleteCond->toArray(), $options);
    }

    /**
     * Add index to collection
     *
     * @param string|arrau $keys            
     * @param array $options            
     */
    public function ensureIndex ($keys, $options = array())
    {
        $options['w'] = true;
        $result = $this->_collection->ensureIndex($keys, $options);
        return $result;
    }

    public function dropIndexes ()
    {
        $result = $this->_collection->deleteIndexes();
        return $result;
    }

    /**
     * check if the index is set
     *
     * @todo implement check index
     * @param
     *            array
     * @return boolean
     */
    public function checkIndex ($keys)
    {
        return false;
    }
    
    public static function lazyLoadConfig(){
        $config = Manager::getService('config');
        $options = $config['datastream'];
        if (isset($options)) {
            $connectionString = 'mongodb://';
            if (! empty($options['mongo']['login'])) {
                $connectionString .= $options['mongo']['login'];
                $connectionString .= ':' . $options['mongo']['password'] . '@';
            }
            $connectionString .= $options['mongo']['server'];
            if (isset($options['mongo']['port'])) {
                $connectionString .= ':' . $options['mongo']['port'];
            }
            self::setDefaultMongo($connectionString);
        
            self::setDefaultDb($options['mongo']['db']);
        }
        
        Events::getEventManager()->attach(ProxyCollection::POST_REQUEST,array('Rubedo\\Mongo\\DataAccess','log'),1);
        //         Events::getEventManager()->attach(ProxyCollection::POST_REQUEST,array('Rubedo\\Mongo\\DataAccess','alertOnIndex'),10);
        //         Events::getEventManager()->attach(ProxyCollection::POST_REQUEST,array('Rubedo\\Mongo\\DataAccess','checkMultiple'),20);
    }

    public static function log ($e)
    {
        $target = $e->getTarget();
        $mongoFunctionWithQuery = array(
            'find',
            'findOne',
            'update',
            'remove'
        );
        if (in_array($target->function, $mongoFunctionWithQuery)) {
            Manager::getService('Logger')->Info("Request on MongoDB collection: '" . $target->collection->getName() . "'", array(
                'Collection' => $target->collection->getName(),
                'Function' => $target->function,
                'Query' => $target->args[0]
            ));
        }
    }
    
    public static function checkMultiple($e){
        $target = $e->getTarget();
        $args = $target->args;
        $params = isset($args[0])?$args[0]:null;
        $key = $target->collection->getName().'_'.$target->function.'_'.md5(serialize($params));
        if(isset(static::$requestArray[$key])){
            Manager::getService('Logger')->Error("An already sent query has again been called on '".$target->collection->getName()."'",array(
            'Collection' => $target->collection->getName(),
            'Query' => $target->args[0]
            ));
            static::$requestArray[$key]++;
        }else{
            static::$requestArray[$key]=1;
        }
    }

    public static function alertOnIndex ($e)
    {
        $target = $e->getTarget();
        $mongoFunctionWithQuery = array('find','update','remove');
        if (in_array($target->function,$mongoFunctionWithQuery)) {
            $cursor = $target->collection->find($target->args[0]);
        } else {
            $cursor = $e->getTarget()->result;
        }
        
        if ($cursor instanceof \MongoCursor) {
            $explain = $cursor->explain();
            if ($explain['cursor'] == 'BasicCursor') {
                Manager::getService('Logger')->Error("A query on the collection '".$target->collection->getName()."' didn't used an index",array(
                    'Collection' => $target->collection->getName(),
                    'Query' => $target->args[0]
                ));
            }
        }
    }
}
