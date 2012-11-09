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

}
