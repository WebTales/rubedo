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
 * Interface of service handling TinyUrl
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface ITinyUrl extends IAbstractCollection{
	
    /**
     * find a tinyUrl object base on URL argument
     *
     * @param string $url
     * @return array
     */
    public function findByUrl($url);
    
    /**
     * Create a tinyUrl object base on URL argument
     *
     * return created object ID
     *
     * @param string $url
     * @return string
     */
    public function createUrlAlias($url);
    
    
}
