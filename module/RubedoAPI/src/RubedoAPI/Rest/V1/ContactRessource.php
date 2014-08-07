<?php


namespace RubedoAPI\Rest\V1;


use Rubedo\Services\Manager;
use RubedoAPI\Exceptions\APIControllerException;
use RubedoAPI\Tools\FilterDefinitionEntity;
use RubedoAPI\Tools\VerbDefinitionEntity;

class ContactRessource extends AbstractRessource {
    function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Contact')
            ->setDescription('Send an email to contact')
            ->editVerb('post', function(VerbDefinitionEntity &$entity) {
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
                    )
                ;
            })
        ;
    }

    public function postAction($params)
    {
        /** @var \Rubedo\Interfaces\Collection\IMailingList $mailingListsService */
        $mailingListsService = Manager::getService('MailingList');
        /** @var \Rubedo\Interfaces\Mail\IMailer $mailerService */
        $mailerService = Manager::getService('Mailer');

        $mailingList = $mailingListsService->findById($params['mailingListId']);
        if (empty($mailingList) || empty($mailingList['replyToAddress']))
            throw new APIControllerException('Can\'t find recipient', 404);
        $mailerObject = $mailerService->getNewMessage();

        if (empty($mailingList['replyToName']))
            $mailerObject->setTo($mailingList['replyToAddress']);
        else
            $mailerObject->setTo(array($mailingList['replyToAddress'] => $mailingList['replyToName']));
        $mailerObject->setFrom($params['from']);
        $mailerObject->setSubject($params['subject']);
        $mailerObject->setBody($this->buildEmail($params['fields']));

        // Send e-mail
        $errors = array();
        if($mailerService->sendMessage($mailerObject, $errors)) {
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

    protected function buildEmail($fields)
    {
        $lines = array();
        foreach ($fields as $name => $content) {
            $lines[] = $name . ' : ' . $content;
        }
        return implode("\n", $lines);
    }

}