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
     * Db Driver ClassName
     * @var string
     */
    private $_dbDriverClassName = '\\Mongo';

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
     * Setter of the dependancy for the Db Driver Objec
     * @param string $className Name of the DB Driver
     */
    public function setdbDriverClassName($className)
    {
        $this -> _dbDriverClassName = $className;
    }

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
        $this -> _adapter = new $this -> _dbDriverClassName($mongo);
        $this -> _dbName = $this -> _adapter -> $dbName;
        $this -> _collection = $this -> _dbName -> $collection;

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
        return iterator_to_array($this -> _collection -> find());
    }

    /**
     * Do a findone request on the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::findOne()
     * @return array
     */
    public function findOne()
    {
        return $this -> _collection -> findOne();
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
        $returnArray = $this -> _collection -> insert($obj, array("safe" => $safe));
        if (isset($obj['_id'])) {
            $returnArray['insertedId'] = $obj['_id'];
        }
        return $returnArray;
    }

    /**
     * Update an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::update
     * @param array $criteria Update condition criteria
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function update(array $criteria, array $obj, $safe = true)
    {
        return $this -> _collection -> update($criteria, $obj, array("safe" => $safe));
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
        return $this -> _collection -> remove($obj, array("safe" => $safe));
    }

    /**
     * Drop The current Collection
     */
    public function drop()
    {
        return $this -> _collection -> drop();
    }

}
