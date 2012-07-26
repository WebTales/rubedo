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
 * @version $Id:
 */
namespace Rubedo\Mongo;

use Rubedo\Interfaces\IDataAccess;

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
     * QueryBuilder object used to do the read/write action to MongoDB
     * 
     * @var QueryBuilder
     */
    private $_mongoQueryBuilder;

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
        $this->_mongoQueryBuilder = new QueryBuilder($collection, $dbName, $mongo);
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
     * @param array $query
     *            Request parameters array
     * @param array $fields
     *            Requested fields array
     * @return array
     */
    public function find (array $query = array(), array $fields = array())
    {
        return iterator_to_array($this->_mongoQueryBuilder->find($query, $fields));
    }
    
    /**
     * Do a findone request on the current collection
     *
     * @param array $query
     *            Request parameters array
     * @param array $fields
     *            Requested fields array
     * @return array
     */
    public function findOne (array $query = array(), array $fields = array())
    {
        return $this->_mongoQueryBuilder->findOne($query, $fields);
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
        return $this->_mongoQueryBuilder->insert($obj, array("safe" => $safe));
    }
}