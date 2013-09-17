<?php

/**
 * Rubedo -- ECM solution Copyright (c) 2013, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Cache;

use Rubedo\Services\Manager;
use Zend\Cache\Storage\Adapter\AbstractAdapter;
use Rubedo\Services\Events;


/**
 * Zend Cache in MongoDb
 *
 * @author jbourdin
 * @todo implement lifetime and cache names
 *      
 */
class MongoCache extends AbstractAdapter
{

    const CACHE_HIT = 'rubedo_cache_hit';

    const CACHE_MISS = 'rubedo_cache_miss';

    protected $_dataService;

    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->_dataService = Manager::getService('Cache');
    }

    /**
     * (non-PHPdoc) @see \Zend\Cache\Storage\Adapter\AbstractAdapter::internalGetItem()
     */
    protected function internalGetItem(&$normalizedKey, &$success = null, &$casToken = null)
    {
        $obj = $this->_dataService->findByCacheId($normalizedKey);
        
        if ($obj) {
            $success = true;
            Events::getEventManager()->trigger(self::CACHE_HIT, null, array(
                'key' => $normalizedKey
            ));
            return $obj['data'];
        } else {
            $success = false;
            Events::getEventManager()->trigger(self::CACHE_MISS, null, array(
                'key' => $normalizedKey
            ));
            return null;
        }
    }

    /**
     * (non-PHPdoc) @see \Zend\Cache\Storage\Adapter\AbstractAdapter::internalGetMetadata()
     */
    protected function internalGetMetadata(&$normalizedKey)
    {
        $obj = $this->_dataService->findByCacheId($normalizedKey);
        
        if ($obj) {
            unset($obj['data']);
            return $obj;
        } else {
            return null;
        }
    }

    /**
     * (non-PHPdoc) @see \Zend\Cache\Storage\Adapter\AbstractAdapter::internalRemoveItem()
     */
    protected function internalRemoveItem(&$normalizedKey)
    {
        return $this->_dataService->deleteByCacheId($normalizedKey);
    }

    /**
     * (non-PHPdoc) @see \Zend\Cache\Storage\Adapter\AbstractAdapter::internalSetItem()
     */
    protected function internalSetItem(&$normalizedKey, &$value)
    {
        $obj = array();
        $obj['data'] = $value;
        $obj['cacheId'] = $normalizedKey;
        
        $currentTimeService = \Rubedo\Services\Manager::getService('CurrentTime');
        $currentTime = $currentTimeService->getCurrentTime();
        
        $obj['createTime'] = $currentTime;
        $obj['lastUpdateTime'] = $currentTime;
        
        // if ($specificLifetime) {
        // $obj['expire'] = Manager::getService('CurrentTime')->getCurrentTime() + $specificLifetime;
        // } elseif ($this->getOption('lifetime')) {
        // $lifetime = $this->getOption('lifetime');
        // $obj['expire'] = Manager::getService('CurrentTime')->getCurrentTime() + $lifetime;
        // }
        
        return $this->_dataService->upsertByCacheId($obj, $normalizedKey);
    }

    public function clean()
    {
        return $this->_dataService->drop();
    }
}