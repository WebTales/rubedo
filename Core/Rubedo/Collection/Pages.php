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

use Rubedo\Interfaces\Collection\IPages,Rubedo\Services\Manager;

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
	    return $this->_dataService->findOne(array('text'=>$urlSegment,'parentId'=>$parentId,'site'=>$siteId));
	}
	

	/* (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy (array $obj, $safe = true)
    {
        $pageId = $obj['id'];
        $returnValue = parent::destroy($obj,$safe);
        Manager::getService('UrlCache')->customDelete(array('pageId'=>$pageId),$safe);
        return $returnValue;
    }

	/* (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $safe = true)
    {
        $pageId = $obj['id'];
        $returnValue = parent::update($obj,$safe);
        Manager::getService('UrlCache')->customDelete(array('pageId'=>$pageId),$safe);
        return $returnValue;
    }
	
	public function findByNameAndSite($name,$siteId){
		$filterArray['site'] = $siteId;
        $filterArray['text'] = $name;
		return $this->_dataService->findOne($filterArray);
	}

	
	
	
}
