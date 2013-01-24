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
	

	public function __construct(){
		$this->_collectionName = 'Pages';
		parent::__construct();
	}
	
	public function matchSegment($urlSegment,$parentId,$siteId){
	    return $this->_dataService->findOne(array('pageURL'=>$urlSegment,'parentId'=>$parentId,'site'=>$siteId));
	}
	

	/* (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy (array $obj, $options = array('safe'=>true))
    {
        $pageId = $obj['id'];
        $returnValue = parent::destroy($obj,$options);
        Manager::getService('UrlCache')->customDelete(array('pageId'=>$pageId),$options);
        return $returnValue;
    }

	/* (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array('safe'=>true))
    {
        if(empty($obj['pageURL'])){
            $dataUrl = $obj['title'];
        }else{
            $dataUrl = $obj['pageURL'];
        }
    	
    	$obj['pageURL'] = $this->_filterUrl($dataUrl);
        
        $pageId = $obj['id'];
        $returnValue = parent::update($obj,$options);
        Manager::getService('UrlCache')->customDelete(array('pageId'=>$pageId),$options);
        return $returnValue;
    }
	
	public function findByNameAndSite($name,$siteId){
		$filterArray['site'] = $siteId;
        $filterArray['text'] = $name;
		return $this->_dataService->findOne($filterArray);
	}

    public function create (array $obj, $options = array('safe'=>true))
    {
        if(empty($obj['text'])){
            $obj['text'] = $obj['title'];
        }
        if(empty($obj['pageURL'])){
            $dataUrl = $obj['title'];
        }else{
            $dataUrl = $obj['pageURL'];
        }
    	
    	$obj['pageURL'] = $this->_filterUrl($dataUrl);
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
	    $url = mb_ereg_replace("[^A-Za-z0-9\.\-]","",$url);
	    $url = trim($url,'-');
	    
	    return $url;
	}
	public function deleteBySiteId($id)
	{
		return $this->_dataService->customDelete(array('site' => $id));
	}
	
}
