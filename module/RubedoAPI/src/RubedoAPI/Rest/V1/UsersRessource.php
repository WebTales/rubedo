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

namespace RubedoAPI\Rest\V1;

use MongoId;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Exceptions\APIAuthException;
use RubedoAPI\Exceptions\APIEntityException;

class UsersRessource extends AbstractRessource {
    protected $toExtractFromFields = array('name', 'text', 'login', 'email', 'password');

    public function __construct()
    {
        parent::__construct();
        $this->define();
    }

    public function getEntityAction($id) {
        $user = $this->getUsersCollection()->findById($id);
        if (empty($user)) {
            throw new APIEntityException('User not found', 404);
        }
        $user = array_intersect_key(
            $user,
            array_flip(
                array(
                    'name',
                    'groups',
                    'fields',
                    'taxonomy',
                    'language',
                    'workingLanguage',
                    'id',
                    'readOnly',
                    'typeId',
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

    public function patchEntityAction($id, $params)
    {
        $user = $this->getUsersCollection()->findById($id);
        if (empty($user)) {
            throw new APIEntityException('User not found', 404);
        }
        $data = &$params['user'];
        $type = $this->getUserTypesCollection()->findById(empty($data['typeId'])?$user['typeId']:$data['typeId']);
        if (empty($type)) {
            throw new APIEntityException('UserType not found.', 404);
        }
        if (
            (isset($data['status']) && !$this->getAclService()->hasAccess('write.ui.users.' . $data['status']))
            || ($this->getCurrentUserAPIService()->getCurrentUser() != $user)
        ) {
            throw new APIAuthException('You have no suffisants rights', 403);
        }
        if (isset($data['fields'])) {
            foreach ($data['fields'] as $fieldName => $fieldValue) {
                if (in_array($fieldName, $this->toExtractFromFields)) {
                    $data[$fieldName] = $fieldValue;
                }
            }
            $data['fields'] = $this->filterFields($type, $data['fields']);
        }
        $user = array_replace_recursive($user, $data);
        $updateResult = $this->getUsersCollection()->update($user);
        $updateResult['message'] = &$updateResult['msg'];
        return $updateResult;
    }

    public function postAction($params) {
        $userType = $this->getUserTypesCollection()->findById($params['usertype']);
        if (empty($userType))
            throw new APIEntityException('Usertype not exist', 404);
        $user = array();
        $user['name'] = $params['name'];
        $user['email'] = $params['email'];
        $user['login'] = $params['email'];
        $user['typeId'] = $userType['id'];
        $user['defaultGroup'] = $userType['defaultGroup'];
        $user['groups'] = array($userType['defaultGroup']);
        $user['taxonomy'] = array();

        $fields = empty($params['fields'])?array():$params['fields'];
        $user['fields'] = &$fields;

        $existingFields = array();
        foreach ($userType['fields'] as $userTypeField) {
            $existingFields[] = $userTypeField['config']['name'];
        }

        foreach ($fields as $fieldName => &$fieldValue) {
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
        }

        $createdUser = $this->getUsersCollection()->create($user);

        if (!$createdUser['success']) {
            return $createdUser;
        }

        if (!empty($params['password'])) {
            $passwordChanged = $this->getUsersCollection()->changePassword($params['password'], $createdUser['data']['version'], $createdUser['data']['id']);
            if (!$passwordChanged) {
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
                throw new APIEntityException('Can\'t send mail');
            }
        }

        return array(
            'success' => true,
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

    protected function definePost(VerbDefinitionEntity &$verbDef)
    {
        $verbDef
            ->setDescription('Create a user')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Name')
                    ->setKey('name')
                    ->setFilter('string')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Email')
                    ->setKey('email')
                    ->setFilter('validate_email')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Password')
                    ->setKey('password')
            )
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
                    ->setDescription('Fields')
                    ->setKey('fields')
            );
    }

    protected function defineGetEntity(VerbDefinitionEntity &$verbDef)
    {
        $verbDef
            ->setDescription('Get informations about a user')
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Users')
                    ->setKey('user')
                    ->setRequired()
            );
    }

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