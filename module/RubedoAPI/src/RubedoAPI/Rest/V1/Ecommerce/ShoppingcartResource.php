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

namespace RubedoAPI\Rest\V1\Ecommerce;


use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Rest\V1\AbstractResource;

/**
 * Class ShoppingcartResource
 * @package RubedoAPI\Rest\V1\Ecommerce
 */
class ShoppingcartResource extends AbstractResource
{

    /**
     * {@inheritdoc}
     */
    function __construct()
    {
        parent::__construct();
        $this->define();
    }

    /**
     * Post action
     *
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function postAction($params)
    {
        $params['amount'] = isset($params['amount']) ? $params['amount'] : 1;
        if (empty($params['shoppingCartToken'])) {
            $cartUpdate = $this->getShoppingCartCollection()->addItemToCart($params['productId'], $params['variationId'], $params['amount']);
        } else {
            $cartUpdate = $this->getShoppingCartCollection()->addItemToCart($params['productId'], $params['variationId'], $params['amount'], $params['shoppingCartToken']);
        }
        if ($cartUpdate === false) {
            throw new APIEntityException('Update failed');
        }
        return array(
            'success' => true,
            'shoppingCart' => $this->filterShoppingCart($cartUpdate),
        );
    }

    /**
     * Delete action
     *
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function deleteAction($params)
    {
        $params['amount'] = isset($params['amount']) ? $params['amount'] : 1;
        if (empty($params['shoppingCartToken'])) {
            $cartUpdate = $this->getShoppingCartCollection()->removeItemFromCart($params['productId'], $params['variationId'], $params['amount']);
        } else {
            $cartUpdate = $this->getShoppingCartCollection()->removeItemFromCart($params['productId'], $params['variationId'], $params['amount'], $params['shoppingCartToken']);
        }
        if ($cartUpdate === false) {
            throw new APIEntityException('Update failed');
        }
        return array(
            'success' => true,
            'shoppingCart' => $this->filterShoppingCart($cartUpdate),
        );
    }

    /**
     * Get action
     *
     * @param $params
     * @return array
     */
    public function getAction($params)
    {
        if (empty($params['shoppingCartToken'])) {
            $shoppingCart = $this->getShoppingCartCollection()->getCurrentCart();
        } else {
            $shoppingCart = $this->getShoppingCartCollection()->getCurrentCart($params['shoppingCartToken']);
        }

        return array(
            'success' => true,
            'shoppingCart' => $shoppingCart,
        );
    }

    /**
     * Filter shopping cart
     *
     * @param $shoppingCart
     * @return array
     */
    protected function filterShoppingCart($shoppingCart)
    {
        $mask = array('id', 'shoppingCart', 'name');
        return array_intersect_key($shoppingCart, array_flip($mask));
    }

    /**
     * Define verbs
     */
    protected function define()
    {
        $this
            ->definition
            ->setName('Shopping Cart')
            ->setDescription('Use shopping cart')
            ->editVerb('post', function (VerbDefinitionEntity &$entity) {
                $this->defineEdition($entity);
                $entity->setDescription('Add item to cart');
            })
            ->editVerb('delete', function (VerbDefinitionEntity &$entity) {
                $this->defineEdition($entity);
                $entity->setDescription('Remove item to cart');
            })
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $this->defineGet($entity);
            });
    }

    /**
     * Define both post and delete filters
     *
     * @param VerbDefinitionEntity $entity
     */
    protected function defineEdition(VerbDefinitionEntity &$entity)
    {
        $entity
            ->setDescription('Edit item to cart')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Shopping cart token')
                    ->setKey('shoppingCartToken')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Product id')
                    ->setKey('productId')
                    ->setFilter('\MongoId')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Variation id')
                    ->setKey('variationId')
                    ->setFilter('\MongoId')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Amount')
                    ->setKey('amount')
                    ->setFilter('int')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Cart items')
                    ->setKey('shoppingCart')
                    ->setRequired()
            );
    }

    /**
     * Define get filters
     *
     * @param VerbDefinitionEntity $entity
     */
    protected function defineGet(VerbDefinitionEntity &$entity)
    {
        $entity
            ->setDescription('Get shopping cart')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Shopping cart token')
                    ->setKey('shoppingCartToken')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Cart items')
                    ->setKey('shoppingCart')
                    ->setRequired()
            );
    }
} 