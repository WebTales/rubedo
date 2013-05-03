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
class Blocks_AdvancedContactController extends Blocks_AbstractController
{

    protected $_defaultTemplate = 'advancedContact';
    
    public function indexAction ()
    {
    	$blockConfig = $this->getRequest()->getParam('block-config');
    	$formName = $blockConfig["formName"];
    	$output = $this->getAllParams();
    	
    	$errors = array();
    	
    	$objectPath = "Blocks_Model_".$formName;
    	
    	$formPath = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/forms/".$formName.".html.twig");
    	$form = Manager::getService('FrontOfficeTemplates')->render($formPath, array());
    	
    	/*if(isset($blockConfig['captcha'])){
    		$form = new $objectPath(null, $blockConfig['captcha']);
    	} else {
    		$form = new $objectPath();
    	}*/
    	
    	//Check if the form was send
    	if ($this->getParam("post")) {
    	    
	        $twigVar = $this->_request->getPost();
	        
	        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/email-templates/".$formName.".html.twig");
            $mailBody = Manager::getService('FrontOfficeTemplates')->render($template, $twigVar);
	        
			//Create a mailer object
			$mailerService = Manager::getService('Mailer');
			$mailerObject = $mailerService->getNewMessage();
			$stringRecipients = "";
			
			//Get recipients from block config
			$recipients = $blockConfig['contacts'];
			
			if($recipients){
    			//Build e-mail
    			$subject = "[Rubedo]";
    			
    			$mailerObject->setSubject($subject);
    			$mailerObject->setFrom("admin@rubedo.fr");
    			$mailerObject->setTo($recipients);
    			$mailerObject->setBody($mailBody);
    			
    			//Send e-mail
    			$sendResult = $mailerService->sendMessage($mailerObject, $errors);
    			
    			if(!$sendResult){
    			    $errors[] = "L'envoi du mail à échoué, merci de réessayer ultèrieurement";
    			} else {
    			    $output['sendResult'] = true;
    			}
			} else {
			    $errors[] = "Merci de renseigner un destinataire dans les paramètres de configuration du bloc contact avancé.";
			}
    	}
    	
    	if(count($errors)>0){
    		$output['errors'] = $errors;
    	}
    	
        $output["blockConfig"]=$blockConfig;
        
        if (isset($blockConfig['displayType']) && !empty($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
                    "blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
                    "blocks/" . $this->_defaultTemplate . ".html.twig");
        }
        
        $output['form'] = $form;
        
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
}
