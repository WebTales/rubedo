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
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IWorkflowAbstractCollection;
use Rubedo\Services\Manager;

/**
 * Class implementing the API to MongoDB
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
abstract class WorkflowAbstractCollection extends AbstractCollection implements IWorkflowAbstractCollection
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
     * @param bool $options should we wait for a server response
     * @return array
     */
	public function update(array $obj, $options = array('safe'=>true), $live = true){
		if($live === true){
			$this->_dataService->setLive();
		} else {
			$this->_dataService->setWorkspace();
		}
		
		$returnArray = parent::update($obj, $options);
		if($returnArray['success']){
			if($returnArray['data']['status'] === 'published' && !$live){
				$result = $this->publish($returnArray['data']['id']);
				
				if(!$result['success']){
					$returnArray['success'] = false;
					$returnArray['msg'] = "failed to publish the content";
					unset($returnArray['data']);
				}
			}
		} else {
			$returnArray = array('success' => false, 'msg' => 'failed to update');
		}
		
		return $returnArray;
	}
	
	/**
     * Create an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::create
     * @param array $obj data object
     * @param bool $options should we wait for a server response
     * @return array
     */
    public function create(array $obj, $options = array('safe'=>true), $live = false) {
    	if($live === true){
    		throw new Exception('Can\' create directly in live');
		}

		$this->_dataService->setWorkspace();
		
		
        $returnArray = parent::create($obj, $options);
		
		if($returnArray['success']){
			if($returnArray['data']['status'] === 'published'){
				$result = $this->publish($returnArray['data']['id']);
				
				if(!$result['success']){
					$returnArray['success'] = false;
					$returnArray['msg'] = "failed to publish the content";
					unset($returnArray['data']);
				}
			}
		} else {
			$returnArray = array('success' => false, 'msg' => 'failed to update');
		}
		
		return $returnArray;
    }
	
	/**
     * Find an item given by its literral ID
     * @param string $contentId
     * @return array
     */
    public function findById($contentId, $live = true,$raw = true) {
        if($live === true){
			$this->_dataService->setLive();
		} else {
			$this->_dataService->setWorkspace();
		}
		
        $returnArray = $this->_dataService->findById($contentId,$raw);
		
		return $returnArray;
    }
    
    /* (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractCollection::getList()
     */
    public function getList($filters = null, $sort = null, $start = null, $limit = null, $live = true) {
    	if($live === true){
			$this->_dataService->setLive();
		} else {
			$this->_dataService->setWorkspace();
		}

        $returnArray = parent::getList($filters, $sort, $start, $limit);
		
		return $returnArray;
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
		
        $returnArray = parent::readChild($parentId, $filters, $sort);
		
		return $returnArray;
    }
	
	public function publish($objectId) {
		return $this->_dataService->publish($objectId);
	}

}
