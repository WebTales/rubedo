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
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace RubedoAPI\Rest\V1\Auth\Oauth2;

use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Exceptions\APIRequestException;

/**
 * Class GenerateResource
 * auth/oauth2/generate
 *
 * @package RubedoAPI\Rest\V1\Auth\Oauth2
 */
class GenerateResource extends AbstractResource
{
    /**
     * { @inheritdoc }
     */
    function __construct()
    {
        parent::__construct();
        $this->definition
            ->setName('Generate token')
            ->setDescription('Generate token by user and password')
            ->editVerb('post', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Generate token by user and password')
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
                    );
            });
    }

    /**
     * Post to auth/oauth2/generate
     *
     * @param $params
     * @return array
     */
    function postAction($params)
    {
        $output = array('success' => true);
        if (empty($params['PHP_AUTH_USER'])&&empty($_SERVER['PHP_AUTH_USER'])){
            throw new APIRequestException('PHP_AUTH_USER is required', 400);
        }
        if (empty($params['PHP_AUTH_PW'])&&empty($_SERVER['PHP_AUTH_PW'])){
            throw new APIRequestException('PHP_AUTH_PW is required', 400);
        }
        $authParamUser=!empty($params['PHP_AUTH_USER']) ? $params['PHP_AUTH_USER'] : $_SERVER['PHP_AUTH_USER'];
        $authParamPassword=!empty($params['PHP_AUTH_PW']) ? $params['PHP_AUTH_PW'] : $_SERVER['PHP_AUTH_PW'];
        $response = $this->getAuthAPIService()->APIAuth($authParamUser, $authParamPassword);
        $output['token'] = $this->subTokenFilter($response['token']);
        $this->subUserFilter($response['user']);
        $route = $this->getContext()->params()->fromRoute();
        $route['api'] = array('auth');
        $route['method'] = 'GET';
        $route['access_token'] = $output['token']['access_token'];
        //Hack Refresh currentUser
        $this->getCurrentUserAPIService()->setAccessToken($output['token']['access_token']);
        $rightsSubRequest = $this->getContext()->forward()->dispatch('RubedoAPI\\Frontoffice\\Controller\\Api', $route);
        $output['currentUser'] = $rightsSubRequest->getVariables()['currentUser'];
        return $output;
    }
}