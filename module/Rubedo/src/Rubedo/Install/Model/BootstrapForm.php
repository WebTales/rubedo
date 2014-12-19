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
            self::setIds($element);
        }

        $submitButton = (new Submit('Submit'))
            ->setValue('Submit')
            ->setAttribute('class', 'btn btn-large btn-primary')
            ->setAttribute('style', 'margin-right: 10px;');

        $resetButton = (new Button('Reset', array(
            'label' => 'Reset'
        )))
            ->setAttribute('class', 'btn btn-large btn-warning')
            ->setAttribute('type', 'reset');

        $buttonFieldSet = (new Fieldset('buttonGroup'))
            ->add($submitButton)
            ->add($resetButton)
            ->setAttribute('class', 'col-sm-offset-2 col-sm-10');

        $dbForm
            ->setAttribute('id', 'installForm')
            ->setAttribute('class', 'form-horizontal')
            ->add($buttonFieldSet);

        return $dbForm;
    }

    protected static function setIds($element)
    {
        if ($element instanceof FieldsetInterface) {
            foreach ($element as $subElement) {
                self::setIds($subElement);
            }
            $subElement->setAttribute('id', $subElement->getName());
        }
        $element->setLabelAttributes(array(
            'class' => 'control-label'
        ));
        $element->setAttribute('id', $element->getName());
    }
}

