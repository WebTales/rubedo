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
namespace Rubedo\Interfaces\Collection;

use WebTales\MongoFilters\IFilter;

/**
 * Abstract interface for the service handling collections
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IAbstractCollection
{

    /**
     * Do a find request on the current collection
     *
     * @param \WebTales\MongoFilters\IFilter $filters
     *            filter the list with mongo syntax
     * @param array $sort
     *            sort the list with mongo syntax
     * @return array
     */
    public function getList (IFilter $filters = null, $sort = null, $start = null, $limit = null);

    /**
     * Find an item given by its literral ID
     *
     * @param string $contentId            
     * @return array
     */
    public function findById ($contentId);

    /**
     * Find an item given by its name (find only one if many)
     *
     * @param string $name            
     * @return array
     */
    public function findByName ($name);

    /**
     * Create an objet in the current collection
     *
     * @param array $obj
     *            data object
     * @param bool $options
     *            should we wait for a server response
     * @return array
     */
    public function create (array $obj, $options = array());

    /**
     * Update an objet in the current collection
     *
     * @param array $obj
     *            data object
     * @param bool $options
     *            should we wait for a server response
     * @return array
     */
    public function update (array $obj, $options = array());

    /**
     * Delete objets in the current collection
     *
     * @param array $obj
     *            data object
     * @param bool $options
     *            should we wait for a server response
     * @return array
     */
    public function destroy (array $obj, $options = array());

    /**
     * Find child of a node tree
     *
     * @param string $parentId
     *            id of the parent node
     * @param \WebTales\MongoFilters\IFilter $filters
     *            array of data filters (mongo syntax)
     * @param array $sort
     *            array of data sorts (mongo syntax)
     * @return array children array
     */
    public function readChild ($parentId, IFilter $filters = null, $sort = null);

    /**
     * Do a count of item matching filter
     *
     * @param \WebTales\MongoFilters\IFilter $filters            
     * @return integer
     */
    public function count (IFilter $filters = null);

    /**
     * return a list with its parent-line
     *
     * @param \WebTales\MongoFilters\IFilter $filters            
     * @return array
     */
    public function getListWithAncestors (IFilter $filters = null);

    /**
     * Verify if all indexes are sets in DB
     *
     * @return boolean
     */
    public function checkIndexes ();

    /**
     * Ensure all indexes
     *
     * @return booelan
     */
    public function ensureIndexes ();

    /**
     * Do a findone request
     *
     * @param \WebTales\MongoFilters\IFilter $value
     *            search condition
     * @return array
     */
    public function findOne (IFilter $value);

    /**
     * Do a custom find
     *
     * @param \WebTales\MongoFilters\IFilter $filter            
     * @param array $fieldRule            
     * @return MongoCursor
     */
    public function customFind (IFilter $filter = null, $fieldRule = array());

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
    public function customUpdate (array $data, IFilter $updateCond, $options = array());

    /**
     * Do a delete on multiple items with a specific filter
     *
     * @param unknown $deleteCond            
     * @param unknown $options            
     * @return Ambigous <boolean, multitype:>
     */
    public function customDelete (IFilter $deleteCond, $options = array());

    /**
     * getter of the model
     *
     * @return array
     */
    public function getModel ();

    /**
     * Return the array of ancestors for a given item
     *
     * @param array $item
     *            object whose ancestors we're looking for
     * @param number $limit
     *            max number of ancestors to be found
     * @return array array of ancestors
     */
    public function getAncestors ($item, $limit = 10);

    public function fetchAllChildren ($parentId, IFilter $filters = null, $sort = null, $limit = 10);

    public function readTree (IFilter $filters = null);

    public function drop ();

    public function dropIndexes ();

    /**
     * Rename Author info in collection for a given AuthorId
     *
     * @param string $authorId            
     */
    public function renameAuthor ($authorId);
}
