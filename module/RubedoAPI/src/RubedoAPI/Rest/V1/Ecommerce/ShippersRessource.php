<?php

namespace RubedoAPI\Rest\V1\Ecommerce;

use Rubedo\Services\Manager;
use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Rest\V1\AbstractRessource;
use RubedoAPI\Tools\FilterDefinitionEntity;
use RubedoAPI\Tools\VerbDefinitionEntity;

class ShippersRessource extends AbstractRessource {
    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Shippers')
            ->setDescription('Deal with Shippers')
            ->editVerb('get', function(VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get a page and all blocks')
                    ->editInputFilter('access_token', function(FilterDefinitionEntity &$filter) {
                        $filter
                            ->setRequired()
                        ;
                    })
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('Shippers')
                            ->setKey('shippers')
                            ->setRequired()
                    )
                ;
            })
        ;
    }

    /**
     * @param $params
     * @throws \RubedoAPI\Exceptions\APIEntityException
     * @return array
     */
    public function getAction($params)
    {
        $user = $params['identity']->getUser();
        if (!isset($user['shippingAddresse']) || !isset($user['shippingAddresse']['country']))
            throw new APIEntityException('User\'s country is mandatory');

        $items = 0;
        $myCart = Manager::getService("ShoppingCart")->getCurrentCart();

        foreach ($myCart as $value) {
            $items = $items + $value['amount'];
        }
        $myShippers = Manager::getService("Shippers")->getApplicableShippers($user['shippingAddress']['country'], $items);
        if (empty($myShippers))
            throw new APIEntityException('No shippers', 404);

        return array(
            'success' => true,
            'shippers' => $myShippers,
        );
    }
}