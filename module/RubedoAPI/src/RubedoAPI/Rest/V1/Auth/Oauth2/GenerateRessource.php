<?php

namespace RubedoAPI\Rest\V1\Auth\Oauth2;

use Rubedo\Services\Manager;
use RubedoAPI\Rest\V1\AbstractRessource;
use RubedoAPI\Tools\FilterDefinitionEntity;
use RubedoAPI\Tools\VerbDefinitionEntity;

class GenerateRessource extends AbstractRessource {
    function __construct()
    {
        parent::__construct();
        $this->definition
            ->setName('Generate token')
            ->setDescription('')
            ->editVerb('post', function(VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('User to log in')
                            ->setKey('PHP_AUTH_USER')
                            ->setRequired()

                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('Password for the user')
                            ->setKey('PHP_AUTH_PW')
                            ->setRequired()
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('token')
                            ->setRequired()
                    )
                ;
            })
        ;
    }

    function postAction($params)
    {
        $output = array('success' => true);
        $response = $this->getAuthAPIService()->APIAuth($params['PHP_AUTH_USER'], $params['PHP_AUTH_PW']);
        $output['token'] = $this->subTokenFilter($response['token']);
        $output['token']['user'] = $this->subUserFilter($response['user']);
        return $output;
    }

    //todo refactor in FilterDefinitionEntity
    protected function subTokenFilter(&$token)
    {
        return array_intersect_key($token, array_flip(array('access_token', 'refresh_token', 'lifetime', 'createTime')));
    }
    protected function subUserFilter(&$user)
    {
        return array_intersect_key($user, array_flip(array('id', 'login')));
    }
}