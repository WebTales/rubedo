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
namespace Rubedo\Interfaces\Mongo;

/**
 * Interface of data access services
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IDataAccess
{

    /**
     * Initialize a data service handler to read or write in a DataBase
     * Collection
     *
     * @param string $collection name of the DB
     * @param string $dbName name of the DB
     * @param string $connection connection string to the DB server
     */
    public function init ($collection, $dbName = null, $connection = null);

    /**
     * Do a find request on the current collection
     *
     * @return array
     */
    public function read ();

    /**
	 * Do a findone request on the current collection
	 *
	 * @see \Rubedo\Interfaces\IDataAccess::findOne()
	 * @param array $value search condition
	 * @return array
	 */
    public function findOne ($value);

    /**
     * Create an objet in the current collection
     *
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function create (array $obj, $safe = true);

    /**
     * Update an objet in the current collection
     *
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function update(array $obj, $safe = true);

    /**
     * Update an objet in the current collection
     *
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function destroy (array $obj, $safe = true);
	
	/**
     * Do a find request on the current collection and return content as tree
     *
     * @return array
     */
	public function readTree();
	
	/**
	 * Find child of a node tree
	 * @param $parentId id of the parent node
	 * @return array children array
	 */
	 public function readChild($parentId);
	 
	 /**
	  * Add a filter condition to the service
	  * 
	  * Filter should be 
	  * array('field'=>'value') 
	  * or
	  * array('field'=>array('operator'=>value))
	  * 
	  * @param array $filter Native Mongo syntax filter array
	  * @return bool
	  */
	 public function addFilter(array $filter);
	 
	 /**
	  * Return the current array of conditions.
	  * @return array
	  */
	 public function getFilterArray();
	 
	 /**
	  * Unset all filter condition to the service  
	  *
	  * @return bool
	  */
	 public function clearFilter();
	 
	 /**
	  * Add a sort condition to the service
	  * 
	  * Sort should be 
	  * array('field'=>'value') 
	  * or
	  * array('field'=>array('operator'=>value))
	  * 
	  * @param array $sort Native Mongo syntax sort array
	  * @return bool
	  */
	 public function addSort(array $sort);
	 
	 /**
	  * Return the current array of conditions.
	  * @return array
	  */
	 public function getSortArray();
	 
	 /**
	  * Unset all sort condition to the service  
	  *
	  * @return bool
	  */
	 public function clearSort();
	 
	 /**
     * Add to the field list the array passed in argument
     *
     * @param array $fieldList
     */
    public function addToFieldList(array $fieldList);
	
	/**
     * Give the fields into the fieldList array
     * @return array
     */
    public function getFieldList();
	
	/**
     * Allow to remove one field in the current array
     *
     * @param array $fieldToRemove
     */
    public function removeFromFieldList(array $fieldToRemove);
	
	/**
     * Clear the fieldList array
     *
     */
    public function clearFieldList();
	
	/**
     * Add to the exclude field list the array passed in argument
     *
     * @param array $excludeFieldList
     */
    public function addToExcludeFieldList(array $excludeFieldList);
	
	/**
     * Give the fields into the excludeFieldList array
     */
    public function getExcludeFieldList();
	
	/**
     * Allow to remove one field in the current excludeFieldList array
     *
     * @param array $excludeFieldToRemove
     */
    public function removeFromExcludeFieldList(array $fieldToRemove);
	
	/**
     * Clear the excludeFieldList array
     */
    public function clearExcludeFieldList();
	
	/**
     * Hash a string and its salt
     *
     * @param $msg contains the string destined to be hashed
     * @param $salt
     * @return $hash is the final string with the message and its salt hashed
     */
    public function getHash($msg, $salt);
	
	/**
     * Compare the hashed string with a string hashed in the functions
     * If they are the same, the function return true
     *
     * @param $hash is the string already hashed
     * @param $msg is the string destined to be hashed with the salt
     * @param $salt is the salt for the $msg string
     */
    public function checkHash($hash, $msg, $salt);
	 
}