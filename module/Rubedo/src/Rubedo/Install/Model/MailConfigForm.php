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
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Number;
/**
 * Form for DB Config
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class MailConfigForm extends BootstrapForm
{
    public static function getForm($params){
        
        $serverNameField = new Text('server');
        $serverNameField->setAttribute('Required',true);
        $serverNameField->setValue(isset($params['server']) ? $params['server'] : null);
        $serverNameField->setLabel('Server Name');
        
        $serverPortField = new Number('port');
        $serverPortField->setAttribute('Required',true);
        $serverPortField->setValue(isset($params['port']) ? $params['port'] : null);
        $serverPortField->setLabel('Server Port');
        
        $sslField = new Checkbox('ssl');
        $sslField->setValue(isset($params['ssl']) ? $params['ssl'] : null);
        $sslField->setLabel('Use SSL');
        
        $loginField = new Text('username');
        $loginField->setValue(isset($params['username']) ? $params['username'] : null);
        $loginField->setLabel('User name');
        
        $passwordField = new Password('password');
        $passwordField->setValue(isset($params['password']) ? $params['password'] : null);
        $passwordField->setLabel('Password');

        
        $dbForm = new Form();
        $dbForm->add($serverNameField);
        $dbForm->add($serverPortField);
        $dbForm->add($sslField);
        $dbForm->add($loginField);
        $dbForm->add($passwordField);
        
        $dbForm = self::setForm($dbForm);
        
        return $dbForm;
    }
}

