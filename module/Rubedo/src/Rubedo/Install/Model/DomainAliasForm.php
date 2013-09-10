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
/**
 * Form for DB Config
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class DomainAliasForm extends BootstrapForm
{
    public static function getForm(){
               
        $domainField = new Text('domain');
        $domainField->setAttribute('Required',true);
        $domainField->setLabel('Site domain');
        
        $localDomainField = new Text('localDomain');
        $localDomainField->setAttribute('Required',true);
        $localDomainField->setLabel('Local domain');
        
        $dbForm = new Form();
        $dbForm->add($domainField);
        $dbForm->add($localDomainField);
        
        $dbForm = self::setForm($dbForm);
        
        return $dbForm;
    }
}

