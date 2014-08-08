<?php

namespace RubedoAPI\Rest\V1;

use Rubedo\Services\Manager;
use RubedoAPI\Tools\FilterDefinitionEntity;
use RubedoAPI\Tools\VerbDefinitionEntity;

class PagesRessource extends AbstractRessource {
    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Oauth2 authentication')
            ->setDescription('Login by oauth2')
        ;
    }

}