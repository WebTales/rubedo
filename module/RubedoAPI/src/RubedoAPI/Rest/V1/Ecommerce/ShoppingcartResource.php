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

class ShoppingcartResource extends AbstractResource {

    function __construct()
    {
        parent::__construct();
        $this->define();
    }

    public function postAction($params)
    {
        $params['amount'] = isset($params['amount'])?$params['amount']:1;
        $cartUpdate = $this->getShoppingCartCollection()->addItemToCart($params['productId'], $params['variationId'], $params['amount']);
        if ($cartUpdate === false) {
            throw new APIEntityException('Update failed');
        }
    }

    public function deleteAction($params)
    {
        $params['amount'] = isset($params['amount'])?$params['amount']:1;
        $cartUpdate = $this->getShoppingCartCollection()->removeItemFromCart($params['productId'], $params['variationId'], $params['amount']);
        if ($cartUpdate === false) {
            throw new APIEntityException('Update failed');
        }
    }

    protected function define()
    {
        $this
            ->definition
            ->setName('Shopping Cart')
            ->setDescription('Use shopping cart')
            ->editVerb('post', function (VerbDefinitionEntity &$entity) {
                $this->defineEdition($entity);
            })
            ->editVerb('delete', function (VerbDefinitionEntity &$entity) {
                $this->defineEdition($entity);
            });
    }

    protected function defineEdition (VerbDefinitionEntity &$entity)
    {
        $entity
            ->setDescription('Add item to cart')
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
                    ->setKey('cartItems')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Total amount')
                    ->setKey('totalAmount')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Total items')
                    ->setKey('totalItems')
            );
    }
} 