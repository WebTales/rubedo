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

use Rubedo\Interfaces\Collection\IWorkspaces,Rubedo\Services\Manager, WebTales\MongoFilters\Filter;
use WebTales;

/**
 * Service to handle Workspaces
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Workspaces extends AbstractCollection implements IWorkspaces
{
    protected $_indexes = array(
        array('keys'=>array('text'=>1),'options'=>array('unique'=>true)),
    );
    
   protected $_addAll=false;
    
    protected function _init(){
        parent::_init();
        
		if (! self::isUserFilterDisabled()) {	
	        $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
	        if(in_array('all',$readWorkspaceArray)){
	            $this->_addAll = true;	             
	            return;
	        }
	        $mongoIdArray = array();
	        foreach ($readWorkspaceArray as $workspaceId){
	            if($workspaceId == 'global'){
	                continue;
	            }
	            $mongoIdArray[]=$workspaceId;
	        }
	        $filter = Filter::Factory('InUid')->setValue($mongoIdArray);
	        
	        $this->_dataService->addFilter($filter);
		}else{
		    $this->_addAll = true;
		}
    }
    
    
    /**
     * a virtual workspace which is the main & public one
     *
     * @var array
     */
    protected $_virtualGlobalWorkspace = array(
        'id' => 'global',
        'text' => 'Global',
        'readOnly' => true
    );
    
    /**
     * a virtual workspace whichis an alias for "all" workspaces
     *
     * @var array
     */
    protected $_virtualAllWorkspaces = array(
            'id' => 'all',
            'text' => 'Tous les espaces',
            'readOnly' => true,
    );

    public function __construct ()
    {
        $this->_collectionName = 'Workspaces';
        parent::__construct();
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::getList()
     * 
     */
    public function getList (\WebTales\MongoFilters\IFilter $filters = null, $sort = null, $start = null, $limit = null)
    {
        $this->_addAll = !$this->_hasNotAllWorkspacesFilter($filters);
        
        
        $list = parent::getList($filters, $sort, $start, $limit);
        $list['data'] = array_merge(array(
            $this->_virtualGlobalWorkspace
        ), $list['data']);
        
        if($this->_addAll){
            $list['data'] = array_merge(array(
                    $this->_virtualAllWorkspaces
            ), $list['data']);
            $list['count'] = $list['count'] + 1;
        }
        
        $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
        
        
        
		if (! self::isUserFilterDisabled()) {	
	        foreach ($list['data'] as &$workspace){
	            if(in_array($workspace['id'],$writeWorkspaces)){
	                $workspace['canContribute']=true;
	            }else{
	                $workspace['canContribute']=false;
	            }
	        }
		}
		
        $list['count'] = $list['count'] + 1;
        
        
        
        return $list;
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::getList()
     */
    public function getWholeList ($filters = null, $sort = null, $start = null, $limit = null)
    {
        $list = parent::getList($filters, $sort, $start, $limit);
        $list['data'] = array_merge(array(
            $this->_virtualGlobalWorkspace,$this->_virtualAllWorkspaces
        ), $list['data']);
        
        foreach ($list['data'] as &$workspace) {
            $workspace['canContribute'] = true;
        }
		
        $list['count'] = $list['count'] + 2;
        return $list;
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::findById()
     */
    public function findById ($contentId)
    {
        if ($contentId == 'global') {
            return $this->_virtualGlobalWorkspace;
        } elseif ($contentId == 'all') {
            return $this->_virtualAllWorkspaces;
        } else {
            return parent::findById($contentId);
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy (array $obj, $options = array())
    {
        if ($obj['id'] == 'global') {
            throw new \Rubedo\Exceptions\Access('You can not destroy global workspace', "Exception61");
        }
        
        return parent::destroy($obj, $options);
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \Rubedo\Collection\AbstractCollection::count()
     */
    public function count (\WebTales\MongoFilters\IFilter $filters = null)
    {
        return parent::count($filters) + 1;
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create (array $obj, $options = array())
    {
        if ($obj['text'] == 'Global') {
            throw new \Rubedo\Exceptions\Access('You can not create global workspace', "Exception62");
        }
        unset($obj['canContribute']);
        return parent::create($obj, $options);
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array())
    {
        if ($obj['id'] == 'global') {
            throw new \Rubedo\Exceptions\Access('You can not update global workspace', "Exception63");
        }
        if ($obj['name'] == 'Global') {
            throw new \Rubedo\Exceptions\Access('can\'t create a global workspace', "Exception62");
        }
        unset($obj['canContribute']);
        return parent::update($obj, $options);
    }
    
    /**
     * Add a readOnly field to contents based on user rights
     *
     * @param array $obj
     * @return array
     */
    protected function _addReadableProperty ($obj)
    {
        if (! self::isUserFilterDisabled()) {
    
            if (!Manager::getService('Acl')->hasAccess("write.ui.workspaces")) {
                $obj['readOnly'] = true;
            }
        }
    
        return $obj;
    }
    
    public function getAdminWorkspaceId(){
        $adminWorkspace = Manager::getService('Workspaces')->findByName('admin');
        if($adminWorkspace){
            return $adminWorkspace['id'];
        }else{
            return null;
        }
    }

    /**
     * Return true if $filters contains a Rubedo\Mongo\NotAllWorkspacesFilter
     *
     * @param WebTales\MongoFilters\IFilter $filters            
     * @return boolean
     */
    protected function _hasNotAllWorkspacesFilter (
            \WebTales\MongoFilters\IFilter $filters = null)
    {
        if ($filters instanceof WebTales\MongoFilters\CompositeFilter) {
            foreach ($filters->getFilters() as $filter) {
                if ($this->_hasNotAllWorkspacesFilter($filter)) {
                    return true;
                }
            }
        }
        
        if ($filters instanceof \Rubedo\Mongo\NotAllWorkspacesFilter) {
            
            return true;
        }
        
        return false;
    }
    
}
