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
use Zend\Debug\Debug;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

/**
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class SignUpController extends AbstractController
{

    public function indexAction ()
    {
        if ($this->getRequest()->isPost()){
            $params = $this->params()->fromPost();
            $output = $this->params()->fromQuery();
            if ((!isset($params['name']))||(!isset($params['email']))||(!isset($params['userTypeId']))){
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/fail.html.twig");
                return $this->_sendResponse($output, $template);
            }
            $alreadyExistingUser=Manager::getService("Users")->findByEmail($params['email']);
            if ($alreadyExistingUser){
                $output['signupMessage']="Email already in use.";
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                return $this->_sendResponse($output, $template);
            }
            $userType=Manager::getService('UserTypes')->findById($params['userTypeId']);
            if ($userType['signUpType']=="none") {
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/fail.html.twig");
                return $this->_sendResponse($output, $template);
            }
            $newUser=array();
            $newUser['name']=$params['name'];
            $newUser['email']=$params['email'];
            $newUser['login']=$params['email'];
            $newUser['typeId']=$params['userTypeId'];
            $newUser['defaultGroup']=$userType['defaultGroup'];
            $newUser['groups']=array($userType['defaultGroup']);
            $newUser['taxonomy']=array();
            unset($params['name']);
            unset($params['email']);
            unset($params['userTypeId']);
            $newUser['fields']=$params;
            if ($userType['signUpType']=="open") {
                $newUser['status']="approved";
            } else if ($userType['signUpType']=="moderated") {
                $newUser['status']="pending";
            } else if ($userType['signUpType']=="emailConfirmation") {
                $newUser['status']="emailUnconfirmed";
                $currentTimeService = Manager::getService('CurrentTime');
                $currentTime = $currentTimeService->getCurrentTime();
                $newUser['signupTime']=$currentTime;
            }
            
            $createdUser=Manager::getService('Users')->create($newUser);
            if (!$createdUser['success']) {
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/fail.html.twig");
                return $this->_sendResponse($output, $template);
            } else if ($userType['signUpType']=="emailConfirmation"){
                //send email here
                $emailVars=array();
                $emailVars["name"]=$newUser["name"];
                $emailVars["confirmUrl"]=$_SERVER['HTTP_REFERER'].'?confirmingEmail=1&userId='.$createdUser['data']['id'].'&signupTime='.$newUser["signupTime"];
                $etemplate = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/confirm-email-body.html.twig");
                $mailBody = Manager::getService('FrontOfficeTemplates')->render($etemplate, $emailVars);
                $mailService = Manager::getService('Mailer');
                $message = $mailService->getNewMessage();
                $message->setTo(array(
                    $newUser["email"]
                ));
                $config = Manager::getService('config');
                $options = $config['rubedo_config'];
                $message->setFrom(array($options['fromEmailNotification']=>"Rubedo"));
                $message->setSubject('['.Manager::getService('Sites')->getHost($output['site']['id']).'] '.'Account creation');
                
                $message->setBody($mailBody);
                $message->addPart($mailBody, 'text/html');
                
                $result = $mailService->sendMessage($message);
                if ($result===1){
                    $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/confirmEmail.html.twig");
                    return $this->_sendResponse($output, $template);
                } else {
                    $output['signupMessage']="Unable to send confirmation email";
                    $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                    return $this->_sendResponse($output, $template);
                }
                
                
            } else {
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/done.html.twig");
                return $this->_sendResponse($output, $template);
            }
        }
        $blockConfig = $this->params()->fromQuery('block-config', array());
        $output = $this->params()->fromQuery();
        if (isset($output["confirmingEmail"])){
            
            $userId = $this->params()->fromQuery("userId");
            $signupTime = $this->params()->fromQuery("signupTime");
            if ((!isset($userId))||(!isset($signupTime))){
                $output['signupMessage']="Missing required parameters";
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                return $this->_sendResponse($output, $template);
            }
            $user=Manager::getService("Users")->findById($userId);
            if (! $user){
                $output['signupMessage']="Unknown user";
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                return $this->_sendResponse($output, $template);
            }
            if ($user['status']!="emailUnconfirmed"){
                $output['signupMessage']="Account email already confimred";
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                return $this->_sendResponse($output, $template);
            }
            if ($user['signupTime']!=$signupTime){
                $output['signupMessage']="Invalid sign up time";
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                return $this->_sendResponse($output, $template);
            }
            $user["status"]="approved";
            $update=Manager::getService("Users")->update($user);
            if (!$update['success']){
                $output['signupMessage']="Error during user update";
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                return $this->_sendResponse($output, $template);
            } else {
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailConfirmed.html.twig");
                return $this->_sendResponse($output, $template);
            }
            
        }
        if (! isset($blockConfig['userType'])) {
            return $this->_sendResponse(array(), "block.html.twig");
        }
        $output['userTypeId']=$blockConfig['userType'];
        $userType=Manager::getService('UserTypes')->findById($blockConfig['userType']);
        if ($userType['signUpType']=="none") {
            return $this->_sendResponse(array(), "block.html.twig");
        }
        $output['fields']=$userType['fields'];
        if ((isset($blockConfig['introduction'])) && ($blockConfig['introduction'] != "")) {
            $content = Manager::getService('Contents')->findById($blockConfig["introduction"], true, false);
            $output['contentId'] = $blockConfig["introduction"];
            $output['text'] = $content["fields"]["body"];
            $output["locale"] = isset($content["locale"]) ? $content["locale"] : null;
        }
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signUp.html.twig");
        $css = array();
        $js = array();
        return $this->_sendResponse($output, $template, $css, $js);
    }
     
}
