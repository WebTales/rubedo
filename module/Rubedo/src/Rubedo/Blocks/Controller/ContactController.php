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

Use Rubedo\Services\Manager;

/**
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
class ContactController extends AbstractController
{

    protected $_defaultTemplate = 'contact';

    public function indexAction ()
    {
        $blockConfig = $this->params()->fromQuery('block-config');
        
        $output = $this->params()->fromQuery();
        
        $errors = array();
        
        if (isset($blockConfig['captcha'])) {
            $contactForm = new \Rubedo\Blocks\Model\Contact(null, $blockConfig['captcha']);
        } else {
            $contactForm = new \Rubedo\Blocks\Model\Contact();
        }
        
        // Check if the form was send
        if (is_string($this->getParamFromQuery('name'))) {
            if ($contactForm->isValid($_POST)) {
                if (isset($blockConfig['contacts']) && is_array($blockConfig['contacts']) && count($blockConfig['contacts']) > 0) {
                    // Create a mailer object
                    $mailerService = Manager::getService('Mailer');
                    $mailerObject = $mailerService->getNewMessage();
                    
                    // Get recipients from block config
                    $recipients = $blockConfig['contacts'];
                    
                    // Build e-mail
                    $name = $this->getParamFromQuery('name');
                    $email = $this->getParamFromQuery('email');
                    $subject = $this->getParamFromQuery('subject');
                    $message = $this->getParamFromQuery('message');
                    $message = $name . " (" . $email . ") : \n" . $message;
                    
                    $mailerObject->setSubject($subject);
                    $mailerObject->setFrom($email);
                    $mailerObject->setTo($recipients);
                    $mailerObject->setBody($message);
                    
                    // Send e-mail
                    $sendResult = $mailerService->sendMessage($mailerObject, $errors);
                    
                    if (! $sendResult) {
                        $errors[] = "L'envoi du mail à échoué, merci de réessayer ultèrieurement";
                    } else {
                        $output['sendResult'] = true;
                    }
                } else {
                    $errors[] = "Il est nécéssaire de spécifier un destinataire dans l'interface d'administration, merci de contacter l'administrateur du site.";
                }
            }
        }
        
        if (count($errors) > 0) {
            $output['errors'] = $errors;
        }
        
        $output["blockConfig"] = $blockConfig;
        
        if (isset($blockConfig['displayType']) && ! empty($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $this->_defaultTemplate . ".html.twig");
        }
        
        $output['contactForm'] = $contactForm;
        
        $css = array();
        $js = array();
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
