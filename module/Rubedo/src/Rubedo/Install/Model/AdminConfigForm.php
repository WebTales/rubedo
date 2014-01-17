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
namespace Rubedo\Install\Model;

use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\Form\Element\Password;
use Zend\Form\Element\Email;
use Zend\InputFilter\InputFilter;
/**
 * Form for DB Config
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class AdminConfigForm extends BootstrapForm
{
    public static function getForm(){
        $nameField = new Text('name');
        $nameField->setAttribute('Required',true);
        $nameField->setAttribute('autocomplete', 'off');
        $nameField->setLabel('Account Name');
        
        $loginField = new Text('login');
        $loginField->setAttribute('Required',true);
        $loginField->setAttribute('autocomplete', 'off');
        $loginField->setLabel('Login');
        
        $passwordField = new Password('password');
        $passwordField->setAttribute('Required',true);
        $passwordField->setAttribute('autocomplete', 'off');
        $passwordField->setLabel('Password');
        
        $confirmPasswordField = new Password('confirmPassword');
        $confirmPasswordField->setAttribute('Required',true);
        $confirmPasswordField->setAttribute('autocomplete', 'off');
        $confirmPasswordField->setLabel('Confirm password');
        
        $emailField = new Email('email');
        $emailField->setAttribute('Required',true);
        $emailField->setLabel('Email');
        
        $inputFilter = new InputFilter();
        $inputFilter->add(array(
            'name' => 'password',
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 2,
                    )
                )
            )
        ));
        $inputFilter->add(array(
            'name' => 'confirmPassword',
            'validators' => array(
                array(
                    'name' => 'Identical',
                    'options' => array(
                        'token' => 'password'
                    )
                )
            )
        ));
        
        $dbForm = new Form();
        $dbForm->add($nameField);
        $dbForm->add($loginField);
        $dbForm->add($passwordField);
        $dbForm->add($confirmPasswordField);
        $dbForm->add($emailField);
        $dbForm->setInputFilter($inputFilter);
        $dbForm = self::setForm($dbForm);
        
        return $dbForm;
        
    }
}

