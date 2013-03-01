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

use Rubedo\Interfaces\Collection\IUrlCache;

/**
 * Service to handle Users
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class UrlCache extends AbstractCollection implements IUrlCache
{
    protected $_indexes = array(
        array('keys'=>array('siteId'=>1,"url"=>1),'options'=>array('unique'=>true)),
        array('keys'=>array('siteId'=>1,"pageId"=>1),'options'=>array('unique'=>true)),
    
    );
    
    /**
     * Set the collection name
     */
    public function __construct ()
    {
        $this->_collectionName = 'UrlCache';
        parent::__construct();        
    }
    
    public function verifyIndexes(){
        $this->_dataService->ensureIndex(array('url'=>1,'siteId'=>1),array('unique'=>true));
        $this->_dataService->ensureIndex(array('date'=>1),array('expireAfterSeconds'=>600));
    }

  
    /* (non-PHPdoc)
     * @see \Rubedo\Interfaces\Collection\IUrlCache::findByPageId()
     */
    public function findByPageId ($pageId)
    {
        return $this->_dataService->findOne(array(
            'pageId' => $pageId
        ));
    }
    

    /* (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create (array $obj, $options = array('safe'=>false))
    {
        $obj['date'] = $this->_dataService->getMongoDate();
        
        parent::create($obj,$options);       
    }

	/* (non-PHPdoc)
	 * @see \Rubedo\Interfaces\Collection\IUrlCache::findByUrl()
	 */
	public function findByUrl ($url, $siteId)
    {
        return $this->_dataService->findOne(array(
            'url' => $url,
            'siteId' => $siteId
        ));
    }
}
