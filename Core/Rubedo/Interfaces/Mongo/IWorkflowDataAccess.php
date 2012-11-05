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
}
