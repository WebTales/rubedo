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

namespace RubedoAPI\Rest\V1\Users;


use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Exceptions\APIRequestException;
use RubedoAPI\Rest\V1\AbstractResource;

/**
 * Class ConfirmemailResource
 * @package RubedoAPI\Rest\V1\Users
 */
class ConfirmemailResource extends AbstractResource
{
    /**
     * {@inheritdoc}
     */
    function __construct()
    {
        parent::__construct();
        $this->define();
    }

    /**
     * Post action
     *
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     * @throws \RubedoAPI\Exceptions\APIRequestException
     */
    public function postAction($params)
    {
        $user = $this->getUsersCollection()->findById($params['userId']);
        if (empty($user)) {
            throw new APIEntityException('User not found', 404);
        }
        if ($user['status'] != 'emailUnconfirmed') {
            throw new APIEntityException('User already confirmed', 409);
        }
        if ($user['signupTime'] != $params['signupTime']) {
            throw new APIRequestException('Invalid Signup time', 400);
        }
        $user['status'] = 'approved';
        return $this->getUsersCollection()->update($user);
    }

    /**
     * Define
     */
    protected function define()
    {
        $this
            ->definition
            ->setDescription('Confirm user email')
            ->setName('Confirm Email')
            ->editVerb('post', function (VerbDefinitionEntity &$entity) {
                $this->definePost($entity);
            });
    }

    /**
     * Define post
     *
     * @param VerbDefinitionEntity $entity
     */
    protected function definePost(VerbDefinitionEntity &$entity)
    {
        $entity
            ->setDescription('Confirm email')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('User ID')
                    ->setKey('userId')
                    ->setFilter('\MongoId')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Signup time')
                    ->setKey('signupTime')
                    ->setRequired()
            );
    }
} 