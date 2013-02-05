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

use Rubedo\Interfaces\Collection\IDamTypes,Rubedo\Services\Manager;

/**
 * Service to handle Groups
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class DamTypes extends AbstractCollection implements IDamTypes
{
    /**
     * Only access to content with read access
     * @see \Rubedo\Collection\AbstractCollection::_init()
     */
    protected function _init(){
        parent::_init();
        $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
        if(in_array('all',$readWorkspaceArray)){
            return;
        }
        $readWorkspaceArray[] = null;
        $filter = array('workspaces'=> array('$in'=>$readWorkspaceArray));
        $this->_dataService->addFilter($filter);
    }

	public function __construct(){
		$this->_collectionName = 'DamTypes';
		parent::__construct();
	}
	
	public function create (array $obj, $options = array('safe'=>true))
    {
    	$obj = $this->_addDefaultWorkspace($obj);
		
		return parent::create($obj, $options);
	}
	
	public function update (array $obj, $options = array('safe'=>true))
    {
    	$obj = $this->_addDefaultWorkspace($obj);
		
		return parent::update($obj, $options);
	}
	
	protected function _addDefaultWorkspace($obj){
		
		if (! isset($obj['workspaces']) ||  $obj['workspaces']=='' || $obj['workspaces']==array()) {
            $obj['workspaces'] = array(
                'global'
            );
        }
				
		return $obj;
	}
	
	/**
     *  (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractCollection::findById()
     */
    public function findById ($contentId)
    {
        $obj = parent::findById ($contentId);
        $obj= $this->_addReadableProperty ($obj);
        return $obj;
        
    }
	
	protected function _addReadableProperty ($obj)
    {
        if (! isset($obj['workspaces'])) {
            $obj['workspaces'] = array(
                'global'
            );
        }
        $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
        
        if (count(array_intersect($obj['workspaces'], $writeWorkspaces)) == 0) {
            $obj['readOnly'] = true;
        } else {
            
            $obj['readOnly'] = false;
        }
        
        return $obj;
    }
	
}
