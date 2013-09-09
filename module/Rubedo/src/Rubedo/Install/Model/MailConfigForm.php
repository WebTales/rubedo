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
class Install_Model_MailConfigForm extends Install_Model_BootstrapForm
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

        
        $dbForm = new Zend_Form();
        $dbForm->add($serverNameField);
        $dbForm->add($serverPortField);
        $dbForm->add($sslField);
        $dbForm->add($loginField);
        $dbForm->add($passwordField);
        
        $dbForm = self::setForm($dbForm);
        
        return $dbForm;
    }
}

