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
 * Interface of service handling Countries
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
interface ICountries extends IAbstractCollection
{

    /**
     * Return the country (language object) stored in database
     *
     * @param   string  $alpha2     Requested country
     * @return  array               Contain the country object from Mongo
     */
    public function findOneByAlpha2($alpha2);

}
