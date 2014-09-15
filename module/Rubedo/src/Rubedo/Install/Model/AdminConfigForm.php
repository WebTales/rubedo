<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
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
    public static function getForm()
    {
        $nameField = (new Text('name'))
            ->setAttribute('Required',true)
            ->setAttribute('autocomplete', 'off')
            ->setLabel('Account Name')
            ->setAttribute('class', 'form-control');

        $loginField = (new Text('login'))
            ->setAttribute('Required',true)
            ->setAttribute('autocomplete', 'off')
            ->setLabel('Login')
            ->setAttribute('class', 'form-control');

        $passwordField = (new Password('password'))
            ->setAttribute('Required',true)
            ->setAttribute('autocomplete', 'off')
            ->setLabel('Password')
            ->setAttribute('class', 'form-control');
        
        $confirmPasswordField = (new Password('confirmPassword'))
            ->setAttribute('Required',true)
            ->setAttribute('autocomplete', 'off')
            ->setLabel('Confirm password')
            ->setAttribute('class', 'form-control');
        
        $emailField = (new Email('email'))
            ->setAttribute('Required',true)
            ->setLabel('Email')
            ->setAttribute('class', 'form-control');
        
        $inputFilter = (new InputFilter())
            ->add(array(
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
            ))
            ->add(array(
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
        
        $dbForm = (new Form())
            ->add($nameField)
            ->add($loginField)
            ->add($passwordField)
            ->add($confirmPasswordField)
            ->add($emailField)
            ->setInputFilter($inputFilter);
        return self::setForm($dbForm);
    }
}

