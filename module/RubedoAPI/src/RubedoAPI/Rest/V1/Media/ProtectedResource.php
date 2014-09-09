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

namespace RubedoAPI\Rest\V1\Media;

use Rubedo\Mail\Mailer;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Exceptions\APIControllerException;
use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Rest\V1\AbstractResource;

class ProtectedResource extends AbstractResource {

    function __construct() {
        parent::__construct();
        $this->define();
    }

    public function postAction($params)
    {
        $mailingList = $this->getMailingListCollection()->findById($params['mailingListId']);
        if (empty($mailingList)) {
            throw new APIEntityException('MailingList not found', 404);
        }

        $site = $this->getSitesCollection()->findById($params['siteId']);
        if (empty($site)) {
            throw new APIEntityException('Site not found', 404);
        }

        $media = $this->getDamCollection()->findById($params['mediaId']);
        if (empty($media)) {
            throw new APIEntityException('Media not found', 404);
        }

        if (!$this->getMailingListCollection()->subscribe($mailingList['id'], $params['email'], false)['success']) {
            throw new APIControllerException('Can\'t subscribe to mailing list', 500);
        }

        $this->sendDamMail($mailingList, $site, $media, $params['email']);

        return array(
            'success' => true,
        );
    }

    protected function sendDamMail ($mailingList, $site, $media, $email)
    {
        $tk = $this->getTinyUrlCollection()->creamDamAccessLinkKey($media['id']);
        $protocol = in_array('HTTP', $site['protocol']) ? 'http' : 'https';

        $fileUrl = $protocol . '://' . $this->getSitesCollection()->getHost($site['id']) . '?tk=' . $tk;

        if (!Mailer::isActive()) {
            throw new APIControllerException('Mailer is not active');
        }
        $this->sendEmail($mailingList, $site, $email, $fileUrl);
    }

    protected function sendEmail ($mailingList, $site, $email, $url)
    {
        $twigVar = array(
            'downloadUrl' => $url
        );
        $twigVar['signature'] = $this->getTranslateService()->translateInWorkingLanguage("Blocks.ProtectedRessource.Mail.signature") . ' ' . $this->getSitesCollection()->getHost($site['id']);
        $template = $this->getFrontOfficeTemplatesService()->getFileThemePath("blocks/protected-resource/mail-body.html.twig");
        $config = $this->getconfigService();
        $options = $config['rubedo_config'];
        $mailBody = $this->getFrontOfficeTemplatesService()->render($template, $twigVar);

        $template = $this->getFrontOfficeTemplatesService()->getFileThemePath("blocks/protected-resource/mail-body.plain.twig");
        $plainMailBody = $this->getFrontOfficeTemplatesService()->render($template, $twigVar);

        $mailService = $this->getMailerService();

        $message = $this->getMailingListCollection()->getNewMessage($mailingList['id']);

        $message
            ->setTo(array($email => $email))
            ->setFrom(array($options['fromEmailNotification'] => $options['fromEmailNotification']))
            ->setSubject('[' . $this->getSitesCollection()->getHost($site['id']) . '] ' . $this->getTranslateService()->translateInWorkingLanguage("Blocks.ProtectedRessource.Mail.Subject"))
            ->setBody($plainMailBody)
            ->addPart($mailBody, 'text/html');

        $result = $mailService->sendMessage($message);
        if ($result !== 1) {
            throw new APIControllerException('Email not sent', 500);
        }
    }

    protected function define()
    {
        $this
            ->definition
            ->setDescription('Send an email with tinyURL')
            ->setName('Send email')
            ->editVerb('post', function (VerbDefinitionEntity &$verbDef) {
                $verbDef
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('Mailing list ID')
                            ->setKey('mailingListId')
                            ->setFilter('\MongoId')
                            ->setRequired()
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('media ID')
                            ->setKey('mediaId')
                            ->setFilter('\MongoId')
                            ->setRequired()
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('site ID')
                            ->setKey('siteId')
                            ->setFilter('\MongoId')
                            ->setRequired()
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('email')
                            ->setKey('email')
                            ->setFilter('validate_email')
                            ->setRequired()
                    );
            });
    }

}