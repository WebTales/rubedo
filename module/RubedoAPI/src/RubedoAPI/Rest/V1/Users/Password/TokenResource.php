<?php

namespace RubedoAPI\Rest\V1\Users\Password;


use Rubedo\Collection\AbstractCollection;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Exceptions\APIRequestException;
use RubedoAPI\Rest\V1\AbstractResource;

/**
 * Class TokenResource
 * @package RubedoAPI\Rest\V1\Users\Password
 */
class TokenResource extends AbstractResource
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
     * Get action
     *
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     * @throws \RubedoAPI\Exceptions\APIRequestException
     */
    public function getAction($params)
    {
        $wasFiltered = AbstractCollection::disableUserFilter();
        $user = $this->getUsersCollection()->findByEmail($params['email']);
        AbstractCollection::disableUserFilter($wasFiltered);

        if (empty($user)) {
            throw new APIEntityException('User not found', 404);
        }

        $site = $this->getSitesCollection()->findById($params['siteId']);
        if (empty($site)) {
            throw new APIEntityException('Site not found', 404);
        }

        $user['recoverToken'] = md5(serialize($user) . time());
        $this->getUsersCollection()->update($user);
        $emailVars = array(
            'link' => '?recoverEmail=' . $user['email'] . '&token=' . $user['recoverToken'],
        );

        if (!$this->sendMail('Blocks.Auth.Email.sendToken.subject', 'email_send_token', $emailVars, $user, $site)) {
            throw new APIRequestException('Can\'t send email with token');
        }

        return array(
            'success' => true,
        );
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
        AbstractCollection::disableUserFilter();
        $user = $this->getUsersCollection()->findByEmail($params['email']);
        AbstractCollection::disableUserFilter(false);

        if (empty($user)) {
            throw new APIEntityException('User not found', 404);
        }
        if (!isset($user['recoverToken'])) {
            throw new APIEntityException('Token not exist', 404);
        }
        if ($params['token'] != $user['recoverToken']) {
            throw new APIEntityException('Token incorrect', 400);
        }

        $user['recoverToken'] = null;
        $user['salt'] = $this->getHashService()->generateRandomString();
        $user['password'] = $this->getHashService()->derivatePassword($params['password'], $user['salt']);

        return $this->getUsersCollection()->update($user);
    }

    /**
     * Send mail
     *
     * @param $title
     * @param $template
     * @param $vars
     * @param $user
     * @param $site
     * @return bool
     */
    protected function sendMail($title, $template, $vars, $user, $site)
    {
        $options = $this->getConfigService()['rubedo_config'];

        $vars['siteName'] = !empty($site['title']) ? $site['title'] : $site['text'];
        $vars['siteUrl'] = (in_array('HTTPS', $site['protocol']) ? 'https://' : 'http://')
            . $site['text'];
        $vars['lang'] = $user['language'];
        $vars['name'] = (!empty($user['name'])) ? $user['name'] : $user['login'];
        $vars['URI'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ($vars['siteUrl'] . $_SERVER['REDIRECT_URL']);

        $templateHtml = $this->getFrontOfficeTemplatesService()->getFileThemePath("blocks/authentication/" . $template . ".html.twig");
        $templateTxt = $this->getFrontOfficeTemplatesService()->getFileThemePath("blocks/authentication/" . $template . ".txt.twig");

        $bodyHtml = $this->getFrontOfficeTemplatesService()->render($templateHtml, $vars);
        $bodyTxt = html_entity_decode($this->getFrontOfficeTemplatesService()->render($templateTxt, $vars), ENT_QUOTES);

        $message = $this->getMailerService()
            ->getNewMessage()
            ->addPart($bodyTxt, 'text/plain')
            ->setTo(array(
                $user["email"] => (!empty($user['name'])) ? $user['name'] : $user['login'],
            ))
            ->setFrom(array($options['fromEmailNotification'] => !empty($options['fromEmailNotificationName']) ? $options['fromEmailNotificationName'] : "Rubedo"))
            ->setSubject('[' . $vars['siteName'] . ']' . $this->getTranslationAPIService()->getTranslation(
                    $title,
                    $vars['lang'],
                    $site['locale']
                ))
            ->setBody($bodyHtml, 'text/html');
        return $this->getMailerService()->sendMessage($message);
    }

    /**
     * Define
     */
    protected function define()
    {
        $this
            ->definition
            ->setName('Password token')
            ->setDescription('Password token')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $this->defineGet($entity);
            })
            ->editVerb('post', function (VerbDefinitionEntity &$entity) {
                $this->definePost($entity);
            });
    }

    /**
     * Define get
     *
     * @param VerbDefinitionEntity $entity
     */
    protected function defineGet(VerbDefinitionEntity &$entity)
    {
        $entity
            ->setDescription('Send a token')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Email')
                    ->setKey('email')
                    ->setFilter('validate_email')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Site id')
                    ->setKey('siteId')
                    ->setFilter('\MongoId')
                    ->setRequired()
            );
    }

    /**
     * Define post
     *
     * @param VerbDefinitionEntity $entity
     */
    protected function definePost(VerbDefinitionEntity &$entity)
    {
        $entity
            ->setDescription('Set new password')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Email')
                    ->setKey('email')
                    ->setFilter('validate_email')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Token')
                    ->setKey('token')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Password')
                    ->setKey('password')
                    ->setRequired()
            );
    }
} 