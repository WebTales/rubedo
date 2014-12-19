<?php

namespace RubedoAPI\Collection;

use RubedoAPI\Traits\LazyServiceManager;

class ShoppingCart extends \Rubedo\Collection\ShoppingCart
{
    use LazyServiceManager;

    public function getCurrentCart($shoppingCartToken = null)
    {
        $currentUser = $this->getCurrentUser();
        if ($currentUser && $this->hasShoppingCart($currentUser)) {
            return $currentUser[static::KEY];
        }

        if (empty($shoppingCartToken)) {
            return array();
        }

        $tempCart = $this->findById($shoppingCartToken, true);
        return $tempCart ? $tempCart : array();
    }

    public function setCurrentCart($cart, $shoppingCartToken = null)
    {
        $currentUser = $this->getCurrentUser();
        if ($currentUser && $this->hasShoppingCart($currentUser)) {
            $currentUser[static::KEY] = $cart;
            return $this->usersService->update($currentUser);
        } else {
            $tempCart = null;
            $new = false;
            if ($shoppingCartToken) {
                $tempCart = $this->findById($shoppingCartToken);
            }
            if (!$tempCart) {
                $new = true;
                $tempCart = array();
            }
            $tempCart[static::KEY] = $cart;
            if ($new) {
                return $this->create($tempCart);
            }
            return $this->update($tempCart);
        }
    }

    public function setToUser($shoppingCartToken)
    {
        $currentUser = $this->getCurrentUser();
        $currentUser[static::KEY] = $this->findById($shoppingCartToken)[static::KEY];
        return $this->usersService->update($currentUser);
    }

    public function addItemToCart($productId, $variationId, $amount = 1, $shoppingCartToken = null)
    {
        $cart = $this->getCurrentCart($shoppingCartToken);
        $cart = $this->editItemToArray($cart, $productId, $variationId, $amount);
        $update = $this->setCurrentCart($cart, $shoppingCartToken);

        return !$update['success'] ? $update['success'] : $update['data'];
    }

    public function removeItemFromCart($productId, $variationId, $amount = 1, $shoppingCartToken = null)
    {
        $cart = $this->getCurrentCart($shoppingCartToken);
        $cart = $this->editItemToArray($cart, $productId, $variationId, -$amount);
        $update = $this->setCurrentCart($cart, $shoppingCartToken);

        return !$update['success'] ? $update['success'] : $update['data'];
    }
}