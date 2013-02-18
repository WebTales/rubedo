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
class Install_Model_DomainAliasForm
{
    public static function getForm(){
        
  
        
        
        $domainField = new Zend_Form_Element_Text('domain');
        $domainField->setRequired(true);
        $domainField->setLabel('Site domain');
        
        $localDomainField = new Zend_Form_Element_Text('localDomain');
        $localDomainField->setRequired(true);
        $localDomainField->setLabel('Local domain');
        

        $submitButton = new Zend_Form_Element_Submit('Submit');
        $submitButton->setAttrib('class', 'btn btn-large btn-primary');
        
        $dbForm = new Zend_Form();
        $dbForm->setMethod('post');
        $dbForm->setAttrib('id', 'domainForm');
        $dbForm->addElement($domainField);
        $dbForm->addElement($localDomainField);
        $dbForm->addElement($submitButton);
        
        return $dbForm;
    }
}

