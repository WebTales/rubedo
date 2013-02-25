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

use Rubedo\Interfaces\Collection\IPages, Rubedo\Services\Manager;

/**
 * Service to handle Pages
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Pages extends AbstractCollection implements IPages
{
    protected $_indexes = array(
        array('keys'=>array('site'=>1,'parentId'=>1,'orderValue'=>1)),
        array('keys'=>array('site'=>1,'parentId'=>1,'workspace'=>1,'orderValue'=>1)),
        
    );
    
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

	public function __construct(){
		$this->_collectionName = 'Pages';
		parent::__construct();
	}
	
	public function matchSegment($urlSegment,$parentId,$siteId){
	    return $this->_dataService->findOne(array('pageURL'=>$urlSegment,'parentId'=>$parentId,'site'=>$siteId));
	}

	/**
	 * Delete objects in the current collection
	 *
	 * @see \Rubedo\Interfaces\IDataAccess::destroy
	 * @param array $obj
	 *            data object
	 * @param bool $options
	 *            should we wait for a server response
	 * @return array
	 */
	public function destroy(array $obj, $options = array('safe'=>true)) {
	    $deleteCond = array('_id' => array('$in' => $this->_getChildToDelete($obj['id'])));
	
	    $resultArray = $this->_dataService->customDelete($deleteCond);
	
	    if ($resultArray['ok'] == 1) {
	        if ($resultArray['n'] > 0) {
	            $returnArray = array('success' => true);
	        } else {
	            $returnArray = array('success' => false, "msg" => 'no record had been deleted');
	        }
	    } else {
	        $returnArray = array('success' => false, "msg" => $resultArray["err"]);
	    }
	    
	    $this->_clearCacheForPage($obj);
	    return $returnArray;
	}

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array('safe'=>true))
    {
        $obj = $this->_initContent($obj);
        
        $returnValue = parent::update($obj, $options);
        
        $this->_clearCacheForPage($obj);
        
        $this->propagateWorkspace ($obj['id'], $obj['workspace']);
        
        return $returnValue;
    }
    
    /**
     * Set workspace and URL.
     * 
     * @param array $obj
     * @throws \Exception
     * @return array
     */
    protected function _initContent($obj){
        
        //set inheritance for workspace
        if (! isset($obj['inheritWorkspace']) || $obj['inheritWorkspace']!==false) {
            $obj['inheritWorkspace'] = true;
        }
        //resolve inheritance if not forced
        if ($obj['inheritWorkspace']) {
            unset($obj['workspace']);
            $ancestorsLine = array_reverse($this->getAncestors($obj));
            foreach ($ancestorsLine as $key => $ancestor) {
                if (isset($ancestor['inheritWorkspace']) && $ancestor['inheritWorkspace'] == false) {
                    $obj['workspace'] = $ancestor['workspace'];
                    break;
                }
            }
            if (! isset($obj['workspace'])) {
                $site = Manager::getService('Sites')->findById($obj['site']);
                $obj['workspace'] = (isset($site['workspace'])&&!empty($site['workspace'])) ? $site['workspace'] : 'global';
            }
        }
        //verify workspace can be attributed
        if (! self::isUserFilterDisabled()) {	
        	$writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
		
	        if (! in_array($obj['workspace'], $writeWorkspaces)) {
	            throw new \Rubedo\Exceptions\Access('You can not assign page to this workspace');
	        }
		}
        
        //set text property
        if (empty($obj['text'])) {
            $obj['text'] = $obj['title'];
        }
        
        //set pageUrl
        if (empty($obj['pageURL'])) {
            $dataUrl = $obj['title'];
        } else {
            $dataUrl = $obj['pageURL'];
        }
        
        //filter URL
        $obj['pageURL'] = $this->_filterUrl($dataUrl);
        
        return $obj;
    }
	
    protected function _clearCacheForPage($obj){
        $pageId = $obj['id'];
        Manager::getService('UrlCache')->customDelete(array(
        'pageId' => $pageId
        ), array('safe'=>false));
    }
    
	public function findByNameAndSite($name,$siteId){
		$filterArray['site'] = $siteId;
        $filterArray['text'] = $name;
		return $this->_dataService->findOne($filterArray);
	}
	public function getListByMaskId($maskId)
	{
		$filterArray[]=array("property"=>"maskId","value"=>$maskId);
		return $this->getList($filterArray);
	}
	public function maskIsUsed($maskId)
	{
		$filterArray["maskId"]=$maskId;
		$result=$this->_dataService->findOne($filterArray);
		return ($result!=null)?array("used"=>true):array("used"=>false);
	}

    public function create (array $obj, $options = array('safe'=>true))
    {
        $obj = $this->_initContent($obj);
        return parent::create($obj, $options);
    }
		
	
	protected function _filterUrl($url){
	    mb_regex_encoding('UTF-8');
	     
	    $normalizeChars = array(
	        'Á'=>'A', 'À'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Å'=>'A', 'Ä'=>'A', 'Æ'=>'AE', 'Ç'=>'C',
	        'É'=>'E', 'È'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Í'=>'I', 'Ì'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ð'=>'Eth',
	        'Ñ'=>'N', 'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O',
	        'Ú'=>'U', 'Ù'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y',
	         
	        'á'=>'a', 'à'=>'a', 'â'=>'a', 'ã'=>'a', 'å'=>'a', 'ä'=>'a', 'æ'=>'ae', 'ç'=>'c',
	        'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e', 'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'eth',
	        'ñ'=>'n', 'ó'=>'o', 'ò'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o',
	        'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y',
	         
	        'ß'=>'sz', 'þ'=>'thorn', 'ÿ'=>'y', ' '=>'-','\''=>'-'
	    );
	     
	    $url = strtr(trim($url),$normalizeChars);
	    $url = mb_strtolower($url,'UTF-8');
	    $url = mb_ereg_replace("[^A-Za-z0-9\\.\\-]","",$url);
	    $url = trim($url,'-');
	    
	    return $url;
	}
	
	
	public function deleteBySiteId($id)
	{
		return $this->_dataService->customDelete(array('site' => $id));
	}
	
	public function clearOrphanPages() {
		$masksService = Manager::getService('Masks');
		
		$result = $masksService->getList();
		
		//recovers the list of contentTypes id
		foreach ($result['data'] as $value) {
			$masksArray[] = $value['id'];
		}

		$result = $this->customDelete(array('maskId' => array('$nin' => $masksArray)));
		
		if($result['ok'] == 1){
			return array('success' => 'true');
		} else {
			return array('success' => 'false');
		}
	}
	
	public function countOrphanPages() {
		$masksService = Manager::getService('Masks');

		$result = $masksService->getList();
		
		//recovers the list of contentTypes id
		foreach ($result['data'] as $value) {
			$masksArray[] = $value['id'];
		}
		
		return $this->count(array(array('property' => 'maskId', 'operator' => '$nin', 'value' => $masksArray)));
	}
	
	protected function _addReadableProperty ($obj)
    {
        if (! self::isUserFilterDisabled()) {
        	//Set the workspace for old items in database	
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

	/* (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractCollection::readChild()
     */
    public function readChild ($parentId, $filters = null, $sort = null)
    {
        $list = parent::readChild ($parentId,$filters, $sort);
        $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
        $returnArray = array();
        foreach ($list as $page){
        	if (! self::isUserFilterDisabled()) {
	            if(!in_array($page['workspace'], $writeWorkspaces)){
	                $page['readOnly'] =true;
	            }else{
	                $page['readOnly'] =false;
	            }
			}
           $returnArray[] = $page;
        }
        return $returnArray;
        
    }

    public function propagateWorkspace ($parentId, $workspaceId, $siteId = null)
    {
        $filters = array();
        if ($siteId) {
            $filters[] = array(
                'property' => 'site',
                'value' => $siteId
            );
        }
        $pageList = $this->readChild($parentId,$filters);
        foreach ($pageList as $page) {
        	if (! self::isUserFilterDisabled()) {
	            if (! $page['readOnly']) {
	                if ($page['workspace'] != $workspaceId) {
	                    $this->update($page);
	                }
	            }
			} else {
				if ($page['workspace'] != $workspaceId) {
                    $this->update($page);
                }
			}
        }
    }

    /**
     *
     * @param string $id
     *            id whose children should be deleted
     * @return array array list of items to delete
     */
    protected function _getChildToDelete($id) {
        // delete at least the node
        $returnArray = array($this->_dataService->getId($id));
    
        // read children list
        $terms = $this->readChild($id);
    
        // for each child, get sublist of children
        if (is_array($terms)) {
            foreach ($terms as $key => $value) {
                $returnArray = array_merge($returnArray, $this->_getChildToDelete($value['id']));
            }
        }
    
        return $returnArray;
    }
}