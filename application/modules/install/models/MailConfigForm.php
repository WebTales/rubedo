<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

/**
 * Form for DB Config
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Install_Model_MailConfigForm
{
    public static function getForm($params){
        
  
        
        
        $serverNameField = new Zend_Form_Element_Text('server');
        $serverNameField->setRequired(true);
        $serverNameField->setValue(isset($params['server']) ? $params['server'] : null);
        $serverNameField->setLabel('Server Name');
        
        $serverPortField = new Zend_Form_Element_Text('port');
        $serverPortField->setValue(isset($params['port']) ? $params['port'] : null);
        $serverPortField->addValidator('digits');
        $serverPortField->setLabel('Server Port');
        
        $sslField = new Zend_Form_Element_Checkbox('ssl');
        $sslField->setValue(isset($params['ssl']) ? $params['ssl'] : null);
        $sslField->setLabel('Use SSL');
        
        $loginField = new Zend_Form_Element_Text('username');
        $loginField->setValue(isset($params['username']) ? $params['username'] : null);
        $loginField->setLabel('User name');
        
        $passwordField = new Zend_Form_Element_Password('password');
        $passwordField->setRenderPassword(true);
        $passwordField->setValue(isset($params['password']) ? $params['password'] : null);
        $passwordField->setLabel('Password');

        $submitButton = new Zend_Form_Element_Submit('Submit');
        $submitButton->setAttrib('class', 'btn btn-large btn-primary');
        $resetButton = new Zend_Form_Element_Reset('Reset');
        $resetButton->setAttrib('class', 'btn btn-large btn-warning');
        
        $dbForm = new Zend_Form();
        $dbForm->setMethod('post');
        $dbForm->setAttrib('id', 'installForm');
        $dbForm->addElement($serverNameField);
        $dbForm->addElement($serverPortField);
        $dbForm->addElement($sslField);
        $dbForm->addElement($loginField);
        $dbForm->addElement($passwordField);
        $dbForm->addDisplayGroup(array(
            $resetButton,
            $submitButton
        ), 'buttons');
        $dbForm->getDisplayGroup('buttons')->setDecorators(array(
            
            'FormElements',
            array(
                'HtmlTag',
                array(
                    'tag' => 'div',
                    'class' => 'form-actions'
                )
            )
        ));
        foreach ($dbForm->getElements() as $element) {
            $element->removeDecorator('HtmlTag');
            if ($element->getDecorator('label')) {
                $element->removeDecorator('Label');
                $element->addDecorator('Label');
            }
        }
        foreach ($dbForm->getDisplayGroups() as $group) {
            foreach ($group->getElements() as $element) {
                    //$element->clearDecorators();
                    //$element->addDecorator('FormElements');
                $element->removeDecorator('HtmlTag');
                $element->removeDecorator('Label');
                $element->removeDecorator('Tooltip');
                $element->removeDecorator('DtDdWrapper');
            }
        }
        $dbForm->removeDecorator('HtmlTag');
        
        return $dbForm;
    }
}

