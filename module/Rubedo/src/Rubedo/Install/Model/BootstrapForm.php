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

use Zend\Form\Element\Submit;
use Zend\Form\Element\Button;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\Form\FieldsetInterface;

/**
 * Form
 * for
 * bootstrap
 *
 * @author
 *         jbourdin
 * @category
 *           Rubedo
 * @package
 *          Rubedo
 */
class BootstrapForm
{

    protected static function setForm(Form $dbForm)
    {
        foreach ($dbForm as $element) {
            if ($element instanceof FieldsetInterface) {
                foreach ($element as $subElement) {
                    $subElement->setLabelAttributes(array(
                        'class' => 'control-label'
                    ));
                }
                continue;
            }
            $element->setLabelAttributes(array(
                'class' => 'control-label'
            ));
        }
        
        $submitButton = new Submit('Submit');
        $submitButton->setValue('Submit');
        $submitButton->setAttribute('class', 'btn btn-large btn-primary');
        $resetButton = new Button('Reset', array(
            'label' => 'Reset'
        ));
        $resetButton->setAttribute('class', 'btn btn-large btn-warning');
        $resetButton->setAttribute('type', 'reset');
        $dbForm->setAttribute('id', 'installForm');
        $dbForm->setAttribute('class', 'form-horizontal');
        
        $buttonFieldSet = new Fieldset('buttonGroup');
        $buttonFieldSet->add($submitButton);
        $buttonFieldSet->add($resetButton);
        $buttonFieldSet->setAttribute('class', 'form-actions');
        $dbForm->add($buttonFieldSet);
        
        $dbForm->setAttribute('class', 'form-horizontal');
        
        return $dbForm;
    }
}

