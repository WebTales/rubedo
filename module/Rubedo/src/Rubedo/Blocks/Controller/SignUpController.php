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

use Rubedo\Services\Manager;
use Zend\Captcha\ReCaptcha;
use Zend\Captcha\Image as CaptchaImage;
use Zend\Session\Container as SessionContainer;

/**
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class SignUpController extends AbstractController
{

    public function indexAction()
    {
        $blockConfig = $this->params()->fromQuery('block-config', array());
        $output = $this->params()->fromQuery();

        $postConfirmer=$this->params()->fromPost('userTypeId');
        if (isset($blockConfig['captcha']) && $blockConfig['captcha']) {
            $output['captcha'] = $this->generateCaptcha();
        }

        if ($this->getRequest()->isPost() && isset($postConfirmer)) {
            $params = $this->params()->fromPost();
            if (isset($blockConfig['captcha']) && $blockConfig['captcha'] && !$this->captchaIsValid($params)) {
                $output['signupMessage'] = "Blocks.SignUp.fail.captchaIsWrong";
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/fail.html.twig");
                return $this->_sendResponse($output, $template);
            }
            if (!isset($params['name'], $params['email'], $params['userTypeId'])) {
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/fail.html.twig");
                return $this->_sendResponse($output, $template);
            }
            $collectPassword = isset($output['block-config']['collectPassword']) ? $output['block-config']['collectPassword'] : false;
            if ($collectPassword) {
                if (!isset($params['password'], $params['confirmPassword'])) {
                    $output['signupMessage'] = "Blocks.SignUp.emailConfirmError.missingPasswordParameters";
                    $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                    return $this->_sendResponse($output, $template);
                }
                if ($params['password'] != $params['confirmPassword']) {
                    $output['signupMessage'] = "Blocks.SignUp.emailConfirmError.passwordParametersDontMatch";
                    $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                    return $this->_sendResponse($output, $template);
                }
            }
            $alreadyExistingUser = Manager::getService("Users")->findByEmail($params['email']);
            if ($alreadyExistingUser) {
                $output['signupMessage'] = "Blocks.SignUp.emailConfirmError.emailInUse";
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                return $this->_sendResponse($output, $template);
            }
            $userType = Manager::getService('UserTypes')->findById($params['userTypeId']);
            if ($userType['signUpType'] == "none") {
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/fail.html.twig");
                return $this->_sendResponse($output, $template);
            }
            $mailingListsToSubscribe=array();
            $newUser = array();
            $newUser['name'] = $params['name'];
            $newUser['email'] = $params['email'];
            $newUser['login'] = $params['email'];
            $newUser['typeId'] = $params['userTypeId'];
            $newUser['defaultGroup'] = $userType['defaultGroup'];
            $newUser['groups'] = array($userType['defaultGroup']);
            $newUser['taxonomy'] = array();
            unset($params['name']);
            unset($params['email']);
            unset($params['userTypeId']);
            if ($collectPassword) {
                $newPassword = $params['password'];
                unset($params['password']);
                unset($params['confirmPassword']);
            }
            foreach ($params as $key => $value){
                if (strpos($key,"mlSubscr_")!==FALSE){
                    $mailingListsToSubscribe[]=str_replace("mlSubscr_","",$key);
                    unset($params[$key]);
                }
            }
            $newUser['fields'] = $params;
            if ($userType['signUpType'] == "open") {
                $newUser['status'] = "approved";
            } else if ($userType['signUpType'] == "moderated") {
                $newUser['status'] = "pending";
            } else if ($userType['signUpType'] == "emailConfirmation") {
                $newUser['status'] = "emailUnconfirmed";
                $currentTimeService = Manager::getService('CurrentTime');
                $currentTime = $currentTimeService->getCurrentTime();
                $newUser['signupTime'] = $currentTime;
            }
            $createdUser = Manager::getService('Users')->create($newUser);
            if (($createdUser['success']) && ($collectPassword)) {
                Manager::getService('Users')->changePassword($newPassword, $createdUser['data']['version'], $createdUser['data']['id']);
            }
            if (($createdUser['success']) && ($mailingListsToSubscribe)) {
                $mailingListService=Manager::getService("MailingList");
                foreach ($mailingListsToSubscribe as $mailingListId){
                     $mailingListService->subscribe($mailingListId, $newUser['email'], false);
                }
            }
            if (!$createdUser['success']) {
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/fail.html.twig");
                return $this->_sendResponse($output, $template);
            } else if ($userType['signUpType'] == "emailConfirmation") {
                $emailVars = array();
                $emailVars["name"] = $newUser["name"];
                $emailVars["confirmUrl"] = $_SERVER['HTTP_REFERER']
                    . '?confirmingEmail=1&userId=' . $createdUser['data']['id']
                    . '&signupTime=' . $newUser["signupTime"];

                $etemplate = Manager::getService('FrontOfficeTemplates')
                    ->getFileThemePath("blocks/signup/confirm-email-body.html.twig");
                $mailBody = Manager::getService('FrontOfficeTemplates')->render($etemplate, $emailVars);

                $mailService = Manager::getService('Mailer');
                $config = Manager::getService('config');
                $options = $config['rubedo_config'];
                $currentLang = Manager::getService('CurrentLocalization')->getCurrentLocalization();
                $subject = '[' . Manager::getService('Sites')->getHost($output['site']['id']) . '] '
                    . Manager::getService('Translate')->getTranslation(
                        'Blocks.SignUp.confirmEmail.subject',
                        $currentLang,
                        'en'
                    );

                $message = $mailService->getNewMessage()
                    ->setTo(array(
                        $newUser["email"] => (!empty($newUser['name'])) ? $newUser['name'] : $newUser['login'],
                    ))
                    ->setFrom(array($options['fromEmailNotification'] => "Rubedo"))
                    ->setSubject($subject)
                    ->setBody($mailBody, 'text/html');
                $result = $mailService->sendMessage($message);

                if ($result === 1) {
                    $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/confirmEmail.html.twig");
                    return $this->_sendResponse($output, $template);
                } else {
                    $output['signupMessage'] = "Blocks.SignUp.emailConfirmError.unableToSendConfirmMail";
                    $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                    return $this->_sendResponse($output, $template);
                }


            } else {
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/done.html.twig");
                return $this->_sendResponse($output, $template);
            }
        }

        if (isset($output["confirmingEmail"])) {

            $userId = $this->params()->fromQuery("userId");
            $signupTime = $this->params()->fromQuery("signupTime");
            if ((!isset($userId)) || (!isset($signupTime))) {
                $output['signupMessage'] = "Blocks.SignUp.emailConfirmError.missingRequiredParameters";
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                return $this->_sendResponse($output, $template);
            }
            $user = Manager::getService("Users")->findById($userId);
            if (!$user) {
                $output['signupMessage'] = "Blocks.SignUp.emailConfirmError.unknownUser";
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                return $this->_sendResponse($output, $template);
            }
            if ($user['status'] != "emailUnconfirmed") {
                $output['signupMessage'] = "Blocks.SignUp.emailConfirmError.emailAlreadyConfirmed";
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                return $this->_sendResponse($output, $template);
            }
            if ($user['signupTime'] != $signupTime) {
                $output['signupMessage'] = "Blocks.SignUp.emailConfirmError.invalidSignUpTime";
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                return $this->_sendResponse($output, $template);
            }
            $user["status"] = "approved";
            $update = Manager::getService("Users")->update($user);
            if (!$update['success']) {
                $output['signupMessage'] = "Blocks.SignUp.emailConfirmError.userUpdateFailed";
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                return $this->_sendResponse($output, $template);
            } else {
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailConfirmed.html.twig");
                return $this->_sendResponse($output, $template);
            }

        }
        if (!isset($blockConfig['userType'])) {
            return $this->_sendResponse(array(), "block.html.twig");
        }
        $output['userTypeId'] = $blockConfig['userType'];
        $userType = Manager::getService('UserTypes')->findById($blockConfig['userType']);
        if ($userType['signUpType'] == "none") {
            return $this->_sendResponse(array(), "block.html.twig");
        }
        $output['fields'] = $userType['fields'];
        $output['collectPassword'] = isset($blockConfig['collectPassword']) ? $blockConfig['collectPassword'] : false;
        if ((isset($blockConfig['introduction'])) && ($blockConfig['introduction'] != "")) {
            $content = Manager::getService('Contents')->findById($blockConfig["introduction"], true, false);
            $output['contentId'] = $blockConfig["introduction"];
            $output['text'] = $content["fields"]["body"];
            $output["locale"] = isset($content["locale"]) ? $content["locale"] : null;
        }
        $mailingListArray=array();
        if (( isset($blockConfig['mailingListId']))&&(is_array($blockConfig['mailingListId']))) {
            $mailingListService=Manager::getService("MailingList");
            foreach ($blockConfig['mailingListId'] as $value){
                $myList=$mailingListService->findById($value);
                if ($myList){
                    $mailingListArray[]=array(
                        "label"=>$myList['name'],
                        "value"=>$value
                    );
                }
            }
        }
        $output['mailingListArray']=$mailingListArray;
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signUp.html.twig");
        $css = array();
        $js = array();
        return $this->_sendResponse($output, $template, $css, $js);
    }

    /**
     * Generate captcha html or id
     *
     * @return array
     */
    protected function generateCaptcha ()
    {
        $recaptchaService = Manager::getService('Recaptcha');
        $recaptchaKey = $recaptchaService->getKeyPair();
        $captcha = array();
        if ($recaptchaKey) {
            $captchaInstance = new ReCaptcha($recaptchaKey);
            $captcha['html'] = $captchaInstance->getService()->getHtml();
        } else {
            $captchaOptions = array(
                'wordLen' => 6,
                'font' => APPLICATION_PATH . '/data/fonts/fonts-japanese-gothic.ttf',
                'height' => 100,
                'width' => 220,
                'fontSize' => 50,
                'imgDir' => APPLICATION_PATH . '/public/captcha/',
                'imgUrl' => '/captcha',
                'dotNoiseLevel' => 200,
                'lineNoiseLevel' => 20
            );
            $captchaInstance = new CaptchaImage($captchaOptions);
            $captchaInstance->generate();
            $captcha['id'] = $captchaInstance->getId();
        }
        return $captcha;

    }

    /**
     * Check if captcha is valid
     *
     * @param array $params
     * @return bool
     */
    function captchaIsValid(array $params) {
        $recaptchaService = Manager::getService('Recaptcha');
        $recaptchaKey = $recaptchaService->getKeyPair();
        if ($recaptchaKey) {
            if (empty($params['recaptcha_challenge_field']) || empty($params['recaptcha_response_field'])) {
                return false;
            }
            $captchaInstance = new ReCaptcha($recaptchaKey);
            return $captchaInstance->getService()
                ->verify(
                    $params['recaptcha_challenge_field'],
                    $params['recaptcha_response_field']
                )
                ->isValid();
        } else {
            $captcha = $params['captcha'];
            if (empty($captcha)) {
                return false;
            }
            $captchaId = $captcha['id'];
            $captchaInput = $captcha['input'];
            $captchaSession = new SessionContainer('Zend_Form_Captcha_' . $captchaId);
            $captchaIterator = $captchaSession->getIterator();
            $captchaWord = $captchaIterator['word'];
            return $captchaWord && $captchaInput == $captchaWord;
        }
    }
}