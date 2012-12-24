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
namespace Rubedo\Interfaces\Mongo;

/**
 * Interface of data access services
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IWorkflowDataAccess extends IDataAccess{
	
	/**
	 * Set the current workspace to workspace
	 */
	public function setWorkspace();
	
	/**
	 * Set the current workspace to live
	 */
	public function setLive();
	 
	/**
	 * Publish a content
	 */
	public function publish($objectId);
	
	/**
	 * Allow to read in the current collection
	 * 
	 * @return array
	 */
	public function read();
	
	/**
	 * Allow to update an element in the current collection
	 * 
	 * @return bool
	 */
	public function update(array $obj, $safe = true);
	
	/**
	 * Allow to create an item in the current collection
	 * 
	 * @return array
	 */
	public function create(array $obj, $safe = true);
	
	/**
     * Delete objets in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::destroy
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function destroy(array $obj, $safe = true);
}
