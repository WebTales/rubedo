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
use Rubedo\Interfaces\Collection\ITinyUrl;

/**
 * Service to handle TinyUrl
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class TinyUrl extends AbstractCollection implements ITinyUrl
{

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
    public function createUrlAlias ($url, $email = false, $expire = false)
    {
        $tinyUrlObj = $this->findByUrl($url);
        
        if ($expire || ! $tinyUrlObj) {
            $obj = array();
            $obj['url'] = $url;
            $result = $this->create($obj);
            $tinyUrlObj = $result['data'];
        }
        $generatedKey = $tinyUrlObj['id'];
        
        return $generatedKey;
    }

    public function findByParameters ($action, $controller, $module, $params, 
            $email)
    {
        $cond = array();
        $cond['action'] = $action;
        $cond['controller'] = $controller;
        $cond['module'] = $module;
        foreach ($params as $key => $value) {
            $cond['params.' . $key] = $value;
        }
        if ($email) {
            $cond['email'] = $email;
        }
        return $this->_dataService->findOne($cond);
    }

    public function createFromParameters ($action, $controller, $module, 
            $params = array(), $email = false, $expire = false)
    {
        $tinyUrlObj = $this->findByParameters($action, $controller, $module, 
                $params, $email);
        if ($expire || ! $tinyUrlObj) {
            $obj = array();
            $obj['params'] = $params;
            $obj['controller'] = $controller;
            $obj['action'] = $action;
            $obj['module'] = $module;
            $result = $this->tinyUrlService->create($obj);
            $tinyUrlObj = $result['data'];
        }
        $generatedKey = $tinyUrlObj['id'];
        return $generatedKey;
    }
}
