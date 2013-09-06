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
use Zend\View\Model\JsonModel;

/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class ProtectedResourceController extends AbstractController
{

    protected $_defaultTemplate = 'protected-resource';

    public function indexAction ()
    {
        $blockConfig = $this->getParam('block-config', array());
        $output = $this->getAllParams();
        
        if ((isset($blockConfig['introduction'])) && ($blockConfig['introduction'] != "")) {
            $content = Manager::getService('Contents')->findById($blockConfig["introduction"], true, false);
            $output['contentId'] = $blockConfig["introduction"];
            $output['text'] = $content["fields"]["body"];
            $output["locale"] = isset($content["locale"]) ? $content["locale"] : null;
        }
        
        $output['mailingListId'] = $blockConfig['mailingListId'];
        $output['damId'] = $blockConfig['documentId'];
        
        if (isset($blockConfig['displayType']) && ! empty($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $this->_defaultTemplate . ".html.twig");
        }
        
        $css = array();
        $js = array(
            $this->getRequest()->getBasePath() . '/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/access-resource.js")
        );
        return $this->_sendResponse($output, $template, $css, $js);
    }

    /**
     * Allow to add an email into a mailing list
     *
     * @return json
     */
    public function xhrSubmitEmailAction ()
    {
        // Default mailing list
        $this->mailingListId = $this->getParam("mailing-list-id");
        if (! $this->mailingListId) {
            throw new \Rubedo\Exceptions\User("Incomplete form.", "Exception19");
        }
        $this->damId = $this->getParam("dam-id");
        if (! $this->damId) {
            throw new \Rubedo\Exceptions\User("Incomplete form.", "Exception19");
        }
        $this->siteId = $this->getParam("site-id");
        if (! $this->siteId) {
            throw new \Rubedo\Exceptions\User("Incomplete form.", "Exception19");
        }
        
        // Declare email validator
        $emailValidator = new \Zend\Validator\EmailAddress();
        
        // MailingList service
        $mailingListService = \Rubedo\Services\Manager::getService("MailingList");
        
        // Get email
        $this->email = $this->getParam("email");
        
        // Validate email
        if ($emailValidator->isValid($this->email)) {
            // Register user
            $subcribeResult = $mailingListService->subscribe($this->mailingListId, $this->email, false);
            
            if ($subcribeResult['success']) {
                $resultArray = $this->_sendDamMail();
            }
            
            return new JsonModel($resultArray);
        } else {
            return new JsonModel(array(
                "success" => false,
                "msg" => "Adresse e-mail invalide"
            ));
        }
    }

    protected function _sendDamMail ()
    {
        $tk = Manager::getService('TinyUrl')->creamDamAccessLinkKey($this->damId);
        $site = Manager::getService('Sites')->findById($this->siteId);
        $protocol = in_array('HTTP', $site['protocol']) ? 'http' : 'https';
        
        $fileUrl = $protocol . '://' . Manager::getService('Sites')->getHost($this->siteId) . '?tk=' . $tk;
        
        // $resultArray = array('success'=>true,'msg'=>'Un courriel contenant le lien de téléchargement vous a été envoyé.');
        if (! Zend_Registry::getInstance()->isRegistered('swiftMail')) {
            $resultArray = array(
                'success' => true,
                'msg' => '<a href="' . $fileUrl . '">' . Manager::getService("Translate")->translateInWorkingLanguage("Blocks.ProtectedRessource.Message.Download") . 'Cliquez pour votre téléchargement</a>'
            );
        } else {
            $resultArray = $this->_sendEmail($fileUrl);
        }
        
        return $resultArray;
    }

    /**
     *
     * @todo change from address !
     */
    protected function _sendEmail ($url)
    {
        $twigVar = array(
            'downloadUrl' => $url
        );
        $twigVar['signature'] = Manager::getService("Translate")->translateInWorkingLanguage("Blocks.ProtectedRessource.Mail.signature") . ' ' . Manager::getService('Sites')->getHost($this->siteId);
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/protected-resource/mail-body.html.twig");
        $mailBody = Manager::getService('FrontOfficeTemplates')->render($template, $twigVar);
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/protected-resource/mail-body.plain.twig");
        $plainMailBody = Manager::getService('FrontOfficeTemplates')->render($template, $twigVar);
        
        $mailService = Manager::getService('Mailer');
        
        $message = Manager::getService('MailingList')->getNewMessage($this->mailingListId);
        
        $message->setTo(array(
            $this->email
        ));
        $message->setSubject('[' . Manager::getService('Sites')->getHost($this->siteId) . '] ' . Manager::getService("Translate")->translateInWorkingLanguage("Blocks.ProtectedRessource.Mail.Subject"));
        
        $message->setBody($plainMailBody);
        $message->addPart($mailBody, 'text/html');
        
        $result = $mailService->sendMessage($message);
        if ($result === 1) {
            $resultArray = array(
                'success' => true,
                'msg' => Manager::getService("Translate")->translateInWorkingLanguage("Blocks.ProtectedRessource.Message.EmailSent")
            );
        } else {
            $resultArray = array(
                'success' => false,
                'msg' => Manager::getService("Translate")->translateInWorkingLanguage("Blocks.ProtectedRessource.Message.EmailSent")
            );
        }
        
        return $resultArray;
    }
}
