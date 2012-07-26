<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    yet to be written
 * @version    $Id:
 */

namespace Rubedo\Mongo;

use Rubedo\Interfaces\IDataAccess;

/**
 * Class implementing actual access to mongoDb
 *
 * @author jbourdin
 *        
 */
class DataAccess implements IDataAccess
{

    /**
     * MongoDB Connection
     *
     * @var \Mongo
     */
    private $_mongo;

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
     * Initialize a data service handler to read or write in a MongoDb
     * Collection
     *
     * @param string $connection
     *            connection string to the DB server
     * @param string $dbName
     *            name of the DB
     * @param string $collection
     *            name of the DB
     */
    public function __construct ($collection, $dbName = null, $mongo = null)
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
        $this->_mongo = new \Mongo($mongo);
        $this->_dbName = $this->_mongo->$dbName;
        $this->_collection = $this->_dbName->$collection;
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
     * @return \MongoIterator
     */
    public function find (array $query = array(), array $fields = array())
    {
        return $this->_collection->find($query, $fields);
    }

    /**
     * Do a find request on the current collection
     *
     * @return \MongoIterator
     */
    public function findOne (array $query = array(), array $fields = array())
    {
        return $this->_collection->findOne($query, $fields);
    }

    /**
     * Insert an objet in the current collection
     *
     * @param array $obj            
     * @param bool $safe
     *            weither the update should wait for a server response
     * @return array
     */
    public function insert (array $obj, $safe = true)
    {
        return $this->_collection->insert($obj, array("safe" => $safe));
    }
}