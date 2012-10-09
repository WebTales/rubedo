<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
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
    private static $_defaultMongo;

    /**
     * Default value of the database name
     *
     * Used by the constructor if no specific params
     *
     * @var string
     */
    private static $_defaultDb;

    /**
     * MongoDB Connection
     *
     * @var \Mongo
     */
    private $_adapter;

    /**
     * Object which represent the mongoDB Collection
     *
     * @var \MongoCollection
     */
    private $_collection;

    /**
     * Object which represent the mongoDB database
     *
     * @var \MongoDB
     */
    private $_dbName;

    /**
     * Getter of the DB connection string
     * @return string DB connection String
     */
    public static function getDefaultMongo()
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
     * @param string $collection name of the DB
     * @param string $dbName name of the DB
     * @param string $mongo connection string to the DB server
     */
    public function init($collection, $dbName = null, $mongo = null)
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
        $this->_adapter = new \Mongo($mongo);
        $this->_dbName = $this->_adapter->$dbName;
        $this->_collection = $this->_dbName->$collection;

    }

    /**
     * Set the main MongoDB connection string
     *
     * @param string $mongo
     * @throws \Exception
     */
    public static function setDefaultMongo($mongo)
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
    public static function setDefaultDb($dbName)
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
    public function read()
    {
        $data = iterator_to_array($this->_collection->find());
        foreach ($data as &$value) {
            $value['id'] = (string)$value['_id'];
            unset($value['_id']);
            if(!isset($value['version'])){
                $value['version'] = 1;
            }

        }

        $response = array_values($data);

        return $response;
    }


	/**
     * Do a find request on the current collection and return content as tree
     *
     * @see \Rubedo\Interfaces\IDataAccess::readTree()
     * @return array
     */
	public function readTree(){
		$dataStore = $this->read();
		
		$this->_lostChildren = array();
		$rootAlreadyFound = false;

		foreach ($dataStore as $record) {
			$id = $record['id'];
			if(isset($record['parentId']) && $record['parentId']!='root'){
				$parentId = $record['parentId'];
				$this->_lostChildren[$parentId][$id] = $record; 
			}else{
				$rootRecord = $record;
				if($rootAlreadyFound){
					throw new \Rubedo\Exceptions\DataAccess('More than one root node found');
				}else{
					$rootAlreadyFound = true;
				}
			}
		}

		if(isset($rootRecord)){
			$result = $this->_appendChild($rootRecord);
		}else{
			$result = array();
		}
		
		return $result;
	}
	
	/**
	 * recursive function to rebuild tree from flat data store
	 * @param array $record root record of the tree
	 * @return array complete tree array
	 */
	protected function _appendChild(array $record){
		$id = $record['id'];
		$record['children']=array();
		if(isset($this->_lostChildren[$id])){
			$children = $this->_lostChildren[$id];
			foreach($children as $child){
				$record['children'][] = $this->_appendChild($child);
			}
		}
		unset($record['parentId']);
		return $record;
	}
	
	/**
	 * Find child of a node tree
	 * @param $parentId id of the parent node
	 * @return array children array
	 */
	 public function readChild($parentId){
	 	
	 	$data = iterator_to_array($this->_collection->find(array('parentId'=>$parentId)));
        foreach ($data as &$value) {
            $value['id'] = (string)$value['_id'];
            unset($value['_id']);
            if(!isset($value['version'])){
                $value['version'] = 1;
            }

        }

        $response = array_values($data);

        return $response;
	 }

    /**
     * Do a findone request on the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::findOne()
     * @return array
     */
    public function findOne()
    {
        return $this->_collection->findOne();
    }

    /**
     * Create an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::create
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function create(array $obj, $safe = true)
    {
    	$currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
    	$currentUser = $currentUserService->getCurrentUserSummary();
		
		unset($obj['leaf']);
		
        $obj['version'] = 1;
		$obj['lastUpdateUser'] = $currentUser;
		$obj['createUser'] = $currentUser;
		
		
		$currentTimeService = \Rubedo\Services\Manager::getService('CurrentTime');
    	$currentTime = $currentTimeService->getCurrentTime();
		
		$obj['createDate'] = $currentTime;
		$obj['lastUpdateDate'] = $currentTime;
		
        $resultArray = $this->_collection->insert($obj, array("safe" => $safe));
        if ($resultArray['ok'] == 1) {
            $obj['id'] = (string)$obj['_id'];
            unset($obj['_id']);
            $returnArray = array('success' => true, "data" => $obj);
        } else {
            $returnArray = array('success' => false, "msg" => $resultArray["err"]);
        }

        return $returnArray;
    }

    /**
     * Update an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::update
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function update(array $obj, $safe = true)
    {
    	$currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
    	$currentUser = $currentUserService->getCurrentUserSummary();
		
		$currentTimeService = \Rubedo\Services\Manager::getService('CurrentTime');
    	$currentTime = $currentTimeService->getCurrentTime();
		
        $id = $obj['id'];
        unset($obj['id']);
		unset($obj['leaf']);
        if (!isset($obj['version'])) {
            throw new \Rubedo\Exceptions\DataAccess('can\'t update an object without a version number.');
        }
        $oldVersion = $obj['version'];
        $obj['version'] = $obj['version'] + 1;
        $obj['lastUpdateUser'] = $currentUser;
		
		$obj['lastUpdateDate'] = $currentTime;
		
        $mongoID = new \MongoID($id);
        $resultArray = $this->_collection->update(array('_id' => $mongoID, 'version' => $oldVersion), $obj, array("safe" => $safe));

        if ($resultArray['ok'] == 1) {
            if ($resultArray['updatedExisting'] == true) {
                $obj['id'] = $id;
                unset($obj['_id']);
                $returnArray = array('success' => true, "data" => $obj);
            } else {
                $returnArray = array('success' => false, "msg" => 'no record had been updated');
            }

        } else {
            $returnArray = array('success' => false, "msg" => $resultArray["err"]);
        }

        return $returnArray;
    }

    /**
     * Delete objets in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::destroy
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function destroy(array $obj, $safe = true)
    {
        $id = $obj['id'];
        if (!isset($obj['version'])) {
            throw new \Rubedo\Exceptions\DataAccess('can\'t destroy an object without a version number.');
        }
        $version = $obj['version'];
        $mongoID = new \MongoID($id);
        $resultArray = $this->_collection->remove(array('_id' => $mongoID, 'version' => $version), array("safe" => $safe));
        if ($resultArray['ok'] == 1) {
            if ($resultArray['n'] == 1) {
                $returnArray = array('success' => true);
            } else {
                $returnArray = array('success' => false, "msg" => 'no record had been deleted');
            }

        } else {
            $returnArray = array('success' => false, "msg" => $resultArray["err"]);
        }
        return $returnArray;
    }

    /**
     * Drop The current Collection
     */
    public function drop()
    {
        return $this->_collection->drop();
    }

}
