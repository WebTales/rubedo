<?php

namespace RubedoAPI\Rest\V1\Auth;

use RubedoAPI\Rest\V1\AbstractRessource;
use RubedoAPI\Tools\VerbDefinitionEntity;

class Oauth2Ressource extends AbstractRessource {
    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Oauth2 authentication')
            ->setDescription('Login by oauth2')
            ->editVerb('post', function(VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('foo')
                ;
            });
        ;
    }

}