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

use Rubedo\Interfaces\Collection\ISites, Rubedo\Services\Manager;

/**
 * Service to handle Sites
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Sites extends AbstractCollection implements ISites
{

    protected static $_overrideSiteName = array();

    protected static $_overrideSiteNameReverse = array();
    
    /**
     * Only access to content with read access
     * @see \Rubedo\Collection\AbstractCollection::_init()
     */
    protected function _init(){
        parent::_init();
		
		if (! self::isUserFilterDisabled()) {
	        $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
	        if(in_array('all',$readWorkspaceArray)){
	            return;
	        }
	        $filter = array('workspace'=> array('$in'=>$readWorkspaceArray));
	        $this->_dataService->addFilter($filter);
		}
    }

    public static function setOverride (array $array)
    {
        foreach ($array as $key => $value) {
            
            $newArray[str_replace('_', '.', $key)] = str_replace('_', '.', $value);
        }
        self::$_overrideSiteName = $newArray;
        self::$_overrideSiteNameReverse = array_flip($newArray);
    }

    public function __construct ()
    {
        $this->_collectionName = 'Sites';
        parent::__construct();
    }

    public function getHost ($site)
    {
        if (is_string($site)) {
            $site = $this->findById($site);
        }
        $label = $site['text'];
        if (isset(self::$_overrideSiteName[$label])) {
            $label = self::$_overrideSiteName[$label];
        }
        return $label;
    }

    public function findByHost ($host)
    {
        if (isset(self::$_overrideSiteNameReverse[$host])) {
            $host = self::$_overrideSiteNameReverse[$host];
        }
        
        $site = $this->findByName($host);
        if ($site === null) {
            $site = $this->_dataService->findOne(array(
                'alias' => $host
            ));
        }
        return $site;
    }
	public function deleteById($id)
	{
		$mongoId=$this->_dataService->getId($id);
		return $this->_dataService->customDelete(array('_id' => $mongoId));
	}
	
	public function destroy(array $obj, $options = array('safe'=>true))
	{
		$id=$obj['id'];
		$pages = \Rubedo\Services\Manager::getService('Pages')->deleteBySiteId($id);
		if($pages['ok']==1)
		{
			$masks = \Rubedo\Services\Manager::getService('Masks')->deleteBySiteId($id);
			if($masks['ok']==1)
			{
				$returnArray=parent::destroy($obj,$options);
			}else{
				$returnArray=array('success'=>false, 'msg'=>"error during masks deletion");
			}
			
		}else{
				$returnArray=array('success'=>false, 'msg'=>"error during pages deletion");
		}
		return $returnArray;
	}
	
	/**
	 *  (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array('safe'=>true,))
    {
        $obj = $this->_initContent($obj);
        
        $return = parent::update($obj,$options);
        if($return['success']==true){
            Manager::getService('Pages')->propagateWorkspace ('root', $return['data']['workspace'], $return['data']['id']);
        }
        return $return;
        
    }

    protected function _setDefaultWorkspace($site){
        if(!isset($site['workspace']) || $site['workspace']==''){
            $site['workspace'] = Manager::getService('CurrentUser')->getMainWorkspace();
        }
        return $site;
    }
    
	/** (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create (array $obj, $options = array('safe'=>true,))
    {
        $obj = $this->_setDefaultWorkspace($obj);
        $obj = $this->_initContent($obj);
        
        return parent::create($obj,$options);
    }
    
    protected function _initContent($obj){
        //verify workspace can be attributed
        if (! self::isUserFilterDisabled()) {
	        $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
	        if (! in_array($obj['workspace'], $writeWorkspaces)) {
	            throw new \Rubedo\Exceptions\Access('You can not assign to this workspace');
	        }
		}
		
        return $obj;
    }

	protected function _addReadableProperty ($obj)
    {
        if (! self::isUserFilterDisabled()) {	
	        if (! isset($obj['workspace'])) {
	            $obj['workspace'] = 'global';
	        }
	        $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
			
	        if (!in_array($obj['workspace'], $writeWorkspaces)) {
	            $obj['readOnly'] = true;
	        } else {
	            
	            $obj['readOnly'] = false;
	        }
		}
        
        return $obj;
    }
	
	/**
	 *  (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractCollection::getList()
     */
    public function getList ($filters = null, $sort = null, $start = null, $limit = null)
    {
        $list = parent::getList($filters,$sort,$start,$limit);
        foreach ($list['data'] as &$obj){
            $obj = $this->_addReadableProperty($obj);
        }
        return $list;
    }    
	
}
