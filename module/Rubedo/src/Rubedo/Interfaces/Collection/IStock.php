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
 * Interface of service handling Stock
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
interface IStock extends IAbstractCollection
{

    /**
     * Get stock from products using aggregation
     *
     * @param   string  $typeId             Correspond to the contentType ID
     * @param   string  $workingLanguage    Language to retrieve in products
     * @return  array   Returns the products and their stock
     */
    public function getStock($typeId, $workingLanguage);

    /**
     * Add a specific amount to stock of a product variation
     *
     * @param   string  $productId      The stock will be updated on this product
     * @param   string  $variationId    More precisely on this variation of the product
     * @param   int     $amount         Amount of products to add to the current stock
     * @return  array   Contains the new stock amount
     */
    public function increaseStock($productId, $variationId, $amount);

    /**
     * Remove a specific amount from stock of a product variation
     *
     * @param   string  $productId      The stock will be updated on this product
     * @param   string  $variationId    More precisely on this variation of the product
     * @param   int     $amount         Amount of products to remove from stock
     * @return  array   Contains the new stock amount
     */
    public function decreaseStock($productId, $variationId, $amount);

}
