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
use RubedoAPI\Exceptions\APIAuthException;
use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Exceptions\APIRequestException;
use RubedoAPI\Rest\V1\AbstractResource;
use WebTales\MongoFilters\Filter;

/**
 * Class OrdersResource
 * @package RubedoAPI\Rest\V1\Ecommerce
 */
class OrdersResource extends AbstractResource
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->define();
    }

    /**
     * @param $params
     * @return array
     */
    public function getAction($params)
    {
        $user = $params['identity']->getUser();
        $filter = Filter::factory()
            ->addFilter(Filter::factory('Value')->setName('userId')->setValue($user['id']));
        $start=isset($params['start']) ? $params['start'] : 0;
        $limit=isset($params['limit']) ? $params['limit'] : null;
        $orders = $this->getOrdersCollection()->getList($filter, array(array('property' => 'createTime', 'direction' => 'desc')),$start,$limit);
        if (!empty($params["orderDetailPage"])) {
            $urlOptions = array(
                'encode' => true,
                'reset' => true
            );

            $orderDetailPageUrl = $this->getContext()->url()->fromRoute('rewrite', array(
                'pageId' => $params["orderDetailPage"],
                'locale' => $params['lang']->getLocale(),
            ), $urlOptions);
        }
        foreach ($orders['data'] as &$order) {
            $order = $this->maskOrderInList($order);
        }
        return array(
            'success' => true,
            'orders' => &$orders['data'],
            'total'=>&$orders['count'],
            'orderDetailPageUrl' => isset($orderDetailPageUrl) ? $orderDetailPageUrl : null,
        );
    }

    /**
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIAuthException
     * @throws \RubedoAPI\Exceptions\APIRequestException
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function postAction($params)
    {
        $pmConfig = $this->getConfigService()['paymentMeans'];
        if (!isset($pmConfig[$params['paymentMeans']])) {
            throw new APIRequestException('Unknown payment method', 400);
        }
        //$myPaymentMeans = $pmConfig[$params['paymentMeans']];

        if (isset($params['shoppingCartToken'])){
            $myCart = $this->getShoppingCartCollection()->getCurrentCart($params['shoppingCartToken']);
        } else {
            $myCart = $this->getShoppingCartCollection()->getCurrentCart();
        }

        if (empty($myCart)) {
            throw new APIAuthException('Shopping cart is empty', 404);
        }

        $currentUser = $this->getCurrentUserAPIService()->getCurrentUser();

        if (!(isset($currentUser['shippingAddress']) && isset($currentUser['shippingAddress']['country']))) {
            throw new APIAuthException('Missing shipping address country');
        }

        $order = array();
        $items = 0;
        foreach ($myCart as $value) {
            $items = $items + $value['amount'];
        }

        $myShippers = $this->getShippersCollection()->getApplicableShippers($currentUser['shippingAddress']['country'], $items);

        $shippingPrice = 0;
        $shippingTaxedPrice = 0;
        $shippingTax = 0;
        $shipperFound = false;
        $usedShipper = array();

        foreach ($myShippers['data'] as $shipper) {
            if ($shipper['shipperId'] == $params['shipperId']) {
                $shippingPrice = $shipper['rate'];
                $shippingTax = $shippingPrice * ($shipper['tax'] / 100);
                $shippingTaxedPrice = $shippingPrice + $shippingTax;
                $shipperFound = true;
                $usedShipper = $shipper;
                break;
            }
        }

        if (!$shipperFound) {
            throw new APIEntityException('Shipper not found', 404);
        }

        $order['detailedCart'] = $this->addCartInfos(
            $myCart,
            $currentUser['typeId'],
            $currentUser['shippingAddress']['country'],
            isset($currentUser['shippingAddress']['regionState']) ? $currentUser['shippingAddress']['regionState'] : "*",
            isset($currentUser['shippingAddress']['postCode']) ? $currentUser['shippingAddress']['postCode'] : "*"
        );

        if (isset($params['shippingComments'])) {
            $order['shippingComments'] = $params['shippingComments'];
        }
        $order['shippingPrice'] = $shippingPrice;
        $order['shippingTaxedPrice'] = $shippingTaxedPrice;
        $order['shippingTax'] = $shippingTax;
        $order['finalTFPrice'] = $order['detailedCart']['totalPrice'] + $order['shippingPrice'];
        $order['finalTaxes'] = $order['detailedCart']['totalTaxedPrice'] - $order['detailedCart']['totalPrice'] + $order['shippingTax'];
        $order['finalPrice'] = $order['detailedCart']['totalTaxedPrice'] + $order['shippingTaxedPrice'];
        $order['shipper'] = $usedShipper;
        $order['userId'] = $currentUser['id'];
        $order['userName'] = $currentUser['name'];
        $order['userEmail'] = $currentUser['email'];
        $order['billingAddress'] = $currentUser['billingAddress'];
        $order['shippingAddress'] = $currentUser['shippingAddress'];
        $order['hasStockDecrementIssues'] = false;
        $order['stockDecrementIssues'] = array();
        $order['paymentMeans'] = $params['paymentMeans'];
        $order['status'] = "pendingPayment";

        $registeredOrder = $this->getOrdersCollection()->createOrder($order);
        if ($registeredOrder['success']){
            if (isset($params['shoppingCartToken'])){
                $this->getShoppingCartCollection()->setCurrentCart(array(),$params['shoppingCartToken']);
            } else {
                $this->getShoppingCartCollection()->setCurrentCart(array());
            }
        }

        return array(
            'success' => $registeredOrder['success'],
            'order' => $registeredOrder['data'],
        );
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
            $value["productId"] = (string) $value["productId"];
            $value["variationId"] = (string) $value["variationId"];
            $myContent = $this->getContentsCollection()->findById($value['productId'], true, false);
            if ($myContent) {
                $value['title'] = $myContent['text'];
                if (isset($myContent["fields"]["image"])&&!empty($myContent["fields"]["image"])){
                    $value["image"]=$myContent["fields"]["image"];
                }
                $value['subtitle'] = '';
                $value['variationProperties'] = array();
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
                                $value['variationProperties'][$varkey] = $varvalue;
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
            'cart' => array_values($cart),
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
                && $offer['endDate'] >= $actualDate
                && $basePrice > $offer['price']
            ) {
                $basePrice = $offer['price'];
            }
        }
        return $basePrice;
    }

    /**
     * @param $id
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function getEntityAction($id, $params)
    {
        $user = $params['identity']->getUser();
        $filters = Filter::factory()
            ->addFilter(Filter::factory('Value')->setName('userId')->setValue($user['id']))
            ->addFilter(Filter::factory('Uid')->setValue($id));
        $order = $this->getOrdersCollection()->findOne($filters);

        if (empty($order)) {
            throw new APIEntityException('Order not found', 404);
        }
        return array(
            'success' => true,
            'order' => $order,
        );
    }

    /**
     * @param $order
     * @return array
     */
    public function maskOrderInList($order)
    {
        $mask = array('status', 'id', 'orderNumber', 'finalPrice','createTime','paymentMeans','shipper');
        return array_intersect_key($order, array_flip($mask));
    }

    /**
     *
     */
    protected function define()
    {
        $this
            ->definition
            ->setName('Orders')
            ->setDescription('Deal with Orders')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $this->defineGet($entity);
            })
            ->editVerb('post', function (VerbDefinitionEntity &$entity) {
                $this->definePost($entity);
            });

        $this
            ->entityDefinition
            ->setName('Order')
            ->setDescription('Deal with order')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $this->defineGetEntity($entity);
            });
    }

    /**
     * @param VerbDefinitionEntity $entity
     */
    protected function defineGet(VerbDefinitionEntity &$entity)
    {
        $entity
            ->setDescription('Get a list of orders')
            ->identityRequired()
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('start')
                    ->setDescription('Item\'s index number to start')
                    ->setFilter('int')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('limit')
                    ->setDescription('How much orders to return')
                    ->setFilter('int')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('orderDetailPage')
                    ->setDescription('Order details page')
                    ->setFilter('\MongoId')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Orders')
                    ->setKey('orders')
                    ->setRequired()
            )->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Total number of orders')
                    ->setKey('total')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Order details page url')
                    ->setKey('orderDetailPageUrl')
            );
    }

    /**
     * @param VerbDefinitionEntity $entity
     */
    protected function definePost(VerbDefinitionEntity &$entity)
    {
        $entity
            ->setDescription('Post new order')
            ->identityRequired()
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Payment mean')
                    ->setKey('paymentMeans')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Shipper')
                    ->setKey('shipperId')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Shipping comments')
                    ->setKey('shippingComments')
                    ->setFilter('string')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Shopping cart token')
                    ->setKey('shoppingCartToken')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Order')
                    ->setKey('order')
                    ->setRequired()
            );
    }

    /**
     * @param VerbDefinitionEntity $entity
     */
    protected function defineGetEntity(VerbDefinitionEntity &$entity)
    {
        $entity
            ->setDescription('Get an order')
            ->identityRequired()
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Order')
                    ->setKey('order')
                    ->setRequired()
            );
    }

}