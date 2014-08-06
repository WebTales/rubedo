<?php


namespace RubedoAPI\Rest\V1;


use RubedoAPI\Tools\VerbDefinitionEntity;

class ContactRessource extends AbstractRessource {
    function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Contact')
            ->setDescription('Send an email to contact')
            ->editVerb('post', function(VerbDefinitionEntity &$entity) {
                $entity->setDescription('Send an email');
            })
        ;
    }
    public function postAction($params)
    {

    }
}