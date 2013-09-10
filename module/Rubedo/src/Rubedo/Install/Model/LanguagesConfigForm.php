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

use Zend\Form\Element\Select;
use Zend\Form\Form;
/**
 * Form for DB Config
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class LanguagesConfigForm extends BootstrapForm
{

    public static function getForm ($params)
    {
        $languageField = new Select('defaultLanguage');
        $languageField->setAttribute('Required',true);
        $languageField->setOptions(array('value_options'=>$params['languages']));
        $languageField->setValue($params['defaultLanguage']);
        
        $languageField->setLabel('Default language');
        
        
 
        $dbForm = new Form();
        $dbForm->add($languageField);
        
        
        $dbForm = self::setForm($dbForm);
        
        return $dbForm;
    }
}

