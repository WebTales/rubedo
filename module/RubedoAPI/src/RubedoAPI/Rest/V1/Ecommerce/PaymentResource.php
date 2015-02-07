<?php

namespace RubedoAPI\Rest\V1\Ecommerce;

use Rubedo\Services\Manager;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Exceptions\APIRequestException;
use RubedoAPI\Rest\V1\AbstractResource;

/**
 * Class PaymentsResource
 * @package RubedoAPI\Rest\V1\Ecommerce
 */
class PaymentResource extends AbstractResource
{
    /**
     * {@inheritdoc}
     */
    function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Payment')
            ->setDescription('Deal with payment')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Retrieve information for payment of an order')
                    ->identityRequired()
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('Id of the order to pay')
                            ->setKey('orderId')
                            ->setRequired()
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('Current user Url for return purposes')
                            ->setKey('currentUserUrl')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('Instructions to use for payment')
                            ->setKey('paymentInstructions')
                            ->setRequired()
                    );
            });
    }

    /**
     * Get to ecommerce/payment
     *
     * @param $params
     * @throws \RubedoAPI\Exceptions\APIRequestException
     * @return array
     */
    public function getAction($params)
    {
        $order=Manager::getService("Orders")->findById($params['orderId']);
        if (!$order){
            throw new APIRequestException('Order not found', 404);
        }
        $currentUser = $this->getCurrentUserAPIService()->getCurrentUser();
        if ($currentUser['id']!=$order['userId']){
            throw new APIRequestException('You have insufficient rights', 403);
        }
        if ($order['status']!='pendingPayment'){
            throw new APIRequestException('This order does not require payment', 404);
        }
        $pmConfig = $this->getConfigService()['paymentMeans'];
        if (!isset($pmConfig[$order['paymentMeans']])) {
            throw new APIRequestException('Unknown payment method', 400);
        }
        $myPaymentMeans = $pmConfig[$order['paymentMeans']];
        $paymentInstructions=Manager::getService($myPaymentMeans['service'])->getOrderPaymentData($order,isset($params['currentUserUrl']) ? $params['currentUserUrl'] : null);
        return array(
            'success' => true,
            'paymentInstructions' => $paymentInstructions,
        );
    }




}