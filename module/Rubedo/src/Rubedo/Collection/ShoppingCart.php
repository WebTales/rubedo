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
namespace Rubedo\Collection;

use Rubedo\Exceptions\User;
use Rubedo\Interfaces\Collection\IShoppingCart;
use Rubedo\Services\Manager;
use Zend\Http\Header\SetCookie;

/**
 * Service to handle shopping cart
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 */
class ShoppingCart extends AbstractCollection implements IShoppingCart
{
    /**
     * @var \Rubedo\Interfaces\User\ICurrentUser
     */
    protected $currentUserService;

    /**
     * @var \Rubedo\Interfaces\Collection\IUsers
     */
    protected $usersService;

    /**
     * @var \Zend\Http\PhpEnvironment\Request
     */
    protected $requestService;

    /**
     * @var \Zend\Http\PhpEnvironment\Response
     */
    protected $responseService;

    const COOKIE = 'rubedoShoppingCart';
    const KEY = 'shoppingCart';

    public function __construct()
    {
        $this->_collectionName = 'TemporaryShoppingCart';
        $this->currentUserService = Manager::getService("CurrentUser");
        $this->usersService = Manager::getService("Users");
        $this->requestService = Manager::getService('Request');
        $this->responseService = Manager::getService('Response');
        parent::__construct();
    }

    public function getCurrentCart()
    {
        $currentUser = $this->getCurrentUser();
        if ($currentUser && $this->hasShoppingCart($currentUser)) {
            return $currentUser[static::KEY];
        } elseif ($this->hasCookie()) {
            $tempCart = $this->findById($this->getCookie(), true);
            return $tempCart?$tempCart[static::KEY]:array();
        } else {
            return array();
        }
    }

    public function setCurrentCart($cart)
    {
        $currentUser = $this->getCurrentUser();
        if ($currentUser && $this->hasShoppingCart($currentUser)) {
            $currentUser[static::KEY] = $cart;
            return $this->usersService->update($currentUser);
        } else {
            $tempCart = null;
            $new = false;
            if ($this->hasCookie()) {
                $tempCart = $this->findById($this->getCookie());
            }
            if (!$tempCart) {
                $new = true;
                $tempCart = array();
            }
            $tempCart[static::KEY] = $cart;
            if ($new) {
                $result = $this->create($tempCart);
            } else {
                $result = $this->update($tempCart);
            }
            $this->setCookie($result['data']['id']);
            return $result;
        }
    }

    protected function hasShoppingCart($array)
    {
        return isset($array[static::KEY]) && is_array($array[static::KEY]);
    }

    public function addItemToCart($productId, $variationId, $amount = 1)
    {
        if (!isset($productId, $variationId)) {
            throw new User('Product id or variation id missing');
        }

        $cart = $this->getCurrentCart();
        $cart = $this->editItemToArray($cart, $productId, $variationId, $amount);
        $update = $this->setCurrentCart($cart);

        return !$update['success']?$update['success']:$update['data'][static::KEY];
    }

    protected function editItemToArray($cart, $productId, $variationId, $amount = 1) {
        $match = false;
        foreach ($cart as $key => &$value) {
            if (!$match && $value['productId'] == $productId && $value['variationId'] == $variationId) {
                $value['amount'] += $amount;
                if ($value['amount'] < 1) {
                    unset($cart[$key]);
                }
                $match = true;
                break;
            }
        }
        if (!$match) {
            $cart[] = array(
                "productId" => $productId,
                "variationId" => $variationId,
                "amount" => $amount
            );
        }
        return $cart;
    }



    public function removeItemFromCart($productId, $variationId, $amount = 1)
    {
        $cart = $this->getCurrentCart();
        $cart = $this->editItemToArray($cart, $productId, $variationId, - $amount);
        $update = $this->setCurrentCart($cart);
        return !$update['success']?$update['success']:$update['data'][static::KEY];
    }

    /**
     * get current user
     *
     * @return array
     */
    protected function getCurrentUser() {
        return $this->currentUserService->getCurrentUser();
    }

    /**
     * has cookie
     *
     * @return boolean
     */
    protected function hasCookie() {
        $cookies = $this->requestService->getCookie();
        return $cookies?$cookies->offsetExists(static::COOKIE):false;
    }

    /**
     * get cookie
     *
     * @return string
     */
    protected function getCookie() {
        return $this->requestService->getCookie()->offsetGet(static::COOKIE);
    }

    protected function setCookie($value) {
        $cookie = new SetCookie(static::COOKIE, $value, time() + 3600 * 24 * 30, '/');
        return $this->responseService->getHeaders()->addHeader($cookie);
    }
}
