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


use Rubedo\Services\Manager;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Exceptions\APIControllerException;

/**
 * Class ContactResource
 * @package RubedoAPI\Rest\V1
 */
class ContactResource extends AbstractResource
{
    /**
     * { @inheritdoc }
     */
    function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Contact')
            ->setDescription('Send an email to contact')
            ->editVerb('post', function (VerbDefinitionEntity &$entity) {
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
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('from')
                            ->setRequired()
                            ->setDescription('Sender is required')
                            ->setFilter('validate_email')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('subject')
                            ->setRequired()
                            ->setDescription('Subject is required')
                            ->setFilter('string')
                    );
            });
    }

    /**
     * Post to contact
     *
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIControllerException
     */
    public function postAction($params)
    {
        /** @var \Rubedo\Interfaces\Collection\IMailingList $mailingListsService */
        $mailingListsService = Manager::getService('MailingList');
        /** @var \Rubedo\Interfaces\Mail\IMailer $mailerService */
        $mailerService = Manager::getService('Mailer');
        $config = Manager::getService('Config');

        $senderOfNotif = $config["rubedo_config"]["fromEmailNotification"];

        if(empty($senderOfNotif)) {
            return [
                'success' => false,
                'message' => 'The "Sender of notifications" property must be set in the install tool'
            ];
        }

        $mailingList = $mailingListsService->findById($params['mailingListId']);
        if (empty($mailingList) || empty($mailingList['replyToAddress']))
            throw new APIControllerException('Can\'t find recipient', 404);
        $mailerObject = $mailerService->getNewMessage();

        if (empty($mailingList['replyToName']))
            $mailerObject->setTo($mailingList['replyToAddress']);
        else
            $mailerObject->setTo([$mailingList['replyToAddress'] => $mailingList['replyToName']]);

        $mailerObject->setFrom($senderOfNotif);
        $mailerObject->setReplyTo($params['from']);
        $mailerObject->setSubject($params['subject']);
        $params['fields']["email"]=$params['from'];
        $mailerObject->setBody(htmlspecialchars_decode($this->buildEmail($params['fields']), ENT_QUOTES));

        // Send e-mail
        $errors = [];
        if ($mailerService->sendMessage($mailerObject, $errors)) {
            return [
                'success' => true,
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error encountered, more details in "errors"',
                'errors' => $errors,
            ];
        }
    }

    /**
     *
     * Build email corpus from fields array
     *
     * @param $fields
     * @return string
     */
    protected function buildEmail($fields)
    {
        $lines = [];
        foreach ($fields as $name => $content) {
            $lines[] = $name . ' : ' . $content;
        }
        return implode("\n", $lines);
    }

}