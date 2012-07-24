<?php
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

    private $_collection;

    private $_db;

    private static $defaultMongo;

    private static $defaultDb;

    /**
     * Initialize a data service handler to read or write in a MongoDb
     * Collection
     *
     * @param string $mongo            
     * @param string $db            
     * @param string $collection            
     */
    public function __construct ($collection, $db = null, $mongo = null)
    {
        if (is_null($mongo)) {
            $mongo = self::$defaultMongo;
        }
        
        if (is_null($db)) {
            $db = self::$defaultDb;
        }
        
        if (gettype($mongo) !== 'string') {
            throw new \Exception('$mongo should be a string');
        }
        if (gettype($db) !== 'string') {
            throw new \Exception('$db should be a string');
        }
        if (gettype($collection) !== 'string') {
            throw new \Exception('$collection should be a string');
        }
        $this->_mongo = new \Mongo($mongo);
        $this->_db = $this->_mongo->$db;
        $this->_collection = $this->_db->$collection;
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
        self::$defaultMongo = $mongo;
    }

    /**
     * Set the main Database name
     *
     * @param unknown_type $db            
     * @throws \Exception
     */
    public static function setDefaultDb ($db)
    {
        if (gettype($db) !== 'string') {
            throw new \Exception('$db should be a string');
        }
        self::$defaultDb = $db;
    }

    /**
     * Do a fin request without filter on the current collection
     *
     * @return \MongoIterator
     */
    public function find ()
    {
        return $this->_collection->find()->limit(10);
    }

    /**
     * Insert an objet in the current collection
     *
     * @param array $obj            
     * @return array
     */
    public function insert (array $obj)
    {
        return $this->_collection->insert($obj, 
                array(
                        "safe" => true
                ));
    }
}