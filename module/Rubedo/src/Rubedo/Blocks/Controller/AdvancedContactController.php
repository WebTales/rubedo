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
class AdvancedContactController extends AbstractController
{

    protected $_defaultTemplate = 'advancedContact';

    public function indexAction ()
    {
        $blockConfig = $this->getRequest()->getParam('block-config');
        $formName = isset($blockConfig["formName"]) ? $blockConfig["formName"] : null;
        
        $error = false;
        
        if ($formName !== null && isset($blockConfig['contacts'])) {
            $output = $this->getAllParams();
            
            $formPath = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/forms/" . $formName . ".html.twig");
            $realPath = realpath(APPLICATION_PATH . "/../public/templates/" . $formPath);
            
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/email-templates/" . $formName . ".html.twig");
            $templateRealPath = realpath(APPLICATION_PATH . "/../public/templates/" . $template);
            
            if (file_exists($realPath) && file_exists($templateRealPath)) {
                $form = Manager::getService('FrontOfficeTemplates')->render($formPath, array());
                
                // Check if the form was send
                if ($this->getParam("post")) {
                    
                    $twigVar = $this->_request->getPost();
                    
                    $mailBody = Manager::getService('FrontOfficeTemplates')->render($template, $twigVar);
                    
                    // Create a mailer object
                    $mailerService = Manager::getService('Mailer');
                    $mailerObject = $mailerService->getNewMessage();
                    
                    // Get recipients from block config
                    $recipients = $blockConfig['contacts'];
                    
                    // Build e-mail
                    $subject = $blockConfig['subject'];
                    
                    $mailerObject->setSubject($subject);
                    $mailerObject->setFrom($blockConfig['from']);
                    $mailerObject->setTo($recipients);
                    $mailerObject->setBody($mailBody);
                    
                    if (isset($blockConfig['cc']) && is_array($blockConfig["cc"])) {
                        $mailerObject->setCc($blockConfig['cc']);
                    }
                    
                    // Send e-mail
                    $sendResult = $mailerService->sendMessage($mailerObject);
                    
                    if (! $sendResult) {
                        $error = true;
                    } else {
                        $output['sendResult'] = true;
                    }
                }
                
                $output["blockConfig"] = $blockConfig;
            } else {
                $error = true;
            }
        } else {
            $error = true;
        }
        
        if (isset($blockConfig['displayType']) && ! empty($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $this->_defaultTemplate . ".html.twig");
        }
        
        $output['error'] = $error;
        $output['form'] = $form;
        
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
}
