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
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace RubedoAPI\Rest\V1\Auth\Oauth2;

use RubedoAPI\Rest\V1\AbstractRessource;
use RubedoAPI\Tools\FilterDefinitionEntity;
use RubedoAPI\Tools\VerbDefinitionEntity;

class RefreshRessource extends AbstractRessource {
    function __construct()
    {
        parent::__construct();
        $this->definition
            ->setName('Refresh Oauth2 token')
            ->setDescription('')
            ->editVerb('post', function(VerbDefinitionEntity &$entity){
                $entity
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('Create new token from refresh token')
                            ->setKey('refresh_token')
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
        $response = $this->getAuthAPIService()->APIRefreshAuth($params['refresh_token']);
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