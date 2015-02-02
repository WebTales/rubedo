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

use Rubedo\Services\Manager;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Rest\V1\AbstractResource;

/**
 * Class ShippersResource
 * @package RubedoAPI\Rest\V1\Ecommerce
 */
class PaymentmeansResource extends AbstractResource
{
    /**
     * { @inheritdoc }
     */
    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Payment Means')
            ->setDescription('Deal with Payment Means')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get a list of active Payment Means')
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('Payment Means')
                            ->setKey('paymentMeans')
                            ->setRequired()
                    );
            });
    }

    /**
     * Get to ecommerce/paymentmeans
     *
     * @param $params
     * @throws \RubedoAPI\Exceptions\APIEntityException
     * @return array
     */
    public function getAction($params)
    {
        $paymentMeans=Manager::getService("PaymentConfigs")->getActivePMConfigs();
        foreach ($paymentMeans['data'] as &$pm){
            $pm=array_intersect_key($pm, array_flip(array("id","paymentMeans","displayName","logo")));
        }

        return array(
            'success' => true,
            'paymentMeans' => $paymentMeans['data'],
        );
    }
}