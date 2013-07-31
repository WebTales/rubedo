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
class Install_Model_LanguagesConfigForm extends Install_Model_BootstrapForm
{

    public static function getForm ($params)
    {
        $languageField = new Zend_Form_Element_Select('defaultLanguage');
        $languageField->setRequired(true);
        $languageField->addMultiOptions($params['languages']);
        $languageField->setValue($params['defaultLanguage']);
        
        $languageField->setLabel('Default language');
        
        
 
        $dbForm = new Zend_Form();
        $dbForm->addElement($languageField);
        
        
        $dbForm = self::setForm($dbForm);
        
        return $dbForm;
    }
}

