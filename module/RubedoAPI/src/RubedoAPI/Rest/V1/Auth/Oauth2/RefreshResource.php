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

/**
 * Class RefreshResource
 * auth/oauth2/refresh
 *
 * @package RubedoAPI\Rest\V1\Auth\Oauth2
 */
class RefreshResource extends AbstractResource
{
    /**
     * { @inheritdoc }
     */
    function __construct()
    {
        parent::__construct();
        $this->definition
            ->setName('Refresh Oauth2 token')
            ->setDescription('Refresh Oauth2 token')
            ->editVerb('post', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Refresh Oauth2 token')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('Create new token from refresh token')
                            ->setKey('refresh_token')
                            ->setRequired()
                    );
            });
    }

    /**
     * Post to auth/oauth2/refresh
     *
     * @param $params
     * @return array
     */
    function postAction($params)
    {
        $output = array('success' => true);
        $response = $this->getAuthAPIService()->APIRefreshAuth($params['refresh_token']);
        $output['token'] = $this->subTokenFilter($response['token']);
        $this->subUserFilter($response['user']);
        $this->getCurrentUserAPIService()->setAccessToken($output['token']['access_token']);
        $route = $this->getContext()->params()->fromRoute();
        $route['api'] = array('auth');
        $route['method'] = 'GET';
        $route['access_token'] = $output['token']['access_token'];
        $rightsSubRequest = $this->getContext()->forward()->dispatch('RubedoAPI\\Frontoffice\\Controller\\Api', $route);
        $output['currentUser'] = $rightsSubRequest->getVariables()['currentUser'];
        return $output;
    }
}