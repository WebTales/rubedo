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
class Blocks_Model_Contact extends Zend_Form
{

    protected $_captcha;

    public function __construct ($options = null, $captcha = false)
    {
        $this->_captcha = $captcha;
        parent::__construct($options);
    }

    public function init ()
    {
        $request = new Zend_Controller_Request_Http();
        
        $this->setMethod('post');
        $this->setAttrib('action', $this->getView()
            ->baseUrl() . $request->getPathInfo());
        $this->setAttrib('class', 'form-horizontal');
        
        $status = new Zend_Form_Element_Hidden('status');
        $status->setValue('true');
        
        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Nom *');
        $name->setRequired(true);
        $name->addErrorMessage("Le champ suivant ne doit pas être vide");
        
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Adresse e-mail *');
        $email->setRequired(true);
        $email->addValidator('EmailAddress');
        $email->addErrorMessage("Le champ suivant ne doit pas être vide et l'adresse e-mail doit etre sous la forme : exemple@exemple.fr");
        
        $subject = new Zend_Form_Element_Text('subject');
        $subject->setLabel('Objet *');
        $subject->setRequired(true);
        $subject->addErrorMessage("Le champ suivant ne doit pas être vide");
        
        $message = new Zend_Form_Element_Textarea('message');
        $message->setLabel('Message *');
        $message->setRequired(true);
        $message->setAttrib('rows', 5);
        $message->addErrorMessage("Le champ suivant ne doit pas être vide");
        
        $this->addElements(array(
            $name,
            $email,
            $subject,
            $message
        ));
        
        if ($this->_captcha) {
            $captcha = new Zend_Form_Element_Captcha('captcha', array(
                'label' => "Merci de saisir le code ci-dessous :",
                'required' => true,
                'captcha' => array(
                    'captcha' => 'image',
                    'wordLen' => 6,
                    'font' => APPLICATION_PATH . "/../data/fonts/fonts-japanese-gothic.ttf",
                    'height' => 100,
                    'width' => 300,
                    'fontSize' => 50,
                    'imgDir' => APPLICATION_PATH . "/../public/captcha/",
                    'imgUrl' => Zend_Controller_Front::getInstance()->getBaseUrl() . "/captcha",
                    'dotNoiseLevel' => 200,
                    'lineNoiseLevel' => 20
                )
            ));
            $captcha->addErrorMessage("Le code que vous avez saisi ne correspond pas avec l'image");
            
            $this->addElement($captcha);
        }
        
        $submit = new Zend_Form_Element_Submit('Valider');
        $submit->setAttrib('class', 'btn btn-success custom-btn btn-large');
        
        $this->addElement($submit);
        
        $this->addDisplayGroup(array(
            $submit
        ), 'button');
        
        $this->getDisplayGroup('button')->setDecorators(array(
            
            'FormElements',
            array(
                'HtmlTag',
                array(
                    'tag' => 'div',
                    'class' => 'form-actions'
                )
            )
        ));
        
        foreach ($this->getElements() as $element) {
            $element->removeDecorator('HtmlTag');
            if ($element->getDecorator('label')) {
                $element->removeDecorator('Label');
                $element->addDecorator(array(
                    'controls' => 'HTMLTag'
                ), array(
                    'tag' => 'div',
                    'class' => 'controls'
                ));
                $element->addDecorator('Label', array(
                    'tag' => 'div',
                    'class' => 'control-label'
                ));
                $element->addDecorator('HTMLTag', array(
                    'tag' => 'div',
                    'class' => 'control-group'
                ));
            }
        }
        foreach ($this->getDisplayGroups() as $group) {
            foreach ($group->getElements() as $element) {
                $element->removeDecorator('HtmlTag');
                $element->removeDecorator('Label');
                $element->removeDecorator('Tooltip');
                $element->removeDecorator('DtDdWrapper');
            }
        }
        $this->setDecorators(array(
            'FormElements',
            'HtmlTag',
            'Form'
        ));
    }
}