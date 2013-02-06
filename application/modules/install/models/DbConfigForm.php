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
class Install_Model_DbConfigForm
{
    public static function getForm($params){
        
        $serverNameField = new Zend_Form_Element_Text('server');
        $serverNameField->setRequired(true);
        $serverNameField->setValue(isset($params['server']) ? $params['server'] : 'localhost/rubedo');
        $serverNameField->setLabel('Server Name');
        
        $serverPortField = new Zend_Form_Element_Text('serverport');
        // $serverPortField->setRequired(true);
        $serverPortField->setValue(isset($params['port']) ? $params['port'] : null);
        $serverPortField->addValidator('digits');
        $serverPortField->setLabel('Server Port');
        
        $dbNameField = new Zend_Form_Element_Text('db');
        $dbNameField->setRequired(true);
        $dbNameField->setValue(isset($params['db']) ? $params['db'] : null);
        $dbNameField->setLabel('Db Name');
        
        $serverLoginField = new Zend_Form_Element_Text('login');
        $serverLoginField->setValue(isset($params['login']) ? $params['login'] : null);
        $serverLoginField->setLabel('Username');
        
        $serverPasswordField = new Zend_Form_Element_Text('password');
        $serverPasswordField->setValue(isset($params['password']) ? $params['password'] : null);
        $serverPasswordField->setLabel('Password');
        
        $submitButton = new Zend_Form_Element_Submit('Submit');
        $submitButton->setAttrib('class', 'btn btn-large btn-primary');
        
        $dbForm = new Zend_Form();
        $dbForm->setMethod('post');
        $dbForm->setAttrib('id', 'dbForm');
        $dbForm->addElement($serverNameField);
        // $dbForm->addElement($serverPortField);
        $dbForm->addElement($dbNameField);
        $dbForm->addElement($serverLoginField);
        $dbForm->addElement($serverPasswordField);
        $dbForm->addElement($submitButton);
        
        return $dbForm;
    }
}

