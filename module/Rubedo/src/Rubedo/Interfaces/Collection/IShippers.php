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

/**
 * Interface of service handling Shippers
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
interface IShippers extends IAbstractCollection
{

    /**
     * Returns the shippers for the given country
     *
     * @param   string  $country    Country where we are searching shippers
     * @param   int     $items      Number of items to deliver
     * @return  array   Contains the shipper's data and their delivery price
     */
    public function getApplicableShippers($country, $items);

    /**
     * Find by ID and merge current country at root of array
     *
     * @param   string  $id         Shipper ID
     * @param   string  $country    Current country to check if the shipper is appropriate
     * @return  array   Shipper's data
     */
    public function findByIdAndWindApplicable($id, $country);

}
