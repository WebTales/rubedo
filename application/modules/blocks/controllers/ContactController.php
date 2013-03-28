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
Use Rubedo\Services\Manager;

require_once ('AbstractController.php');

/**
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_ContactController extends Blocks_AbstractController
{

    protected $_defaultTemplate = 'contact';
    
    public function indexAction ()
    {
    	$blockConfig = $this->getRequest()->getParam('block-config');
    	$errors = array();
    	
    	if(isset($blockConfig['captcha'])){
    		$contactForm = new Application_Form_Contact(null, $blockConfig['captcha']);
    	} else {
    		$contactForm = new Application_Form_Contact();
    	}
    	
    	//Check if the form was send
    	if(is_string($this->getParam('name'))){
	    	if ($contactForm->isValid($_POST)) {
	    		if(isset($blockConfig['contacts']) && is_array($blockConfig['contacts']) && count($blockConfig['contacts'])>0){
	    			//Create a mailer object
	    			$mailerService = Manager::getService('Mailer');
	    			$mailerObject = $mailerService->getNewMessage();
	    			$stringRecipients = "";
	    			
	    			//Get recipients from block config
	    			$recipients = $blockConfig['contacts'];
	    			
	    			//Build e-mail
	    			$name = $this->getParam('name');
	    			$email = $this->getParam('email');
	    			$subject = $this->getParam('subject');
	    			$message = $this->getParam('message');
	    			$message = $name." (".$email.") : \n".$message;
	    			
	    			$mailerObject->setSubject($subject);
	    			$mailerObject->setFrom($email);
	    			$mailerObject->setTo($recipients);
	    			$mailerObject->setBody($message);
	    			
	    			//Send e-mail
	    			$sendResult = $mailerService->sendMessage($mailerObject, $errors);
	    			
	    			if(!$sendResult){
	    				$errors[] = "L'envoi du mail à échoué, merci de réessayer ultèrieurement";
	    			} else {
	    				$output['sendResult'] = true;
	    			}
	    		} else {
	    			$errors[] = "Il est nécéssaire de spécifier un destinataire dans l'interface d'administration, merci de contacter l'administrateur du site.";
	    		}
	    	}
    	}
    	
    	if(count($errors)>0){
    		$output['errors'] = $errors;
    	}
    	
        $output["blockConfig"]=$blockConfig;
        
        if (isset($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
                    "blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
                    "blocks/" . $this->_defaultTemplate . ".html.twig");
        }
        
        $output['contactForm'] = $contactForm;
        
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
}
