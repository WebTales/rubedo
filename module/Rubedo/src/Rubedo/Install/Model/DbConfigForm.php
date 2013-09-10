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
use Zend\Form\Element\Number;
/**
 * Form for DB Config
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class DbConfigForm extends BootstrapForm
{

    public static function getForm ($params)
    {
        $serverNameField = new Text('server');
        $serverNameField->setAttribute('Required',true);
        $serverNameField->setValue(isset($params['server']) ? $params['server'] : 'localhost');
        $serverNameField->setLabel('Server Name');
        
        $serverPortField = new Number('port');
        $serverPortField->setValue(isset($params['port']) ? $params['port'] :27017);
        $serverPortField->setLabel('Server Port');
        
        $dbNameField = new Text('db');
        $dbNameField->setAttribute('Required',true);
        $dbNameField->setValue(isset($params['db']) ? $params['db'] : 'rubedo');
        $dbNameField->setLabel('Db Name');
        
        $serverLoginField = new Text('login');
        $serverLoginField->setValue(isset($params['login']) ? $params['login'] : null);
        $serverLoginField->setLabel('Username');
        
        $serverPasswordField = new Text('password');
        $serverPasswordField->setValue(isset($params['password']) ? $params['password'] : null);
        $serverPasswordField->setLabel('Password');
 
        $dbForm = new Form();
        $dbForm->add($serverNameField);
        $dbForm->add($serverPortField);
        $dbForm->add($dbNameField);
        $dbForm->add($serverLoginField);
        $dbForm->add($serverPasswordField);
        
        $dbForm = self::setForm($dbForm);
        
        return $dbForm;
    }
}

