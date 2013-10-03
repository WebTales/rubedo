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
namespace Rubedo\Blocks\Model;

use Zend\Form\Element\Submit;
use Zend\Form\Element\Button;
use Zend\Form\Element\Text;
use Zend\Form\Element\Textarea;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Captcha;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\Form\FieldsetInterface;
use Rubedo\Services\Manager;
use Zend\Http\Request;
use Zend\Form\Element\Email;
use Zend\Captcha\Image as CaptchaImage;
use Zend\Captcha\AbstractWord;

/**
 * Contact Form
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 *         
 */
class Contact extends Form
{

    protected $_captcha;

    public function __construct($options = null, $captcha = false)
    {
        $this->_captcha = $captcha;
        parent::__construct($options);
    }

    public function init()
    {
        $translationService = Manager::getService("Translate");
        
        $this->setAttribute('class', 'form-horizontal');
        
        $status = new Hidden('status');
        $status->setValue('true');
        
        $name = new Text('name');
        $name->setLabel($translationService->translateInWorkingLanguage("Blocks.Contact.Input.Name.Label"));
        $name->setAttribute('Required', true);
        
        $email = new Email('email');
        $email->setLabel($translationService->translateInWorkingLanguage("Blocks.Contact.Input.Email.Label"));
        $email->setAttribute('Required', true);
        
        $subject = new Text('subject');
        $subject->setLabel($translationService->translateInWorkingLanguage("Blocks.Contact.Input.Subject.Label"));
        $subject->setAttribute('Required', true);
        
        $message = new Textarea('message');
        $message->setLabel($translationService->translateInWorkingLanguage("Blocks.Contact.Input.Message.Label"));
        $message->setAttribute('Required', true);
        $message->setAttribute('rows', 5);
        
        $elements = array(
            $name,
            $email,
            $subject,
            $message
        );
        
        foreach ($elements as $element) {
            $this->add($element);
        }
        
        if ($this->_captcha) {
            $captchaOptions = array(
                'wordLen' => 6,
                'font' => APPLICATION_PATH . "/data/fonts/fonts-japanese-gothic.ttf",
                'height' => 100,
                'width' => 220,
                'fontSize' => 50,
                'imgDir' => APPLICATION_PATH . "/public/captcha/",
                'imgUrl' => "/captcha",
                'dotNoiseLevel' => 200,
                'lineNoiseLevel' => 20,
            );
            
            $captchaImage = new CaptchaImage($captchaOptions);

            $this->add(array(
                'type' => 'Zend\Form\Element\Captcha',
                'name' => 'captcha',
                'options' => array(
                    'label' => $translationService->translateInWorkingLanguage("Blocks.Contact.Input.Captcha.Label"),
                    'captcha' => $captchaImage
                )
            ));
        }
        
        $this->setIds($this);
        
        $submitButton = new Submit('Submit');
        $submitButton->setValue($translationService->translateInWorkingLanguage("Blocks.Contact.Input.Submit.Label"));
        $submitButton->setAttribute('class', 'btn btn-large btn-success');
        
        $buttonFieldSet = new Fieldset('buttonGroup');
        $buttonFieldSet->add($submitButton);
        $buttonFieldSet->setAttribute('class', 'form-actions');
        $this->add($buttonFieldSet);
        $this->setAttribute('class', 'form-horizontal');
    }
    
    
    protected function setIds($element)
    {
        if ($element instanceof FieldsetInterface) {
            foreach ($element as $subElement) {
                $this->setIds($subElement);
            }
            $subElement->setAttribute('id', $subElement->getName());
        }
        $element->setLabelAttributes(array(
            'class' => 'control-label'
        ));
        $element->setAttribute('id',$element->getName());
    }
}