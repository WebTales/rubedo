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
use Rubedo\Services\Manager, Rubedo\Services\Cache;

/**
 * Controller providing control over the cached contents
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class Backoffice_CacheController extends Zend_Controller_Action
{

    /**
     * cache object
     *
     * @var Zend_Cache
     */
    protected $_cache;

    public function init ()
    {
        // $this->_cache = Rubedo\Services\Cache::getCache();
    }

    /**
     * The default read Action
     *
     * Return the content of the collection, get filters from the request
     * params, get sort from request params
     */
    public function indexAction ()
    {
        $countArray = array();
        $countArray['Cached items'] = Manager::getService('Cache')->count();
        $countArray['Cached Url'] = Manager::getService('UrlCache')->count();
        $this->_helper->json($countArray);
    }

    public function clearAction ()
    {
        $countArray = array();
        $countArray['Cached items']=Cache::getCache()->clean();
        if (Manager::getService('UrlCache')->count() > 0) {
            $countArray['Cached Url'] = Manager::getService('UrlCache')->drop();
        }else{
            $countArray['Cached Url'] = true;
        }
        $this->_helper->json($countArray);
    }
}
