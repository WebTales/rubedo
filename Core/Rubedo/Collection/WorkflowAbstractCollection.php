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
		$this->_dataService->setWorkspace();
    }
	
	public function update(array $obj, $safe = true){
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

}
