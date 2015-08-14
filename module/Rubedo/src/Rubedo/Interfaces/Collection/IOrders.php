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
 * Interface of service handling Orders
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
interface IOrders extends IAbstractCollection
{

    /**
     * Create a new order
     *
     * @param   array       $orderData          Contain the order to create or update in database
     * @param   bool        $decrementStock     Decrement the current stock if it's true
     * @return  array       Contain the created/updated order from the database
     */
    public function createOrder($orderData,$decrementStock = true);

    /**
     * Return the unique order number for the given date
     *
     * @param   \DateTime   $dateCode   Contain the date of the order
     * @return mixed    Unique order number for this day
     */
    public function getIncrement($dateCode);

}
