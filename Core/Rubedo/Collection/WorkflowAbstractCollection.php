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
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IAbstractCollection;
use Rubedo\Mongo\DataAccess;
use Rubedo\Services\Manager;

/**
 * Class implementing the API to MongoDB
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
abstract class WorkflowAbstractCollection extends AbstractCollection
{

    protected function _init() {
        // init the data access service
        $this->_dataService = Manager::getService('MongoWorkflowDataAccess');
        $this->_dataService->init($this->_collectionName);
    }
	
	/**
     * Update an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::update
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
	public function update(array $obj, $safe = true, $live = true){
		if($live === true){
			$this->_dataService->setLive();
		} else {
			$this->_dataService->setWorkspace();
		}
		
		$returnArray = parent::update($obj, $safe);
		
		if($returnArray['data']['status'] === 'published'){
			$result = $this->_dataService->publish($returnArray['data']['id']);
			
			if(!$result['success']){
				$returnArray['success'] = false;
				$returnArray['msg'] = "failed to publish the content";
			}
		}
		
		return $returnArray;
	}
	
	/**
     * Create an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::create
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function create(array $obj, $safe = true, $live = true) {
    	if($live === true){
			$this->_dataService->setLive();
		} else {
			$this->_dataService->setWorkspace();
		}
		
        $result = parent::create($obj, $safe);
		
		return $result;
    }
	
	/**
     * Find an item given by its literral ID
     * @param string $contentId
     * @return array
     */
    public function findById($contentId, $live = true) {
        if($live === true){
			$this->_dataService->setLive();
		} else {
			$this->_dataService->setWorkspace();
		}
		
        $result = parent::findById($contentId);
		
		return $result;
    }
	
	/**
     * Do a find request on the current collection
     *
	 * @param array $filters filter the list with mongo syntax
	 * @param array $sort sort the list with mongo syntax
     * @return array
     */
    public function getList($filters = null, $sort = null, $live = true) {
    	if($live === true){
			$this->_dataService->setLive();
		} else {
			$this->_dataService->setWorkspace();
		}
		
        $result = parent::getList($filters, $sort);
		
		return $result;
    }
	
	/**
     * Find child of a node tree
     * @param string $parentId id of the parent node
     * @param array $filters array of data filters (mongo syntax)
     * @param array $sort  array of data sorts (mongo syntax)
     * @return array children array
     */
    public function readChild($parentId, $filters = null, $sort = null, $live = true) {
        if($live === true){
			$this->_dataService->setLive();
		} else {
			$this->_dataService->setWorkspace();
		}
		
        $result = parent::readChild($parentId, $filters, $sort);
		
		return $result;
    }

}
