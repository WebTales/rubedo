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
namespace Rubedo\Interfaces\Collection;

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
     * Return cached URL for a given PageId
     * 
     * @param string $pageId
     *            page ID
     * @return array
     */
    public function findByPageId ($pageId);
    
    /**
     * @param string $url
     * @param string $siteid
     * @return array
     */
    public function findByUrl ($url, $siteid);
}
