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
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IDam;
use Rubedo\Services\Manager;

/**
 * Service to handle Groups
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Dam extends AbstractCollection implements IDam
{

    protected $_indexes = array(
        array('keys'=>array('target'=>1,'createTime'=>-1)),
        array('keys'=>array('mainFileType'=>1,'target'=>1,'createTime'=>-1)),
        array('keys'=>array('originalFileId'=>1),'options'=>array('unique'=>true)),
    );

    /**
     * ensure that no nested contents are requested directly
     */
    protected function _init ()
    {
        parent::_init();
        
        if (! self::isUserFilterDisabled()) {
            $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
            if (! in_array('all', $readWorkspaceArray)) {
                $readWorkspaceArray[] = null;
                $readWorkspaceArray[] = 'all';
                $filter = array(
                        'target' => array(
                                '$in' => $readWorkspaceArray
                        )
                );
                $this->_dataService->addFilter($filter);
	        }
	        
		}
    }

    public function __construct ()
    {
        $this->_collectionName = 'Dam';
        parent::__construct();
    }

    public function destroy (array $obj, $options = array('safe'=>true))
    {
        $obj = $this->_dataService->findById($obj['id']);
        $destroyOriginal = Manager::getService('Files')->destroy(array(
            'id' => $obj['originalFileId']
        ));
        
        $returnArray = parent::destroy($obj, $options);
        if ($returnArray["success"]) {
            $this->_unIndexDam($obj);
        }
        return $returnArray;
    }

    /**
     * Push the dam to Elastic Search
     *
     * @param array $obj            
     */
    protected function _indexDam ($obj)
    {
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->indexDam($obj);
    }

    /**
     * Remove the content from Indexed Search
     *
     * @param array $obj            
     */
    protected function _unIndexDam ($obj)
    {
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->deleteDam($obj['typeId'], $obj['id']);
    }
	
	protected function _validateMediaType(array $obj) {
		
	}

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array('safe'=>true,))
    {
        $this->_filterInputData($obj);
        
        $originalFilePointer = Manager::getService('Files')->findById($obj['originalFileId']);
        if (! $originalFilePointer instanceof \MongoGridFSFile) {
            throw new \Rubedo\Exceptions\Server('no file found');
        }
        $obj['fileSize'] = $originalFilePointer->getSize();
		
		if(count(array_intersect(array($obj['writeWorkspace']), $obj['target']))==0){
			$obj['target'][] = $obj['writeWorkspace'];
			$obj['fields']['target'][] = $obj['writeWorkspace'];
		}
		
        $returnArray = parent::update($obj, $options);
        
        if ($returnArray["success"]) {
            $this->_indexDam($returnArray['data']);
        }
        
        return $returnArray;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create (array $obj, $options = array('safe'=>true), $index = true)
    {
        $obj = $this->_setDefaultWorkspace($obj);
        
        $this->_filterInputData($obj);
        
        $originalFilePointer = Manager::getService('Files')->findById($obj['originalFileId']);
        if (! $originalFilePointer instanceof \MongoGridFSFile) {
            throw new \Rubedo\Exceptions\Server('no file found');
        }
        $obj['fileSize'] = $originalFilePointer->getSize();
        $returnArray = parent::create($obj, $options);
        
        if ($returnArray["success"] and $index) {
            $this->_indexDam($returnArray['data']);
        }
        
        return $returnArray;
    }

    public function getByType ($typeId)
    {
        $filter = array(
            array(
                'property' => 'typeId',
                'value' => $typeId
            )
        );
        
        return $this->getList($filter);
	}

	
	public function getListByDamTypeId($typeId)
	{
		$filterArray[]=array("property"=>"typeId","value"=>$typeId);
		return $this->getList($filterArray);
	}
	
	/**
	 * Set workspace if none given based on User main group.
	 * 
	 * @param array $content
	 * @return array
	 */
	protected function _setDefaultWorkspace($dam){
	    if(!isset($dam['writeWorkspace']) || $dam['writeWorkspace']=='' || $dam['writeWorkspace']==array()){
	        $mainWorkspace = Manager::getService('CurrentUser')->getMainWorkspace();
	        $dam['writeWorkspace'] = $mainWorkspace['id'];
			$dam['fields']['writeWorkspace'] = $mainWorkspace['id'];
	    } else {
        	$readWorkspaces = array_values(Manager::getService('CurrentUser')->getReadWorkspaces());
			
			if(!in_array($dam['writeWorkspace'], $readWorkspaces) && $readWorkspaces[0]!="all"){
				throw new \Rubedo\Exceptions\Access('You don\'t have access to this workspace ');
			}
        }
		
		if(!isset($dam['target'])){
			$dam['target'] = array();
		}
		
		if(!in_array($dam['writeWorkspace'], $dam['target'])){
			$dam['target'][] = $dam['writeWorkspace'];
			$dam['fields']['target'][] = $dam['writeWorkspace'];
		}
		
        return $dam;
    }
	
	/**
	 * Defines if each objects are readable
	 * @param array $obj Contain the current object
	 * @return array
	 */
    protected function _addReadableProperty ($obj)
    {
        if (! self::isUserFilterDisabled()) {	
	        $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();

			//Set the workspace/target for old items in database
            if(!isset($obj['writeWorkspace']) || $obj['writeWorkspace']=="" || $obj['writeWorkspace']==array()){
            	$obj['writeWorkspace'] = "";
				$obj['fields']['writeWorkspace'] = "";
            }
            if(!isset($obj['target']) || $obj['target']=="" || $obj['target']==array()){
            	$obj['target'] = array('global');
				$obj['fields']['target'] = array('global');
            }
			
	        $damTypeId = $obj['typeId'];
			$aclServive = Manager::getService('Acl');
	        $damType = Manager::getService('DamTypes')->findById($damTypeId);
						
	        if ($damType['readOnly'] || !$aclServive->hasAccess("write.ui.dam")) {
	            $obj['readOnly'] = true;
	        } elseif (in_array($obj['writeWorkspace'], $writeWorkspaces) == false) {
	            $obj['readOnly'] = true;
	        } else {
	            
	            $obj['readOnly'] = false;
	        }
        }
        
        return $obj;
    }
	

    
    protected function _filterInputData (array $obj, array $model = null)
    {
        if (! self::isUserFilterDisabled()) {
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
    
            if (! in_array($obj['writeWorkspace'], $writeWorkspaces)) {
                throw new \Rubedo\Exceptions\Access('You can not assign to this workspace');
            }
    
            $readWorkspaces = Manager::getService('CurrentUser')->getReadWorkspaces();
            if ((!in_array('all', $readWorkspaces)) && count(array_intersect($obj['target'], $readWorkspaces))==0) {
                throw new \Rubedo\Exceptions\Access('You can not assign to this workspace');
            }
            
            $damTypeId = $obj['typeId'];
            $damType = Manager::getService('DamTypes')->findById($damTypeId);
            if (! in_array($obj['writeWorkspace'], $damType['workspaces']) && ! in_array('all', $damType['workspaces'])) {
                throw new \Rubedo\Exceptions\Access('You can not assign to this workspace');
            }
        }
        
        return parent::_filterInputData ($obj,$model);
    }
}

