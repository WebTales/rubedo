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
        $filter = array('workspace'=> array('$in'=>$readWorkspaceArray));
        $this->_dataService->addFilter($filter);
    }

	public function __construct(){
		$this->_collectionName = 'Pages';
		parent::__construct();
	}
	
	public function matchSegment($urlSegment,$parentId,$siteId){
	    return $this->_dataService->findOne(array('pageURL'=>$urlSegment,'parentId'=>$parentId,'site'=>$siteId));
	}

    /**
     * (non-PHPdoc)
     * 
     * @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy (array $obj, $options = array('safe'=>true))
    {
        $returnValue = parent::destroy($obj, $options);

        $this->_clearCacheForPage($obj);
        
        return $returnValue;
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array('safe'=>true))
    {
        $obj = $this->_initContent($obj);
        
        $returnValue = parent::update($obj, $options);
        
        $this->_clearCacheForPage($obj);
        
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
        if (! isset($obj['inheritWorkspace']) || empty($obj['inheritWorkspace'])) {
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
        $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
        if (! in_array($obj['workspace'], $writeWorkspaces)) {
            throw new \Exception('You can not assign page to this workspace');
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
	
	/* (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractCollection::getList()
     */
    public function getList ($filters = null, $sort = null, $start = null, $limit = null)
    {
        $list = parent::getList ($filters, $sort, $start, $limit);
        $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
        $returnArray = array();
        foreach ($list as $page){
            if(!in_array($page['workspace'], $writeWorkspaces)){
                $page['readOnly'] =true;
            }else{
                $page['readOnly'] =false;
            }
           $returnArray[] = $page;
        }
        return $returnArray;
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
            if(!in_array($page['workspace'], $writeWorkspaces)){
                $page['readOnly'] =true;
                
            }else{
                $page['readOnly'] =false;
            }
           $returnArray[] = $page;
        }
        return $returnArray;
        
    }

    
    

	
	
	
}
