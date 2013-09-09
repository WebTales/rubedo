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
class Install_Model_EsConfigForm extends Install_Model_BootstrapForm
{
    public static function getForm($params){
        
        
        
        $serverNameField = new Zend_Form_Element_Text('host');
        $serverNameField->setRequired(true);
        $serverNameField->setValue(isset($params['host']) ? $params['host'] : 'localhost');
        $serverNameField->setLabel('Server Name');
        
        $serverPortField = new Zend_Form_Element_Text('port');
        $serverPortField->setValue(isset($params['port']) ? $params['port'] : 9200);
        $serverPortField->addValidator('digits');
        $serverPortField->setLabel('Server Port');
        
        $contentIndexField = new Zend_Form_Element_Text('contentIndex');
        $contentIndexField->setRequired(true);
        $contentIndexField->setValue(isset($params['contentIndex']) ? $params['contentIndex'] : 'contents');
        $contentIndexField->setLabel('Contents index name');
        
        $damIndexField = new Zend_Form_Element_Text('damIndex');
        $damIndexField->setRequired(true);
        $damIndexField->setValue(isset($params['damIndex']) ? $params['damIndex'] : 'dam');
        $damIndexField->setLabel('Dam index name');

        $dbForm = new Zend_Form();
        $dbForm->add($serverNameField);
        $dbForm->add($serverPortField);
        $dbForm->add($contentIndexField);
        $dbForm->add($damIndexField);

        $dbForm = self::setForm($dbForm);
        
        return $dbForm;
    }
}

