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
 * Form
 * for
 * DB
 * Config
 *
 * @author
 *         jbourdin
 * @category
 *           Rubedo
 * @package
 *          Rubedo
 */
class EsConfigForm extends BootstrapForm
{

    public static function getForm($params)
    {
        $serverNameField = new Text('host');
        $serverNameField->setAttribute('Required', true);
        $serverNameField->setValue(isset($params['host']) ? $params['host'] : 'localhost');
        $serverNameField->setLabel('Server Name');
        
        $serverPortField = new Number('port');
        $serverPortField->setValue(isset($params['port']) ? $params['port'] : 9200);
        $serverPortField->setLabel('Server Port');
        
        $contentIndexField = new Text('contentIndex');
        $contentIndexField->setAttribute('Required', true);
        $contentIndexField->setValue(isset($params['contentIndex']) ? $params['contentIndex'] : 'contents');
        $contentIndexField->setLabel('Contents index name');
        
        $damIndexField = new Text('damIndex');
        $damIndexField->setAttribute('Required', true);
        $damIndexField->setValue(isset($params['damIndex']) ? $params['damIndex'] : 'dam');
        $damIndexField->setLabel('Dam index name');
        
        $userIndexField = new Text('userIndex');
        $userIndexField->setAttribute('Required', true);
        $userIndexField->setValue(isset($params['userIndex']) ? $params['userIndex'] : 'users');
        $userIndexField->setLabel('Users index name');
        
        $dbForm = new Form();
        $dbForm->add($serverNameField);
        $dbForm->add($serverPortField);
        $dbForm->add($contentIndexField);
        $dbForm->add($damIndexField);
        $dbForm->add($userIndexField);
        
        $dbForm = self::setForm($dbForm);
        
        return $dbForm;
    }
}

