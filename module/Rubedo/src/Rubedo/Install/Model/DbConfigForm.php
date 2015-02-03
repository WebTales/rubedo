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

    public static function getForm($params)
    {
        $serverNameField = (new Text('server'))
            ->setAttribute('Required', true)
            ->setValue(isset($params['server']) ? $params['server'] : 'localhost')
            ->setLabel('Server Name')
            ->setAttribute('class', 'form-control');

        $replicaSetNameField = (new Text('replicaSetName'))
        ->setAttribute('Required', true)
        ->setValue(isset($params['replicaSetName']) ? $params['replicaSetName'] : '')
        ->setLabel('Replica Set Name')
        ->setAttribute('class', 'form-control');        
        
        $serverPortField = (new Number('port'))
            ->setValue(isset($params['port']) ? $params['port'] : 27017)
            ->setLabel('Server Port')
            ->setAttribute('class', 'form-control');

        $dbNameField = (new Text('db'))
            ->setAttribute('Required', true)
            ->setValue(isset($params['db']) ? $params['db'] : 'rubedo')
            ->setLabel('Db Name')
            ->setAttribute('class', 'form-control');

        $serverAdminLoginField = (new Text('adminLogin'))
            ->setValue(isset($params['adminLogin']) ? $params['adminLogin'] : null)
            ->setLabel('Admin username (not saved)')
            ->setAttribute('class', 'form-control');

        $serverAdminPasswordField = (new Text('adminPassword'))
            ->setValue(isset($params['adminPassword']) ? $params['adminPassword'] : null)
            ->setLabel('Admin password (not saved)')
            ->setAttribute('class', 'form-control');

        $serverLoginField = (new Text('login'))
            ->setValue(isset($params['login']) ? $params['login'] : null)
            ->setLabel('Username')
            ->setAttribute('class', 'form-control');

        $serverPasswordField = (new Text('password'))
            ->setValue(isset($params['password']) ? $params['password'] : null)
            ->setLabel('Password')
            ->setAttribute('class', 'form-control');

        $dbForm = new Form();
        $dbForm->add($serverNameField);
        $dbForm->add($replicaSetNameField);
        $dbForm->add($serverPortField);
        $dbForm->add($dbNameField);
        $dbForm->add($serverAdminLoginField);
        $dbForm->add($serverAdminPasswordField);
        $dbForm->add($serverLoginField);
        $dbForm->add($serverPasswordField);

        $dbForm = self::setForm($dbForm);

        return $dbForm;
    }
}

