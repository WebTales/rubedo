<?php

namespace RubedoAPI\Rest\V1;

use RubedoAPI\Tools\FilterDefinitionEntity;
use RubedoAPI\Tools\VerbDefinitionEntity;

class AuthRessource extends AbstractRessource {
    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Authentication')
            ->setDescription('Login in the Rubedo API')
        ;
    }
    public function optionsAction()
    {
        return array_merge(parent::optionsAction(), $this->getAuthMeans());
    }
    protected function getAuthMeans()
    {
        return [
            'means' => [
                'oauth2' => '/api/v1/auth/oauth2',
            ],
        ];
    }
}