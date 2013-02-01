<?php

/**
 * Rubedo -- ECM solution Copyright (c) 2012, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Cache;

Use Rubedo\Services\Manager;

/**
 * Zend Cache Backend in MongoDb
 *
 * @author jbourdin
 *        
 */
class MongoCache extends \Zend_Cache_Backend implements \Zend_Cache_Backend_Interface
{

    /**
     * Constructor
     *
     * @param array $options
     *            associative array of options
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function __construct (array $options = array())
    {
        parent::__construct($options);
        $this->_dataService = Manager::getService('Cache');
    }

    /**
     * Test if a cache is available for the given id and (if yes) return it
     * (false else)
     *
     * Note : return value is always "string" (unserialization is done by the
     * core not by the backend)
     *
     * @param string $id
     *            Cache id
     * @param boolean $doNotTestCacheValidity
     *            If set to true, the cache validity won't be tested
     * @return string false datas
     */
    public function load ($id, $doNotTestCacheValidity = false)
    {
        if (! $doNotTestCacheValidity) {
            $time = Manager::getService('CurrentTime')->getCurrentTime();
        } else {
            $time = null;
        }
        
        $obj = $this->_dataService->findByCacheId($id, $time);
        
        if ($obj) {
            return $obj['data'];
        } else {
            return false;
        }
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param string $id
     *            cache id
     * @return mixed false cache is not available) or "last modified" timestamp
     *         (int) of the available cache record
     */
    public function test ($id)
    {
        throw new \Rubedo\Exceptions\Server('not yet implemented');
        
    }

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param string $data
     *            Datas to cache
     * @param string $id
     *            Cache id
     * @param array $tags
     *            Array of strings, the cache record will be tagged by each
     *            string entry
     * @param int $specificLifetime
     *            If != false, set a specific lifetime for this cache record
     *            (null => infinite lifetime)
     * @return boolean true if no problem
     */
    public function save ($data, $id, $tags = array(), $specificLifetime = false)
    {
        $obj = array();
        $obj['data'] = $data;
        $obj['cacheId'] = $id;
        $obj['tags'] = $tags;
        if ($specificLifetime) {
            $obj['expire'] = Manager::getService('CurrentTime')->getCurrentTime() + $specificLifetime;
        } elseif ($this->getOption('lifetime')) {
            $lifetime = $this->getOption('lifetime');
            $obj['expire'] = Manager::getService('CurrentTime')->getCurrentTime() + $lifetime;
        }
        
        return $this->_dataService->upsertByCacheId($obj,$id);
    }

    /**
     * Remove a cache record
     *
     * @param string $id
     *            Cache id
     * @return boolean True if no problem
     */
    public function remove ($id)
    {
       return $this->_dataService->deleteByCacheId($id);
    }

    /**
     * Clean some cache records
     *
     * Available modes are :
     * Zend_Cache::CLEANING_MODE_ALL (default) => remove all cache entries
     * ($tags is not used)
     * Zend_Cache::CLEANING_MODE_OLD => remove too old cache entries ($tags is
     * not used)
     * Zend_Cache::CLEANING_MODE_MATCHING_TAG => remove cache entries matching
     * all given tags
     * ($tags can be an array of strings or a single string)
     * Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG => remove cache entries not
     * {matching one of the given tags}
     * ($tags can be an array of strings or a single string)
     * Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG => remove cache entries
     * matching any given tags
     * ($tags can be an array of strings or a single string)
     *
     * @param string $mode
     *            Clean mode
     * @param array $tags
     *            Array of tags
     * @return boolean true if no problem
     */
    public function clean ($mode = \Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {
        $updateCond = array();
        
        $options = array();
        $options['safe'] = true;
        
        switch ($mode) {
            case \Zend_Cache::CLEANING_MODE_MATCHING_TAG:
            case \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
            case \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
                throw new \Rubedo\Exceptions\Server('not yet implemented');
                break;
            case \Zend_Cache::CLEANING_MODE_OLD:
               return $this->_dataService->deleteExpired();
                break;
            default:
                return $this->_dataService->drop();
                break;
        }
    }
    
}