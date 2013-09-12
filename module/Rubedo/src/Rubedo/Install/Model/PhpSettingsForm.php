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
use Zend\Form\Element\Checkbox;
use Zend\Form\Fieldset;

/**
 * Form for DB Config
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class PhpSettingsForm extends BootstrapForm
{

    public static function getForm($params)
    {
        $displayExceptions = new Checkbox('display_exceptions');
        $displayExceptions->setValue(isset($params['view_manager']['display_exceptions']) ? $params['view_manager']['display_exceptions'] : false);
        $displayExceptions->setLabel('Display application exceptions');
        
        $displayExceptionsFieldSet = new Fieldset('view_manager');
        $displayExceptionsFieldSet->add($displayExceptions);
        
        $rubedoConfigFieldset = new Fieldset('rubedo_config');
        
        $extDebug = new Checkbox('extDebug');
        $extDebug->setValue(isset($params['rubedo_config']['extDebug']) ? $params['rubedo_config']['extDebug'] : null);
        $extDebug->setLabel('Use debug mode of ExtJs');
        
        $sessionFieldset = new Fieldset('session');
        $sessionName = new Text('name');
        $sessionName->setAttribute('Required', true);
        $sessionName->setValue(isset($params['session']['name']) ? $params['session']['name'] : 'rubedo');
        $sessionName->setLabel('Name of the session cookie');
        $sessionFieldset->add($sessionName);
        
        $authLifetime = new Text('authLifetime');
        $authLifetime->setAttribute('Required', true);
        $authLifetime->setValue(isset($params['session']['remember_me_seconds']) ? $params['session']['remember_me_seconds'] : '3600');
        $authLifetime->setLabel('Session lifetime');
        $sessionFieldset->add($authLifetime);
        
        $defaultBackofficeHost = new Text('defaultBackofficeHost');
        $defaultBackofficeHost->setAttribute('Required', true);
        $defaultBackofficeHost->setValue(isset($params['rubedo_config']['defaultBackofficeHost']) ? $params['rubedo_config']['defaultBackofficeHost'] : $_SERVER['HTTP_HOST']);
        $defaultBackofficeHost->setLabel('Default backoffice domain');
        
        $isBackofficeSSL = new Checkbox('isBackofficeSSL');
        $isBackofficeSSL->setValue(isset($params['rubedo_config']['isBackofficeSSL']) ? $params['rubedo_config']['isBackofficeSSL'] : isset($_SERVER['HTTPS']));
        $isBackofficeSSL->setLabel('Use SSL for BackOffice');
        
        $enableEmailNotification = new Checkbox('enableEmailNotification');
        $enableEmailNotification->setValue(isset($params['rubedo_config']['enableEmailNotification']) ? $params['rubedo_config']['enableEmailNotification'] : false);
        $enableEmailNotification->setLabel('Enable email notifications');
        
        $fromEmailNotification = new Text('fromEmailNotification');
        $fromEmailNotification->setValue(isset($params['rubedo_config']['fromEmailNotification']) ? $params['rubedo_config']['fromEmailNotification'] : null);
        $fromEmailNotification->setLabel('Sender of notifications');
        
        $dbForm = new Form();
        $dbForm->add($displayExceptionsFieldSet);
        $rubedoConfigFieldset->add($extDebug);
        ;
        $rubedoConfigFieldset->add($defaultBackofficeHost);
        $rubedoConfigFieldset->add($isBackofficeSSL);
        $rubedoConfigFieldset->add($enableEmailNotification);
        $rubedoConfigFieldset->add($fromEmailNotification);
        $dbForm->add($rubedoConfigFieldset);
        $dbForm->add($sessionFieldset);
        
        $dbForm = self::setForm($dbForm);
        
        return $dbForm;
    }
}

