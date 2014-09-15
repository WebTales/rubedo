<?php

namespace RubedoAPI\Rest\V1\Ecommerce\Shoppingcart;

use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Rest\V1\AbstractResource;

class PersistResource extends AbstractResource {
    function __construct()
    {
        parent::__construct();
        $this->define();

    }

    public function postAction($params)
    {
        return $this->getShoppingCartCollection()->setToUser($params['shoppingCartToken']);
    }

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