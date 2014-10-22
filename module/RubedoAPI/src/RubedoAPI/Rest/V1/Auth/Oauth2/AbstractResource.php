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
 * Abstract class AbstractResource
 * @package RubedoAPI\Rest\V1\Auth\Oauth2
 */
abstract class AbstractResource extends \RubedoAPI\Rest\V1\AbstractResource
{
    /**
     * { @inheritdoc }
     */
    function __construct()
    {
        parent::__construct();
        $this->definition
            ->editVerb('post', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('token')
                            ->setRequired()
                            ->setName('Token')
                            ->setDescription('Token authentication (required)')
                    );
            });
    }

    /**
     * Filter token from database
     *
     * @param $token
     * @return array
     */
    protected function subTokenFilter(&$token)
    {
        return array_intersect_key($token, array_flip(array('access_token', 'refresh_token', 'lifetime', 'createTime')));
    }

    /**
     * Filter user from database
     *
     * @param $user
     * @return array
     */
    protected function subUserFilter(&$user)
    {
        $user = $this->getUsersCollection()->findById($user['id']);
        return array_intersect_key($user, array_flip(array('id', 'login', 'name')));
    }
}