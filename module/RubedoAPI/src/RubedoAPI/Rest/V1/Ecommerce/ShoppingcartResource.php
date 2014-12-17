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
            'shoppingCart' => $this->filterShoppingCart($shoppingCart),
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
        $userTypeId="*";
        $country="*";
        $region="*";
        $postalCode="*";
        $currentUser = $this->getCurrentUserAPIService()->getCurrentUser();
        if($currentUser){
            $userTypeId=$currentUser['typeId'];
            if (isset($currentUser['shippingAddress']['country'])&&!empty($currentUser['shippingAddress']['country'])){
                $country=$currentUser['shippingAddress']['country'];
            }
            if (isset($currentUser['shippingAddress']['regionState'])&&!empty($currentUser['shippingAddress']['regionState'])){
                $region=$currentUser['shippingAddress']['regionState'];
            }
            if (isset($currentUser['shippingAddress']['postCode'])&&!empty($currentUser['shippingAddress']['postCode'])){
                $postalCode=$currentUser['shippingAddress']['postCode'];
            }
        }
        return ($this->addCartInfos($shoppingCart,$userTypeId, $country, $region, $postalCode));
    }

    /**
     * @param $cart
     * @param $userTypeId
     * @param $country
     * @param $region
     * @param $postalCode
     * @return array
     */
    protected function addCartInfos($cart, $userTypeId, $country, $region, $postalCode)
    {
        $totalPrice = 0;
        $totalTaxedPrice = 0;
        $totalItems = 0;
        $ignoredArray = array('price', 'amount', 'id', 'sku', 'stock', 'basePrice', 'specialOffers');
        foreach ($cart as &$value) {
            $myContent = $this->getContentsCollection()->findById($value['productId'], true, false);
            if ($myContent) {
                $value['title'] = $myContent['text'];
                $value['subtitle'] = '';
                $unitPrice = 0;
                $taxedPrice = 0;
                $unitTaxedPrice = 0;
                $price = 0;
                foreach ($myContent['productProperties']['variations'] as $variation) {
                    if ($variation['id'] == $value['variationId']) {
                        if (array_key_exists('specialOffers', $variation)) {
                            $variation["price"] = $this->getBetterSpecialOffer($variation['specialOffers'], $variation["price"]);
                            $value['unitPrice'] = $variation["price"];
                        }
                        $unitPrice = $variation['price'];
                        $unitTaxedPrice = $this->getTaxesCollection()->getTaxValue($myContent['typeId'], $userTypeId, $country, $region, $postalCode, $unitPrice);
                        $price = $unitPrice * $value['amount'];
                        $taxedPrice = $unitTaxedPrice * $value['amount'];
                        $totalTaxedPrice = $totalTaxedPrice + $taxedPrice;
                        $totalPrice = $totalPrice + $price;
                        $totalItems = $totalItems + $value['amount'];
                        foreach ($variation as $varkey => $varvalue) {
                            if (!in_array($varkey, $ignoredArray)) {
                                $value['subtitle'] .= ' ' . $varvalue;
                            }
                        }
                    }
                }
                $value['price'] = $price;
                $value['unitPrice'] = $unitPrice;
                $value['unitTaxedPrice'] = $unitTaxedPrice;
                $value['taxedPrice'] = $taxedPrice;
            }
        }
        return array(
            'cart' => $cart,
            'totalPrice' => $totalPrice,
            'totalTaxedPrice' => $totalTaxedPrice,
            'totalItems' => $totalItems
        );
    }

    /**
     * @param $offers
     * @param $basePrice
     * @return mixed
     */
    protected function getBetterSpecialOffer($offers, $basePrice)
    {
        $actualDate = new \DateTime();
        foreach ($offers as $offer) {
            $beginDate = $offer['beginDate'];
            $endDate = $offer['endDate'];
            $offer['beginDate'] = new \DateTime();
            $offer['beginDate']->setTimestamp($beginDate);
            $offer['endDate'] = new \DateTime();
            $offer['endDate']->setTimestamp($endDate);
            if (
                $offer['beginDate'] <= $actualDate
                && $offer['beginDate'] <= $actualDate
                && $basePrice > $offer['price']
            ) {
                $basePrice = $offer['price'];
            }
        }
        return $basePrice;
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