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
namespace Rubedo\Services;

Use Rubedo\Cache\MongoCache;

/**
 * Cache manager
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Cache
{

    /**
     * array of current service parameters
     *
     * @var array
     */
    protected static $_cacheOptions;

    /**
     * Setter of services parameters, to init them from bootstrap
     *
     * @param array $options            
     */
    public static function setOptions ($options)
    {
        self::$_cacheOptions = $options;
    }

    /**
     * getter of services parameters, to init them from bootstrap
     *
     * @return array array of all the services
     */
    public static function getOptions ()
    {
        return self::$_cacheOptions;
    }

    /**
     * Public static method to get an instance of the cache
     *
     * @param string $cacheName
     *            name of the cache called
     * @return \Zend_Cache instance of the cache
     */
    public static function getCache ($cacheName)
    {
        $frontendOptions = array(
            'lifetime' => 7200,
            'automatic_serialization' => true
        );
        
        $backendOptions = array(
            'cacheName' => $cacheName
        );
        
        $cache = \Zend_Cache::factory('Core', new MongoCache(), $frontendOptions, $backendOptions);
        
        return $cache;
    }
}
