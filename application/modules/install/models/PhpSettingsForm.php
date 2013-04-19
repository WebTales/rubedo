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
class Install_Model_PhpSettingsForm extends Install_Model_BootstrapForm
{
    public static function getForm($params){
        
        
        
//         $displayStartupErrors = new Zend_Form_Element_Checkbox('display_startup_errors');
//         $displayStartupErrors->setValue(isset($params['display_startup_errors']) ? $params['display_startup_errors'] : null);
//         $displayStartupErrors->setLabel('Display Startup Errors');
        
        
        $displayErrors = new Zend_Form_Element_Checkbox('display_errors');
        $displayErrors->setValue(isset($params['display_errors']) ? $params['display_errors'] : null);
        $displayErrors->setLabel('Display PHP Errors');
        
        $displayExceptions = new Zend_Form_Element_Checkbox('displayExceptions');
        $displayExceptions->setValue(isset($params['displayExceptions']) ? $params['displayExceptions'] : null);
        $displayExceptions->setLabel('Display application exceptions');
        
        $extDebug = new Zend_Form_Element_Checkbox('extDebug');
        $extDebug->setValue(isset($params['extDebug']) ? $params['extDebug'] : null);
        $extDebug->setLabel('Use debug mode of ExtJs');
        
        $sessionName = new Zend_Form_Element_Text('sessionName');
        $sessionName->setRequired(true);
        $sessionName->setValue(isset($params['sessionName']) ? $params['sessionName'] : 'rubedo');
        $sessionName->setLabel('Name of the session cookie');
        
        $authLifetime = new Zend_Form_Element_Text('authLifetime');
        $authLifetime->setRequired(true);
        $authLifetime->setValue(isset($params['authLifetime']) ? $params['authLifetime'] : '3600');
        $authLifetime->setLabel('Session lifetime');
        
        $defaultBackofficeHost = new Zend_Form_Element_Text('defaultBackofficeHost');
        $defaultBackofficeHost->setRequired(true);
        $defaultBackofficeHost->setValue(isset($params['defaultBackofficeHost']) ? $params['defaultBackofficeHost'] : $_SERVER['HTTP_HOST']);
        $defaultBackofficeHost->setLabel('Default backoffice domain');
        
        $isBackofficeSSL = new Zend_Form_Element_Checkbox('isBackofficeSSL');
        $isBackofficeSSL->setValue(isset($params['isBackofficeSSL']) ? $params['isBackofficeSSL'] : isset($_SERVER['HTTPS']));
        $isBackofficeSSL->setLabel('Use SSL for BackOffice');
        
        $enableEmailNotification = new Zend_Form_Element_Checkbox('enableEmailNotification');
        $enableEmailNotification->setValue(isset($params['enableEmailNotification']) ? $params['enableEmailNotification'] : false);
        $enableEmailNotification->setLabel('Enable email notifications');
        
        $fromEmailNotification = new Zend_Form_Element_Text('fromEmailNotification');
        $fromEmailNotification->setValue(isset($params['fromEmailNotification']) ? $params['fromEmailNotification']:null);
        $fromEmailNotification->setLabel('Sender of notifications');
        
        
        $dbForm = new Zend_Form();
        //$dbForm->addElement($displayStartupErrors);
        $dbForm->addElement($displayErrors);
        $dbForm->addElement($displayExceptions);
        $dbForm->addElement($extDebug);
        $dbForm->addElement($sessionName);
        $dbForm->addElement($authLifetime);
        $dbForm->addElement($defaultBackofficeHost);
        $dbForm->addElement($isBackofficeSSL);
        $dbForm->addElement($enableEmailNotification);
        $dbForm->addElement($fromEmailNotification);
        
        
        
        
        $dbForm = self::setForm($dbForm);
        
        return $dbForm;
    }
}

