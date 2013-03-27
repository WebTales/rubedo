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

/**
 * Contact Form
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 *
 */

class Application_Form_Contact extends Zend_Form
{
    public function init()
    {
    	$this->setMethod('post');
    	
    	$name = new Zend_Form_Element_Text('name');
    	$name->setLabel('Nom *');
    	$name->setRequired(true);
    	
    	$email = new Zend_Form_Element_Text('email');
    	$email->setLabel('Adresse e-mail *');
    	$email->setRequired(true);
    	$email->addValidator('EmailAddress');
    	
    	$subject = new Zend_Form_Element_Text('subject');
    	$subject->setLabel('Objet *');
    	$subject->setRequired(true);
    	
    	$message = new Zend_Form_Element_Textarea('message');
    	$message->setLabel('Message *');
    	$message->setRequired(true);
    	$message->setAttrib('rows', 5);
		
		$captcha = new Zend_Form_Element_Captcha ('captcha',
			array(
				'label' => "Merci de saisir le code ci-dessous :",
				'required' => true,
				'captcha'=> array(
					'captcha' => 'image',
					'wordLen' => 6,
					'font' => APPLICATION_PATH."/../public/captcha/fonts/fonts-japanese-gothic.ttf",
					'height' => 100,
					'width' => 300,
					'fontSize' => 50,
					'imgDir' => APPLICATION_PATH."/../public/captcha/",
					'imgUrl' => Zend_Controller_Front::getInstance()->getBaseUrl()."/captcha",
					'dotNoiseLevel' => 200,
					'lineNoiseLevel' => 20,
				)
			)
		);
		
		$submit = new Zend_Form_Element_Submit('Valider');
		    	
    	$this->addElements(array($name, $email, $subject, $message, $captcha, $submit));
    }
}