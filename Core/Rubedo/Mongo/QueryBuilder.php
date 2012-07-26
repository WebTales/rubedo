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

/**
 * Class implementing read request abstraction to mongoDb
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class QueryBuilder
{

    const COLUMNS = 'columns';

    const WHERE = 'where';

    const ORDER = 'order';

    const LIMIT_COUNT = 'limitcount';

    const LIMIT_OFFSET = 'limitoffset';

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
     * The initial values for the $_parts array.
     * NOTE: It is important for the 'FOR_UPDATE' part to be last to ensure
     * meximum compatibility with database adapters.
     *
     * @var array
     */
    protected static $_partsInit = array(self::COLUMNS => array(),self::WHERE => array(),self::ORDER => array(),self::LIMIT_COUNT => null,self::LIMIT_OFFSET => null);

    /**
     * The component parts of a SELECT statement.
     * Initialized to the $_partsInit array in the constructor.
     *
     * @var array
     */
    protected $_parts = array();

    /**
     * Bind variables for query
     *
     * @var array
     */
    protected $_bind = array();

    /**
     * Class constructor
     *
     * @param string $collection
     *            name of the DB
     * @param string $dbName
     *            name of the DB
     * @param string $mongo
     *            connection string to the DB server
     */
    public function __construct ($collection, $dbName, $mongo)
    {
        $this->_adapter = new \Mongo($mongo);
        $this->_dbName = $this->_adapter->$dbName;
        $this->_collection = $this->_dbName->$collection;
        
        $this->_parts = self::$_partsInit;
    }

    /**
     *
     * @return the $_adapter
     */
    public function getAdapter ()
    {
        return $this->_adapter;
    }

    /**
     *
     * @return the $_parts
     */
    public function getParts ()
    {
        return $this->_parts;
    }

    /**
     *
     * @param multitype: $_parts            
     */
    public function setParts ($_parts)
    {
        $this->_parts = $_parts;
    }

    /**
     * Get bind variables
     *
     * @return array
     */
    public function getBind ()
    {
        return $this->_bind;
    }

    /**
     * Set bind variables
     *
     * @param mixed $bind            
     * @return Zend_Db_Select
     */
    public function bind ($bind)
    {
        $this->_bind = $bind;
        
        return $this;
    }

    /**
     * Set the FROM collection and optional columns to the query.
     *
     * The first parameter $name is a simple string
     *
     * The second parameter can be a single string or an array of strings.
     *
     * The third parameter can be null or an empty string.
     *
     * @param
     *            string The Collection name .
     * @param
     *            array|string The columns to select from this table.
     * @param string $schema
     *            The schema name to specify, if any.
     * @return QueryBuilder This.
     */
    public function from ($name, $cols = '*', $schema = null)
    {
        if (! is_null($schema)) {
            $this->_adapter->setDbName($schema);
        }
        $this->_adapter->setCollection($name);
        $this->columns($cols);
        return $this;
    }

    /**
     * Specifies the columns used in the FROM clause.
     *
     * The parameter can be a single string or an array of strings.
     *
     * @param array|string $cols
     *            The columns to select from this table.
     * @return QueryBuilder This.
     */
    public function columns ($cols = '*')
    {
        if ($cols === '*') {
            $this->_parts['columns'] = array();
        } else {
            if (! is_array($cols)) {
                throw new \Exception('$cols should be an array');
            }
            foreach ($cols as $column) {
                if (! gettype($column) == 'string') {
                    throw new \Exception('$cols should be an array of strings');
                }
                if (! in_array($column, $this->_parts['columns'])) {
                    $this->_parts['columns'][] = $column;
                }
            }
        }
        
        return $this;
    }

    /**
     * Adds a WHERE condition to the query by AND.
     *
     * If a value is passed as the second param, it will be quoted
     * and replaced into the condition wherever a question-mark
     * appears. Array values are quoted and comma-separated.
     *
     * <code>
     * // simplest but non-secure
     * $select->where("id = $id");
     *
     * // secure (ID is quoted but matched anyway)
     * $select->where('id = ?', $id);
     *
     * // alternatively, with named binding
     * $select->where('id = :id');
     * </code>
     *
     * Note that it is more correct to use named bindings in your
     * queries for values other than strings. When you use named
     * bindings, don't forget to pass the values when actually
     * making a query:
     *
     * <code>
     * $db->fetchAll($select, array('id' => 5));
     * </code>
     *
     * @todo write mongo version
     * @param string $cond
     *            The WHERE condition.
     * @param mixed $value
     *            OPTIONAL The value to quote into the condition.
     * @param int $type
     *            OPTIONAL The type of the given value
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function where ($cond, $value = null, $type = null)
    {
        $this->_parts[self::WHERE][] = $this->_where($cond, $value, $type, true);
        
        return $this;
    }

    /**
     * Adds a WHERE condition to the query by OR.
     *
     * Otherwise identical to where().
     *
     * @todo write mongo version
     * @param string $cond
     *            The WHERE condition.
     * @param mixed $value
     *            OPTIONAL The value to quote into the condition.
     * @param int $type
     *            OPTIONAL The type of the given value
     * @return Zend_Db_Select This Zend_Db_Select object.
     *        
     * @see where()
     */
    public function orWhere ($cond, $value = null, $type = null)
    {
        $this->_parts[self::WHERE][] = $this->_where($cond, $value, $type, false);
        
        return $this;
    }

    /**
     * Adds a row order to the query.
     *
     * @todo write mongo version
     * @param mixed $spec
     *            The column(s) and direction to order by.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function order ($spec)
    {
        if (! is_array($spec)) {
            $spec = array($spec);
        }
        
        // force 'ASC' or 'DESC' on each order spec, default is ASC.
        foreach ($spec as $val) {
            if ($val instanceof Zend_Db_Expr) {
                $expr = $val->__toString();
                if (empty($expr)) {
                    continue;
                }
                $this->_parts[self::ORDER][] = $val;
            } else {
                if (empty($val)) {
                    continue;
                }
                $direction = self::SQL_ASC;
                if (preg_match('/(.*\W)(' . self::SQL_ASC . '|' . self::SQL_DESC . ')\b/si', $val, $matches)) {
                    $val = trim($matches[1]);
                    $direction = $matches[2];
                }
                if (preg_match('/\(.*\)/', $val)) {
                    $val = new Zend_Db_Expr($val);
                }
                $this->_parts[self::ORDER][] = array($val,$direction);
            }
        }
        
        return $this;
    }

    /**
     * Sets a limit count and offset to the query.
     *
     * @todo write mongo version
     * @param int $count
     *            OPTIONAL The number of rows to return.
     * @param int $offset
     *            OPTIONAL Start returning after this many rows.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function limit ($count = null, $offset = null)
    {
        $this->_parts[self::LIMIT_COUNT] = (int) $count;
        $this->_parts[self::LIMIT_OFFSET] = (int) $offset;
        return $this;
    }

    /**
     * Sets the limit and count by page number.
     *
     * @todo write mongo version
     * @param int $page
     *            Limit results to this page number.
     * @param int $rowCount
     *            Use this many rows per page.
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function limitPage ($page, $rowCount)
    {
        $page = ($page > 0) ? $page : 1;
        $rowCount = ($rowCount > 0) ? $rowCount : 1;
        $this->_parts[self::LIMIT_COUNT] = (int) $rowCount;
        $this->_parts[self::LIMIT_OFFSET] = (int) $rowCount * ($page - 1);
        return $this;
    }

    /**
     * Get part of the structured information for the currect query.
     *
     * @todo write mongo version
     * @param string $part            
     * @return mixed
     * @throws Zend_Db_Select_Exception
     */
    public function getPart ($part)
    {
        $part = strtolower($part);
        if (! array_key_exists($part, $this->_parts)) {
            require_once 'Zend/Db/Select/Exception.php';
            throw new Zend_Db_Select_Exception("Invalid Select part '$part'");
        }
        return $this->_parts[$part];
    }

    /**
     * Executes the current select object and returns the result
     *
     * @todo write mongo version
     * @param integer $fetchMode
     *            OPTIONAL
     * @param mixed $bind
     *            An array of data to bind to the placeholders.
     * @return PDO_Statement Zend_Db_Statement
     */
    public function Query ($fetchMode = null, $bind = array())
    {
        if (! empty($bind)) {
            $this->bind($bind);
        }
        
        $stmt = $this->_adapter->query($this);
        if ($fetchMode == null) {
            $fetchMode = $this->_adapter->getFetchMode();
        }
        $stmt->setFetchMode($fetchMode);
        return $stmt;
    }

    /**
     * Clear parts of the Select object, or an individual part.
     *
     * @param string $part
     *            OPTIONAL
     * @return QueryBuilder
     */
    public function reset ($part = null)
    {
        if ($part == null) {
            $this->_parts = self::$_partsInit;
        } else {
            if (array_key_exists($part, self::$_partsInit)) {
                $this->_parts[$part] = self::$_partsInit[$part];
            }
        }
        return $this;
    }

    /**
     * Internal function for creating the where clause
     *
     * @todo write mongo version
     * @param string $condition            
     * @param mixed $value
     *            optional
     * @param string $type
     *            optional
     * @param boolean $bool
     *            true = AND, false = OR
     * @return string clause
     */
    protected function _where ($condition, $value = null, $type = null, $bool = true)
    {
        if (count($this->_parts[self::UNION])) {
            require_once 'Zend/Db/Select/Exception.php';
            throw new Zend_Db_Select_Exception("Invalid use of where clause with " . self::SQL_UNION);
        }
        
        if ($value !== null) {
            $condition = $this->_adapter->quoteInto($condition, $value, $type);
        }
        
        $cond = "";
        if ($this->_parts[self::WHERE]) {
            if ($bool === true) {
                $cond = self::SQL_AND . ' ';
            } else {
                $cond = self::SQL_OR . ' ';
            }
        }
        
        return $cond . "($condition)";
    }

    /**
     * Do a find request on the current collection
     *
     * @param array $query
     *            Request parameters array
     * @param array $fields
     *            Requested fields array
     * @return \MongoCursor
     */
    public function find (array $query = array(), array $fields = array())
    {
        return $this->_collection->find($query, $fields);
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