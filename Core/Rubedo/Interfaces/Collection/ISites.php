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
namespace Rubedo\Interfaces\Collection;

/**
 * Interface of service handling Sites
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface ISites extends IAbstractCollection
{

    /**
     * Return the host name of a site, given the ID or Site Array
     * 
     * @param string|array $site
     *            site Array or Site ID
     * @return string hostName
     */
    public function getHost ($site);

    /**
     * Return site matching host part of the URL
     * 
     * @param string $host            
     * @return array site data
     */
    public function findByHost ($host);
}
