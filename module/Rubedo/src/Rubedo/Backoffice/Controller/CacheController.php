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
namespace Rubedo\Backoffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;
use Rubedo\Services\Cache;
use Zend\View\Model\JsonModel;

/**
 * Controller providing control over the cached contents
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class CacheController extends AbstractActionController
{

    /**
     * cache object
     *
     * @var Zend_Cache
     */
    protected $_cache;

    /**
     * The default read Action
     *
     * Return the content of the collection, get filters from the request
     * params, get sort from request params
     */
    public function indexAction ()
    {
        $countArray = array();
        $countArray['cachedItems'] = Manager::getService('Cache')->count();
        $countArray['cachedUrl'] = Manager::getService('UrlCache')->count();
        return new JsonModel($countArray);
    }

    public function clearAction ()
    {
        $countArray = array();
        $countArray['Cached items'] = Cache::getCache()->clean();
        if (Manager::getService('UrlCache')->count() > 0) {
            $countArray['Cached Url'] = Manager::getService('UrlCache')->drop();
        } else {
            $countArray['Cached Url'] = true;
        }
        return new JsonModel($countArray);
    }
}
