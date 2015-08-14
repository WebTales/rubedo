<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Interfaces\Collection;

use Zend\EventManager\EventInterface;

/**
 * Interface of service handling users
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IUrlCache extends IAbstractCollection
{

    /**
     * Ensure that collection's indexes are applied
     */
    public function verifyIndexes();

    /**
     * Return cached URL for a given PageId
     *
     * @param string    $pageId     page ID
     * @param string    $locale     Current language
     *
     * @return array    Contains the page's data
     */
    public function findByPageId($pageId, $locale);

    /**
     * Return cached URL for a given URL
     *
     * @param   string  $url        URL of the cached page
     * @param   string  $siteId     Site ID
     * @return  array   Contains    the page's data
     */
    public function findByUrl($url, $siteId);

    /**
     * @param EventInterface $event
     * @return mixed
     */
    public function urlToPageReadCacheEvent(EventInterface $event);

    /**
     * @param EventInterface $event
     * @return mixed
     */
    public function pageToUrlReadCacheEvent(EventInterface $event);

    /**
     * @param EventInterface $event
     * @return mixed
     */
    public function urlToPageWriteCacheEvent(EventInterface $event);

}
