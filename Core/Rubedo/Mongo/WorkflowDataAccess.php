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
namespace Rubedo\Mongo;

use Rubedo\Interfaces\Mongo\IWorkflowDataAccess;

/**
 * Class implementing the API to MongoDB
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class WorkflowDataAccess extends DataAccess implements IWorkflowDataAccess
{
	protected $_currentWs = "live";
	
	public function setWorkspace(){
		$this->_currentWs = 'workspace';
	}
	
	public function setLive(){
		$this->_currentWs = 'live';
	}
	
	public function publish($objectId){
		
	}

	public function read(){
		$content = parent::read();
		//do filters
		foreach ($content as $key => $value) {
			foreach ($value[$this->_currentWs] as $subkey => $subvalue){
				$content[$key][$subkey] = $subvalue;
			}
			unset($content[$key]['live']);
			unset($content[$key]['workspace']); 	
		}
		
		
		return $content;
	}
	
	public function update(array $obj, $safe = true){
		//do filters
		$result = parent::update($obj, $safe);
		//do post filter
		return $result;
	}
	
	protected function _inputObjectFilter($obj){
		
	}
	
	protected function _outputObjectFilter($obj){
		
	}

}
