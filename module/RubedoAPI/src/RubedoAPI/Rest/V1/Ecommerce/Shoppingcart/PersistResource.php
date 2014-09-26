<?php

namespace RubedoAPI\Rest\V1\Ecommerce\Shoppingcart;

use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Rest\V1\AbstractResource;

/**
 * Class PersistResource
 * @package RubedoAPI\Rest\V1\Ecommerce\Shoppingcart
 */
class PersistResource extends AbstractResource
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
     */
    public function postAction($params)
    {
        return $this->getShoppingCartCollection()->setToUser($params['shoppingCartToken']);
    }

    /**
     * define verbs
     */
    protected function define()
    {
        $this->definition
            ->setName('Persist shopping cart')
            ->setDescription('Persist a shopping cart')
            ->editVerb('post', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Persist cart from cart ID into current user')
                    ->identityRequired()
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('Shopping cart token')
                            ->setKey('shoppingCartToken')
                            ->setRequired()
                    );
            });
    }


}