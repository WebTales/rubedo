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

use Rubedo\Interfaces\Collection\IWorkspaces,Rubedo\Services\Manager;

/**
 * Service to handle Workspaces
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Workspaces extends AbstractCollection implements IWorkspaces
{
    protected function _init(){
        parent::_init();
        
        $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
        if(in_array('all',$readWorkspaceArray)){
            return;
        }
        $mongoIdArray = array();
        foreach ($readWorkspaceArray as $workspaceId){
            if($workspaceId == 'global'){
                continue;
            }
            $mongoIdArray[]=$this->_dataService->getId($workspaceId);
        }
        $filter = array('_id'=> array('$in'=>$mongoIdArray));
        
        $this->_dataService->addFilter($filter);
    }
    
    
    /**
     * a virtual taxonomy which reflects sites & pages trees
     *
     * @var array
     */
    protected $_virtualGlobalWorkspace = array(
        'id' => 'global',
        'text' => 'Global',
        'readOnly' => true
    );

    public function __construct ()
    {
        $this->_collectionName = 'Workspaces';
        parent::__construct();
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::getList()
     */
    public function getList ($filters = null, $sort = null, $start = null, $limit = null)
    {
        $list = parent::getList($filters, $sort, $start, $limit);
        $list['data'] = array_merge(array(
            $this->_virtualGlobalWorkspace
        ), $list['data']);
        
        $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
                
        foreach ($list['data'] as &$workspace){
            if(in_array($workspace['id'],$writeWorkspaces)){
                $workspace['canContribute']=true;
            }else{
                $workspace['canContribute']=false;
            }
        }
        $list['count'] = $list['count'] + 1;
        return $list;
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::findById()
     */
    public function findById ($contentId)
    {
        if ($contentId == 'global') {
            return $this->_virtualGlobalWorkspace;
        } else {
            return parent::findById($contentId);
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy (array $obj, $options = array('safe'=>true))
    {
        if ($obj['id'] == 'global') {
            throw new \Exception('can\'t destroy global workspace');
        }
        
        return parent::destroy($obj, $options);
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \Rubedo\Collection\AbstractCollection::count()
     */
    public function count ($filters = null)
    {
        return parent::count($filters) + 1;
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create (array $obj, $options = array('safe'=>true,))
    {
        if ($obj['text'] == 'Global') {
            throw new \Exception('can\'t create global workspace');
        }
        unset($obj['canContribute']);
        return parent::create($obj, $options);
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array('safe'=>true,))
    {
        if ($obj['id'] == 'global') {
            throw new \Exception('can\'t update global workspace');
        }
        if ($obj['name'] == 'Global') {
            throw new \Exception('can\'t create a global workspace');
        }
        unset($obj['canContribute']);
        return parent::update($obj, $options);
    }
}
