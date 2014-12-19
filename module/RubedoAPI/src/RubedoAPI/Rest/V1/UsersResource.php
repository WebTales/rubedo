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

namespace RubedoAPI\Rest\V1;

use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Exceptions\APIAuthException;
use RubedoAPI\Exceptions\APIEntityException;

/**
 * Class UsersResource
 * @package RubedoAPI\Rest\V1
 */
class UsersResource extends AbstractResource
{
    /**
     * @var array
     */
    protected $toExtractFromFields = array('name', 'login', 'email', 'password', 'photo');
    /**
     * @var array
     */
    protected $toInjectIntoFields = array('name', 'email', 'photo', 'login');

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->define();
    }

    /**
     * Get entity action
     *
     * @param $id
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function getEntityAction($id)
    {
        $user = $this->getUsersCollection()->findById($id);
        if (empty($user)) {
            throw new APIEntityException('User not found', 404);
        }
        foreach ($this->toInjectIntoFields as $fieldToInject) {
            if (isset($user[$fieldToInject])) {
                $user['fields'][$fieldToInject] = $user[$fieldToInject];
            }
        }
        if (!empty($user['fields']['photo'])) {
            $user['photoUrl'] = $this->getUrlAPIService()->userAvatar($user['id'], 100, 100, 'boxed');
        }
        $user = array_intersect_key(
            $user,
            array_flip(
                array(
                    'groups',
                    'fields',
                    'taxonomy',
                    'language',
                    'workingLanguage',
                    'id',
                    'readOnly',
                    'typeId',
                    'photoUrl',
                )
            )
        );
        $userType = $this->getUserTypesCollection()->findById($user['typeId']);
        if (empty($userType)) {
            throw new APIEntityException('Usertype not found', 404);
        }

        $userType = array_intersect_key(
            $userType,
            array_flip(
                array(
                    'UTType',
                    'fields',
                    'layouts',
                    'type',
                    'signUpType',
                )
            )
        );

        $user['type'] = &$userType;

        return array(
            'success' => true,
            'user' => $user,
        );
    }

    /**
     * Patch entity action
     *
     * @param $id
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIAuthException
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function patchEntityAction($id, $params)
    {
        $user = $this->getUsersCollection()->findById($id);
        if (empty($user)) {
            throw new APIEntityException('User not found', 404);
        }
        $data = &$params['user'];
        $type = $this->getUserTypesCollection()->findById(empty($data['typeId']) ? $user['typeId'] : $data['typeId']);
        if (empty($type)) {
            throw new APIEntityException('UserType not found.', 404);
        }
        if ($this->getCurrentUserAPIService()->getCurrentUser()['id'] != $user['id']) {
            throw new APIAuthException('You have no suffisants rights', 403);
        }
        if (isset($data['fields'])) {
            $existingFields = array();
            foreach ($type['fields'] as $userTypeField) {
                $existingFields[] = $userTypeField['config']['name'];
            }
            foreach ($data['fields'] as $fieldName => &$fieldValue) {
                if (in_array($fieldName, $this->toExtractFromFields)) {
                    $data[$fieldName] = $fieldValue;
                }
                if (!in_array($fieldName, $existingFields)) {
                    unset ($data['fields'][$fieldName]);
                }
            }
            $data['fields'] = $this->filterFields($type, $data['fields']);
        }
        $user = array_replace_recursive($user, $data);
        $updateResult = $this->getUsersCollection()->update($user);
        if (!empty($data['password'])) {
            $passwordChanged = $this->getUsersCollection()->changePassword($data['password'], $updateResult['data']['version'], $updateResult['data']['id']);
            if (!$passwordChanged) {
                throw new APIEntityException('Can\'t set password');
            }
        }
        $updateResult['message'] = &$updateResult['msg'];
        return $updateResult;
    }

    /**
     * Post action
     *
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function postAction($params)
    {
        $userType = $this->getUserTypesCollection()->findById($params['usertype']);
        if (empty($userType))
            throw new APIEntityException('Usertype not exist', 404);
        $user = array();
        $user['typeId'] = $userType['id'];
        $user['defaultGroup'] = $userType['defaultGroup'];
        $user['groups'] = array($userType['defaultGroup']);
        $user['taxonomy'] = array();

        $fields = empty($params['fields']) ? array() : $params['fields'];
        $user['fields'] = &$fields;

        $existingFields = array();
        foreach ($userType['fields'] as $userTypeField) {
            $existingFields[] = $userTypeField['config']['name'];
        }

        foreach ($fields as $fieldName => &$fieldValue) {
            if (in_array($fieldName, $this->toExtractFromFields)) {
                $user[$fieldName] = $fieldValue;
            }
            if (!in_array($fieldName, $existingFields)) {
                unset ($fields[$fieldName]);
            }
        }

        if ($userType['signUpType'] == 'open') {
            $user['status'] = 'approved';
        } else if ($userType['signUpType'] == 'moderated') {
            $user['status'] = 'pending';
        } else if ($userType['signUpType'] == 'emailConfirmation') {
            $user['status'] = 'emailUnconfirmed';
            $user['signupTime'] = $this->getCurrentTimeService()->getCurrentTime();
        } else {
            throw new APIEntityException('Usertype not authorised', 403);
        }
        if (
            empty($user['groups'])
            || empty($user['defaultGroup'])
            || empty($user['status'])
            || empty($user['login'])
            || empty($user['email'])
            || empty($user['name'])
        ) {
            throw new APIEntityException('User not consistent: ' . json_encode($user), 400);
        }
        $createdUser = $this->getUsersCollection()->create($user);

        if (!$createdUser['success']) {
            return $createdUser;
        }

        if (!empty($user['password'])) {
            $passwordChanged = $this->getUsersCollection()->changePassword($user['password'], $createdUser['data']['version'], $createdUser['data']['id']);
            if (!$passwordChanged) {
                $this->getUsersCollection()->destroy($createdUser);
                throw new APIEntityException('Can\'t set password');
            }
        }
        if ($userType['signUpType'] == "emailConfirmation") {
            $emailVars = array();
            $emailVars["name"] = $user["name"];
            $emailVars["confirmUrl"] = $params['currentUrl']
                . '?confirmingEmail=1&userId=' . $createdUser['data']['id']
                . '&signupTime=' . $user["signupTime"];

            $etemplate = $this->getFrontOfficeTemplatesService()->getFileThemePath("blocks/signup/confirm-email-body.html.twig");
            $mailBody = $this->getFrontOfficeTemplatesService()->render($etemplate, $emailVars);

            $options = $this->getconfigService()['rubedo_config'];
            $currentLang = $this->getCurrentLocalizationAPIService()->getCurrentLocalization();
            $subject = $this->getTranslateService()->getTranslation(
                'Blocks.SignUp.confirmEmail.subject',
                $currentLang,
                'en'
            );

            $message = $this->getMailerService()->getNewMessage()
                ->setTo(array(
                    $user["email"] => (!empty($user['name'])) ? $user['name'] : $user['login'],
                ))
                ->setFrom(array($options['fromEmailNotification'] => "Rubedo"))
                ->setSubject($subject)
                ->setBody($mailBody, 'text/html');
            $result = $this->getMailerService()->sendMessage($message);
            if ($result !== 1) {
                $this->getUsersCollection()->destroy($createdUser);
                throw new APIEntityException('Can\'t send mail');
            }
        }

        return array(
            'success' => true,
            'user' => $createdUser,
        );
    }

    /**
     * Remove fields if not in content type
     *
     * @param $type
     * @param $fields
     */
    protected function filterFields($type, $fields)
    {
        $existingFields = array();
        foreach ($type['fields'] as $field) {
            $existingFields[] = $field['config']['name'];
        }
        foreach ($fields as $key => $value) {
            if (!in_array($key, $existingFields)) {
                unset ($fields[$key]);
            }
        }
        return $fields;
    }

    /**
     * Define verbs
     */
    protected function define()
    {
        $this->definition
            ->setName('Users')
            ->setDescription('Deal with users')
            ->editVerb('post', function (VerbDefinitionEntity &$verbDef) {
                $this->definePost($verbDef);
            });
        $this->entityDefinition
            ->setName('User')
            ->setDescription('Deal with a user')
            ->editVerb('get', function (VerbDefinitionEntity &$verbDef) {
                $this->defineGetEntity($verbDef);
            })
            ->editVerb('patch', function (VerbDefinitionEntity &$verbDef) {
                $this->definePatchEntity($verbDef);
            });
    }

    /**
     * Define post
     *
     * @param VerbDefinitionEntity $verbDef
     */
    protected function definePost(VerbDefinitionEntity &$verbDef)
    {
        $verbDef
            ->setDescription('Create a user')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Current URL')
                    ->setKey('currentUrl')
                    ->setFilter('validate_url')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('UserType id')
                    ->setKey('usertype')
                    ->setFilter('\MongoId')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Fields names returned with user')
                    ->setKey('fields')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('The user')
                    ->setKey('user')
                    ->setRequired()
            );
    }

    /**
     * Define get entity
     *
     * @param VerbDefinitionEntity $verbDef
     */
    protected function defineGetEntity(VerbDefinitionEntity &$verbDef)
    {
        $verbDef
            ->setDescription('Get informations about a user')
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('The user')
                    ->setKey('user')
                    ->setRequired()
            );
    }

    /**
     * Define patch entity
     *
     * @param VerbDefinitionEntity $verbDef
     */
    protected function definePatchEntity(VerbDefinitionEntity &$verbDef)
    {
        $verbDef
            ->setDescription('Edit informations about a user')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('User data')
                    ->setKey('user')
            );
    }
}