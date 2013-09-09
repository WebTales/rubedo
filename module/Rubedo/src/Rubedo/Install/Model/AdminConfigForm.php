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
 * Form for DB Config
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Install_Model_AdminConfigForm extends Install_Model_BootstrapForm
{
    public static function getForm(){
        $nameField = new Zend_Form_Element_Text('name');
        $nameField->setRequired(true);
        $nameField->setAttrib('autocomplete', 'off');
        $nameField->setLabel('Account Name');
        
        $loginField = new Zend_Form_Element_Text('login');
        $loginField->setRequired(true);
        $loginField->setAttrib('autocomplete', 'off');
        $loginField->setLabel('Login');
        
        $passwordField = new Zend_Form_Element_Password('password');
        $passwordField->setRequired(true);
        $passwordField->setAttrib('autocomplete', 'off');
        $passwordField->setLabel('Password');
        
        $confirmPasswordField = new Zend_Form_Element_Password('confirmPassword');
        $confirmPasswordField->setRequired(true);
        $confirmPasswordField->setAttrib('autocomplete', 'off');
        $confirmPasswordField->setLabel('Confirm password');
        $confirmPasswordField->addValidator('identical', true, array('password'));
        
        $emailField = new Zend_Form_Element_Text('email');
        $emailField->addValidator('EmailAddress');
        $emailField->setRequired(true);
        $emailField->setLabel('Email');
        
        $dbForm = new Zend_Form();
        $dbForm->add($nameField);
        $dbForm->add($loginField);
        $dbForm->add($passwordField);
        $dbForm->add($confirmPasswordField);
        $dbForm->add($emailField);
        
        $dbForm = self::setForm($dbForm);
        
        return $dbForm;
        
    }
}

