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
namespace Rubedo\Interfaces\Collection;

/**
 * Abstract interface for the service handling collections
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IAbstractCollection {

    /**
     * Do a find request on the current collection
     *
	 * @param array $filters filter the list with mongo syntax
	 * @param array $sort sort the list with mongo syntax
     * @return array
     */
    public function getList($filters = null, $sort = null);

    /**
     * Find an item given by its literral ID
     * @param string $contentId
     * @return array
     */
    public function findById($contentId);

    /**
     * Create an objet in the current collection
     *
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function create(array $obj, $safe = true);

    /**
     * Update an objet in the current collection
     *
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function update(array $obj, $safe = true);

    /**
     * Delete objets in the current collection
     *
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function destroy(array $obj, $safe = true);
	
	/**
     * Find child of a node tree
     * @param string $parentId id of the parent node
	 * @param array $filters array of data filters (mongo syntax) 
	 * @param array $sort  array of data sorts (mongo syntax)
     * @return array children array
     */
    public function readChild($parentId, $filters = null, $sort = null);

}
