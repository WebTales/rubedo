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
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\ITinyUrl, WebTales\MongoFilters\Filter;

/**
 * Service to handle TinyUrl
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class TinyUrl extends AbstractCollection implements ITinyUrl
{

    protected $_indexes = array(
        array(
            'keys' => array(
                'expire' => 1
            ),
            'options' => array(
                'expireAfterSeconds' => 172800
            )
        )
    );

    public function __construct ()
    {
        $this->_collectionName = 'TinyUrl';
        parent::__construct();
    }

    /**
     * find a tinyUrl object base on URL argument
     *
     * @param string $url            
     * @return array
     */
    public function findByUrl ($url)
    {
        $cond = array();
        $cond['url'] = $url;
        return $this->_dataService->findOne($cond);
    }

    /**
     * Create a tinyUrl object base on URL argument
     *
     * return created object ID
     *
     * @param string $url            
     * @return string
     */
    public function createUrlAlias ($url, $expire = false)
    {
        $tinyUrlObj = $this->findByUrl($url);
        
        if ($expire || ! $tinyUrlObj) {
            $obj = array();
            $obj['url'] = $url;
            if ($expire) {
                $obj['expire'] = new \MongoDate();
            }
            $result = $this->create($obj);
            $tinyUrlObj = $result['data'];
        }
        $generatedKey = $tinyUrlObj['id'];
        
        return $generatedKey;
    }

    /**
     * find a tinyUrl object base on MVC context
     *
     * @param string $action            
     * @param string $controller            
     * @param string $module            
     * @param array $params            
     * @return array
     */
    public function findByParameters ($action, $controller, $module, $params)
    {
        $cond = Filter::Factory();
        $cond->addFilter(Filter::Factory('Value')->setName('action')
            ->setValue($action));
        $cond->addFilter(Filter::Factory('Value')->setName('controller')
            ->setValue($controller));
        $cond->addFilter(Filter::Factory('Value')->setName('module')
            ->setValue($module));
        
        foreach ($params as $key => $value) {
            $cond->addFilter(Filter::Factory('Value')->setName('params.' . $key)
                ->setValue($value));
        }
        
        return $this->_dataService->findOne($cond);
    }

    /**
     * create a tinyUrl object base on MVC context
     *
     * @param string $action            
     * @param string $controller            
     * @param string $module            
     * @param array $params            
     * @return string
     */
    public function createFromParameters ($action, $controller, $module, $params = array(), $expire = true)
    {
        $tinyUrlObj = $this->findByParameters($action, $controller, $module, $params);
        if ($expire || ! $tinyUrlObj) {
            $obj = array();
            $obj['params'] = $params;
            $obj['controller'] = $controller;
            $obj['action'] = $action;
            $obj['module'] = $module;
            if ($expire) {
                $obj['expire'] = new \MongoDate();
            }
            $result = $this->create($obj);
            $tinyUrlObj = $result['data'];
        }
        $generatedKey = $tinyUrlObj['id'];
        return $generatedKey;
    }

    /**
     * Create an access link to download a document
     *
     * @param string $damId            
     * @return string
     */
    public function creamDamAccessLinkKey ($damId)
    {
        $action = 'index';
        $controller = "dam";
        $module = "default";
        $params = array(
            'media-id' => $damId,
            'attachment' => "download"
        );
        return $this->createFromParameters($action, $controller, $module, $params, true);
    }
}
