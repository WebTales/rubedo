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

use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Exceptions\APIEntityException;

class UsersRessource extends AbstractRessource {
    public function __construct()
    {
        parent::__construct();
        $this->define();
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
    protected function define()
    {
        $this->definition
            ->setName('Users')
            ->setDescription('')
            ->editVerb('post', function (VerbDefinitionEntity &$verbDef) {
               $this->definePost($verbDef);
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
}