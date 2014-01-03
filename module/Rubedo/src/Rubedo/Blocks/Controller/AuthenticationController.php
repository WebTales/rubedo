<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
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
namespace Rubedo\Blocks\Controller;

use Rubedo\Collection\AbstractCollection;
use Rubedo\Exceptions\Server;
use Rubedo\Services\Manager;

/**
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class AuthenticationController extends AbstractController
{
    /**
     * @var \Rubedo\Templates\FrontOfficeTemplates
     */
    private $templateService;

    /**
     * @var array
     */
    private $css = array();

    /**
     * @var array
     */
    private $js = array();

    /**
     * Init class vars
     */
    public function __construct()
    {
        $this->templateService = Manager::getService('FrontOfficeTemplates');
    }

    /**
     * Render block
     *
     * @return array|\Rubedo\Templates\Raw\RawViewModel
     */
    public function indexAction()
    {
        $output = $this->params()->fromQuery();
        if (isset($output['recoverEmail'], $output['token'])) {
            $output = $this->changePassword($output);
            $template = $this->templateService->getFileThemePath("blocks/authentication/change_password.html.twig");
        } elseif (isset($output['recoverPassword']) || isset($output['recoverEmail'])) {
            $output = $this->recoverPassword($output);
            $template = $this->templateService->getFileThemePath("blocks/authentication/send_token.html.twig");
        } else {
            $output = $this->login($output);
            $this->js[] = $this->getRequest()->getBasePath()
                . '/' . $this->templateService->getFileThemePath("js/authentication.js");
            $template = $this->templateService->getFileThemePath("blocks/authentication/authentication.html.twig");
        }
        return $this->_sendResponse($output, $template, $this->css, $this->js);
    }

    /**
     * Send token by email
     *
     * @param array $output
     * @return array
     * @throws \Rubedo\Exceptions\Server
     *
     */
    private function recoverPassword($output)
    {
        $user = null;
        if (isset($output['recoverEmail'])) {
            AbstractCollection::disableUserFilter();
            //Disable filters, else we can't get full user.
            /** @var $userCollection \Rubedo\Collection\Users */
            $userCollection = Manager::getService('Users');
            $user = $userCollection->findByEmail($output['recoverEmail']);
            AbstractCollection::disableUserFilter(false);

            if ($user !== null) {
                $user['recoverToken'] = md5(serialize($user) . time());
                $userCollection->update($user);
                $emailVars = array(
                    'link' => '?recoverEmail=' . $user['email'] . '&token=' . $user['recoverToken'],
                );
                if ($this->sendMail('Blocks.Auth.Email.sendToken.subject', 'email_send_token', $emailVars, $user)) {
                    $output['mailSended'] = true;
                } else {
                    throw new Server('Can\'t send email with token', 'Exception23');
                }
            }
        }
        $output['user'] = $user;
        return $output;
    }

    /**
     * Change the password
     *
     * @param array $output
     * @return array
     * @throws \Rubedo\Exceptions\Server
     */
    private function changePassword($output)
    {
        AbstractCollection::disableUserFilter();
        //Disable filters, else we can't get full user.
        /** @var $userCollection \Rubedo\Collection\Users */
        $userCollection = Manager::getService('Users');
        $user = $userCollection->findByEmail($output['recoverEmail']);
        AbstractCollection::disableUserFilter(false);

        if ($user == null || !isset($user['recoverToken']) || $output['token'] != $user['recoverToken']) {
            $output['error'] = 'Blocks.Auth.Error.TokenIsWrong';
            return $output;
        }
        if ($this->getRequest()->isPost()) {

            $password = $output['password'] = $this->params()->fromPost('password');
            $passwordConfirm = $output['passwordConfirm'] = $this->params()->fromPost('passwordConfirm');

            if (empty($password) || $password != $passwordConfirm) {
                $output['error'] = 'Blocks.Auth.Error.PasswordsNotMatch';
                return $output;
            }

            $user['recoverToken'] = null;

            /** @var $hashService \Rubedo\Security\Hash */
            $hashService = Manager::getService('Hash');
            $user['salt'] = $hashService->generateRandomString();
            $user['password'] = $hashService->derivatePassword($password, $user['salt']);

            $userCollection->update($user);

            $emailVars = array(
                'password' => $password,
            );


            if ($this->sendMail('Blocks.Auth.Email.sendPassword.subject', 'email_send_password', $emailVars, $user)) {
                $output['success'] = true;
            } else {
                throw new Server('Can\'t send email with token', 'Exception23');
            }
        }
        return $output;
    }

    /**
     * Show login box
     *
     * @param array $output
     * @return array
     */
    private function login($output)
    {
        /** @var $currentUserService \Rubedo\User\CurrentUser */
        $currentUserService = Manager::getService('CurrentUser');
        $currentUser = $currentUserService->getCurrentUser();

        if ($currentUser && isset($output['block-config']['redirectPage'])) {
            $this->redirect()->toRoute(null, array('pageId' => $output['block-config']['redirectPage']));
        }

        $output['displayMode'] = isset($output['block-config']['displayMode']) ? $output['block-config']['displayMode'] : 'pop-in';
        $output['enforceHTTPS'] = in_array('HTTPS', $output['site']['protocol']) ? true : false;
        $output['currentUser'] = $currentUser;
        $output['profilePage'] = isset($output['block-config']['profilePage']) ? $output['block-config']['profilePage'] : false;
        if ($output["profilePage"]) {
            $urlOptions = array(
                'encode' => true,
                'reset' => true
            );

            $output['profilePageUrl'] = $this->url()->fromRoute(null, array(
                'pageId' => $output["profilePage"]
            ), $urlOptions);
        }
        return $output;
    }

    /**
     * Send mail
     */
    private function sendMail($title, $template, $vars, $user)
    {
        /** @var $mailService \Rubedo\Mail\Mailer */
        $mailService = Manager::getService('Mailer');
        /** @var $translationService \Rubedo\Internationalization\Translate */
        $translationService = Manager::getService('Translate');
        $config = Manager::getService('config');
        $options = $config['rubedo_config'];
        $currentSite = Manager::getService('Sites')->findById(Manager::getService('PageContent')->getCurrentSite());

        $vars['siteName'] = !empty($currentSite['title']) ? $currentSite['title'] : $currentSite['text'];
        $vars['siteUrl'] = (in_array('HTTPS', $currentSite['protocol']) ? 'https://' : 'http://')
            . $currentSite['text'];
        $vars['lang'] = $user['language'];
        $vars['name'] = (!empty($user['name'])) ? $user['name'] : $user['login'];
        $vars['URI'] = $_SERVER['REDIRECT_URL'];

        $templateHtml = $this->templateService->getFileThemePath("blocks/authentication/" . $template . ".html.twig");
        $templateTxt = $this->templateService->getFileThemePath("blocks/authentication/" . $template . ".txt.twig");

        $bodyHtml = $this->templateService->render($templateHtml, $vars);
        $bodyTxt = html_entity_decode($this->templateService->render($templateTxt, $vars), ENT_QUOTES);

        $message = $mailService
            ->getNewMessage()
            ->addPart($bodyTxt, 'text/plain')
            ->setTo(array(
                $user["email"] => (!empty($user['name'])) ? $user['name'] : $user['login'],
            ))
            ->setFrom(array($options['fromEmailNotification'] => "Rubedo"))

            ->setSubject('[' . $vars['siteName'] . ']' . $translationService->getTranslation(
                    $title,
                    $vars['lang'],
                    $currentSite['locale']
                ))
            ->setBody($bodyHtml, 'text/html');
        return $mailService->sendMessage($message);
    }
}
