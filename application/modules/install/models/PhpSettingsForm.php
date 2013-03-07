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
class Install_Model_PhpSettingsForm extends Install_Model_BootstrapForm
{
    public static function getForm($params){
        
        
        
        $displayStartupErrors = new Zend_Form_Element_Checkbox('display_startup_errors');
        $displayStartupErrors->setValue(isset($params['display_startup_errors']) ? $params['display_startup_errors'] : null);
        $displayStartupErrors->setLabel('Display Startup Errors');
        
        $displayErrors = new Zend_Form_Element_Checkbox('display_errors');
        $displayErrors->setValue(isset($params['display_errors']) ? $params['display_errors'] : null);
        $displayErrors->setLabel('Display Errors');
        
        $displayExceptions = new Zend_Form_Element_Checkbox('displayExceptions');
        $displayExceptions->setValue(isset($params['displayExceptions']) ? $params['displayExceptions'] : null);
        $displayExceptions->setLabel('Display Exceptions');
        
        $dbForm = new Zend_Form();
        $dbForm->addElement($displayStartupErrors);
        $dbForm->addElement($displayErrors);
        $dbForm->addElement($displayExceptions);
        
        $dbForm = self::setForm($dbForm);
        
        return $dbForm;
    }
}

