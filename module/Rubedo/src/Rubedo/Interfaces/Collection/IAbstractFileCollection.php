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

/**
 * Abstract interface for the service handling collections
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IAbstractFileCollection
{

    /**
     * Do a find request on the current collection
     *
     * @param array $filters
     *            filter the list with mongo syntax
     * @param array $sort
     *            sort the list with mongo syntax
     * @return array
     */
    public function getList ($filters = null, $sort = null, $start = null, $limit = null);

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
     * @return array
     */
    public function create (array $obj);

    /**
     * Update an objet in the current collection
     *
     * @param array $obj
     *            data object
     * @return array
     */
    public function update (array $obj);

    /**
     * Delete objets in the current collection
     *
     * @param array $obj
     *            data object
     * @return array
     */
    public function destroy (array $obj);
}
