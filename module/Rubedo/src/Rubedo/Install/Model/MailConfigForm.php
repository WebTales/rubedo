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
        
        $serverNameField = (new Text('server'))
            ->setAttribute('Required',true)
            ->setValue(isset($params['server']) ? $params['server'] : null)
            ->setLabel('Server Name')
            ->setAttribute('class', 'form-control');
        
        $serverPortField = (new Number('port'))
            ->setAttribute('Required',true)
            ->setValue(isset($params['port']) ? $params['port'] : null)
            ->setLabel('Server Port')
            ->setAttribute('class', 'form-control');
        
        $sslField = (new Checkbox('ssl'))
            ->setValue(isset($params['ssl']) ? $params['ssl'] : null)
            ->setLabel('Use SSL')
            ->setAttribute('class', 'checkbox');

        $loginField = (new Text('username'))
            ->setValue(isset($params['username']) ? $params['username'] : null)
            ->setLabel('User name')
            ->setAttribute('class', 'form-control');
        
        $passwordField = (new Password('password'))
            ->setValue(isset($params['password']) ? $params['password'] : null)
            ->setLabel('Password')
            ->setAttribute('class', 'form-control');

        
        $dbForm = (new Form())
            ->add($serverNameField)
            ->add($serverPortField)
            ->add($sslField)
            ->add($loginField)
            ->add($passwordField);
        
        return self::setForm($dbForm);
    }
}

