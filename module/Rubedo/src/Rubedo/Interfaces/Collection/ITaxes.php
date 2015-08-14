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
 * Interface of service handling Taxes
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
interface ITaxes extends IAbstractCollection
{

    /**
     * Calculate the taxed price from the base price depending upon the customer's delivery address
     *
     * @param   string  $productTypeId  Product type ID
     * @param   string  $userTypeId     User type ID
     * @param   string  $country        Customer's country
     * @param   string  $region         Customer's region
     * @param   string  $postalCode     Customer's postal code
     * @param   string  $basePrice      Price of the order without the country taxes
     * @return  float   Price including taxes
     */
    public function getTaxValue($productTypeId, $userTypeId, $country, $region, $postalCode, $basePrice);

}
