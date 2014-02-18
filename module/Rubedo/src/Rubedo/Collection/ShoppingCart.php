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
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IShoppingCart;
use Rubedo\Services\Events;
use Rubedo\Services\Manager;
/**
 * Service to handle UserTypes
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 */
class ShoppingCart extends AbstractCollection implements IShoppingCart
{

    public function __construct()
    {
        $this->_collectionName = 'TemporaryShoppingCart';
        parent::__construct();
    }

    public function getCurrentCart () {
        $currentUser = Manager::getService("CurrentUser")->getCurrentUser();
        if (!$currentUser) {
            return array();
        }
        if ((isset($currentUser['shoppingCart']))&&(is_array($currentUser['shoppingCart']))) {
            return $currentUser['shoppingCart'];
        } else {
            return array();
        }

    }

    public function addItemToCart ($productId, $variationId, $amount=1) {
        if ((!isset($productId))||(!isset($variationId))){
            throw new \Rubedo\Exceptions\User('Product id and variation id missing');
        }
        $currentUser = Manager::getService("CurrentUser")->getCurrentUser();
        if (!$currentUser) {
            return false;
        }
        if ((!isset($currentUser['shoppingCart']))||(!is_array($currentUser['shoppingCart']))) {
            $currentUser['shoppingCart']=array();
        }
        $currentUser['shoppingCart'][]=array(
            "productId" => $productId,
            "variationId" => $variationId,
            "amount" => $amount
        );
        $updatedUser=Manager::getService("Users")->update($currentUser);
        if (!$updatedUser['success']) {
            return $updatedUser['success'];
        } else {
            return $updatedUser['data']['shoppingCart'];
        }
    }

}
