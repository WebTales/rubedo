<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Mongo;

use \MongoCollection;
use Rubedo\Services\Events;
use Rubedo\Exceptions\Server;

/**
 * Proxy to MongoCollection
 *
 * Used to log and check query to MongoDB
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class ProxyCollection
{
    protected static $deniedMethod = array();

    const PRE_REQUEST = 'RubedoMongoProxyCollectionPreRequest';

    const POST_REQUEST = 'RubedoMongoProxyCollectionPostRequest';

    public $collection;

    public function __construct (MongoCollection $collection)
    {
        $this->collection = $collection;
    }

    public function __call ($function, array $args)
    {
        $callBack = array(
            $this->collection,
            $function
        );
        if (! is_callable($callBack) || in_array($function, self::$deniedMethod)) {
            throw new Server('Method not found');
        }
        
        $this->function = $function;
        $this->args = $args;
        
        Events::getEventManager()->trigger(static::PRE_REQUEST, $this);
        $result = call_user_func_array($callBack, $args);
        $this->result = $result;
        
        Events::getEventManager()->trigger(static::POST_REQUEST, $this);
        
        unset($this->function);
        unset($this->args);
        unset($this->args);
        
        return $result;
    }
}
