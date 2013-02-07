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

    /**
     * ensure that no nested contents are requested directly
     */
    protected function _init ()
    {
        parent::_init();
        $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
        if (in_array('all', $readWorkspaceArray)) {
            return;
        }
        $readWorkspaceArray[] = null;
        $filter = array(
            'target' => array(
                '$in' => $readWorkspaceArray
            )
        );
        $this->_dataService->addFilter($filter);
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
        $ElasticDataIndexService->indexDam($obj['id']);
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

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array('safe'=>true,))
    {
        // if(!isset($obj['taxonomy']['navigation']) ||
        // empty($obj['taxonomy']['navigation'])){
        // $obj['taxonomy']['navigation'] =
        // Manager::getService('CurrentUser')->getWriteNavigationTaxonomy ();
        // }
        $originalFilePointer = Manager::getService('Files')->findById($obj['originalFileId']);
        if (! $originalFilePointer instanceof \MongoGridFSFile) {
            throw new \Rubedo\Exceptions\Server('no file found');
        }
        $obj['fileSize'] = $originalFilePointer->getSize();
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
    public function create (array $obj, $options = array('safe'=>true,))
    {
        // if(!isset($obj['taxonomy']['navigation']) ||
        // empty($obj['taxonomy']['navigation'])){
        // $obj['taxonomy']['navigation'] =
        // Manager::getService('CurrentUser')->getWriteNavigationTaxonomy ();
        // }
        if (! isset($obj['workspaces']) ||  $obj['workspaces']=='' || $obj['workspaces']==array()) {
            $obj['workspaces'] = array(
                'global'
            );
        }
		
		if (! isset($obj['target']) ||  $obj['target']=='' || $obj['target']==array()) {
            $obj['target'] = array(
                'global'
            );
        }
        
        $originalFilePointer = Manager::getService('Files')->findById($obj['originalFileId']);
        if (! $originalFilePointer instanceof \MongoGridFSFile) {
            throw new \Rubedo\Exceptions\Server('no file found');
        }
        $obj['fileSize'] = $originalFilePointer->getSize();
        $returnArray = parent::create($obj, $options);
        
        if ($returnArray["success"]) {
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

	/* (non-PHPdoc)
     * @see \Rubedo\Collection\WorkflowAbstractCollection::getList()
     */
    public function getList ($filters = null, $sort = null, $start = null, $limit = null)
    {
        $list = parent::getList($filters,$sort,$start,$limit);
        foreach ($list['data'] as &$obj){
            $obj = $this->_addReadableProperty($obj);
        }
        return $list;
    }
	
	/**
	 * Set workspace if none given based on User main group.
	 * 
	 * @param array $content
	 * @return array
	 */
	protected function _setDefaultWorkspace($content){
	    if(!isset($content['writeWorkspace']) || $content['writeWorkspace']=='' || $content['writeWorkspace']==array()){
	        $mainWorkspace = Manager::getService('CurrentUser')->getMainWorkspace();
	        $content['writeWorkspace'] = $mainWorkspace['id'];
	    }
	    if(!isset($content['target']) || $content['target']=='' || $content['target']==array() ){
	        $content['target'] = array_values(Manager::getService('CurrentUser')->getReadWorkspaces());
	    }
	    return $content;
	}
	
	/**
	 * Defines if each objects are readable
	 * @param array $obj Contain the current object
	 * @return array
	 */
    protected function _addReadableProperty ($obj)
    {
        $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
        $obj = $this->_setDefaultWorkspace($obj);

        $damTypeId = $obj['typeId'];
        $damType = Manager::getService('DamTypes')->findById($damTypeId);
		
		if($obj['fields']['title'] == "Salamandre") {
			//var_dump($obj['writeWorkspace'], $writeWorkspaces, in_array($obj['writeWorkspace'], $writeWorkspaces), $damType['readOnly']);die();
		}
		
        if ($damType['readOnly']) {
            $obj['readOnly'] = true;
        } elseif (in_array($obj['writeWorkspace'], $writeWorkspaces) == false) {
            $obj['readOnly'] = true;
        } else {
            
            $obj['readOnly'] = false;
        }
        
        return $obj;
    }
	
	/**
	 *  (non-PHPdoc)
     * @see \Rubedo\Collection\WorkflowAbstractCollection::findById()
     */
    public function findById ($contentId)
    {
        
        $obj = parent::findById ($contentId);
        $obj = $this->_addReadableProperty($obj);
        return $obj;
        
    }
}

