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

use RubedoAPI\Rest\V1\AbstractResource;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use WebTales\MongoFilters\Filter;

class OrdersResource extends AbstractResource
{
    public function __construct()
    {
        parent::__construct();
        $this->define();
    }

    public function getAction($params)
    {
        $user = $params['identity']->getUser();
        $filter = Filter::factory()->addFilter(Filter::factory('Value')->setName('userId')->setValue($user['id']));
        $orders = $this->getOrdersCollection()->getList($filter, array(array('property' => 'createTime', 'direction' => 'desc')));
        if (!empty($params["orderDetailPage"])) {
            $urlOptions = array(
                'encode' => true,
                'reset' => true
            );

            $orderDetailPageUrl = $this->getContext()->url()->fromRoute('rewrite', array(
                'pageId' => $params["orderDetailPage"],
            ), $urlOptions);
        }
        foreach ($orders['data'] as &$order) {
            $order = $this->maskOrderInList($order);
        }
        return array(
            'success' => true,
            'orders' => &$orders['data'],
            'orderDetailPageUrl' => isset($orderDetailPageUrl)?$orderDetailPageUrl:null,
        );
    }

    public function maskOrderInList($order) {
        $mask = array('status', 'id', 'orderNumber', 'finalTFPrice');
        return array_intersect_key($order, array_flip($mask));
    }

    protected function define()
    {
        $this
            ->definition
            ->setName('Shippers')
            ->setDescription('Deal with Shippers')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $this->defineGet($entity);
            });
    }

    protected function defineGet(VerbDefinitionEntity &$entity)
    {
        $entity
            ->setDescription('Get a page and all blocks')
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
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Order details page url')
                    ->setKey('orderDetailPageUrl')
            );
    }
}