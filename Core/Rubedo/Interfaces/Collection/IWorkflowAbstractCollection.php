<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Interfaces\Collection;

/**
 * Interface of service handling WorkflowAbstractCollection
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IWorkflowAbstractCollection extends IAbstractCollection{
	/**
     * Update an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::update
     * @param array $obj data object
     * @param bool $options should we wait for a server response
     * @return array
     */
	public function update(array $obj, $options = array('safe'=>true), $live = true);
	
	/**
     * Create an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::create
     * @param array $obj data object
     * @param bool $options should we wait for a server response
     * @return array
     */
    public function create(array $obj, $options = array('safe'=>true), $live = false);
	
	/**
     * Find an item given by its literral ID
     * @param string $contentId
     * @return array
     */
    public function findById($contentId, $live = true,$raw = true);
	
	/* (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractCollection::getList()
     */
    public function getList($filters = null, $sort = null, $start = null, $limit = null, $live = true);
	
	/**
     * Find child of a node tree
     * @param string $parentId id of the parent node
     * @param array $filters array of data filters (mongo syntax)
     * @param array $sort  array of data sorts (mongo syntax)
     * @return array children array
     */
    public function readChild($parentId, $filters = null, $sort = null, $live = true);
	
	public function publish($objectId);
	
}
