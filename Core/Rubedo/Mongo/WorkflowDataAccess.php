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
	
	protected $_metaDataFields = array('id', 'idLabel', 'typeId', 'createTime', 'createUser', 'lastUpdateTime', 'lastUpdateUser', 'version', 'online');
	
	protected function _inputObjectFilter($obj){
		foreach ($obj as $key => $value) {
			if(in_array($key, $this->_metaDataFields)){
				continue;
			}
			$obj[$this->_currentWs][$key]=$value;
			unset($obj[$key]);
		}
		
		return $obj;
	}
	
	protected function _outputObjectFilter($obj){
		foreach ($obj[$this->_currentWs] as $key => $value){
			$obj[$key] = $value;
		}
		unset($obj['live']);
		unset($obj['workspace']);
		
		return $obj;
	}
	
	/**
	 * Set the current workspace to workspace
	 */
	public function setWorkspace(){
		$this->_currentWs = 'workspace';
	}
	
	/**
	 * Set the current workspace to live
	 */
	public function setLive(){
		$this->_currentWs = 'live';
	}
	
	/**
	 * Publish a content
	 */
	public function publish($objectId){
		
	}
	
	/**
	 * Allow to read in the current collection
	 * 
	 * @return array
	 */
	public function read(){
		$content = parent::read();
		
		foreach ($content as $key => $value) {
			$content[$key] = $this->_outputObjectFilter($content[$key]);
		}
		
		return $content;
	}
	
	/**
	 * Allow to update an element in the current collection
	 * 
	 * @return bool
	 */
	public function update(array $obj, $safe = true){
		$obj = $this->_inputObjectFilter($obj);
		
		$result = parent::update($obj, $safe);
		
		$result['data'] = $this->_outputObjectFilter($result['data']);
		
		/*if($workspace == 'workspace' && $obj[$workspace]['status']=='published'){
			//do publish action
			//call version service
		}*/
		
		return $result;
	}
	
	/**
	 * Allow to create an item in the current collection
	 * 
	 * @return array
	 */
	public function create(array $obj, $safe = true){
		$result = parent::create($obj, $safe);
		
		$result['data'] = $this->_outputObjectFilter($result['data']);
		
		return $result;
	}

}
