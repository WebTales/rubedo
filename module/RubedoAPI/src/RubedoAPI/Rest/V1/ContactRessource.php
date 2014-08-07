<?php


namespace RubedoAPI\Rest\V1;


use RubedoAPI\Tools\FilterDefinitionEntity;
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
                $entity
                    ->setDescription('Send an email')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('fields')
                            ->setRequired()
                            ->setMultivalued()
                            ->setDescription('Fields to send')
                            ->setFilter('string')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('mailingListId')
                            ->setRequired()
                            ->setDescription('ID of the mailing list to target')
                            ->setFilter('\MongoId')
                    )
                ;
            })
        ;
    }
    public function postAction($params)
    {

    }
}