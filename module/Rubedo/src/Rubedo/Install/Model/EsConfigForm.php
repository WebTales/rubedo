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
        $serverNameField = (new Text('host'))
            ->setAttribute('Required', true)
            ->setValue(isset($params['host']) ? $params['host'] : '')
            ->setLabel('Host(s)')
            ->setAttribute('class', 'form-control')
            ->setAttribute('placeholder', 'host1, host2:9200');

        $contentIndexField = (new Text('contentIndex'))
            ->setAttribute('Required', true)
            ->setValue(isset($params['contentIndex']) ? $params['contentIndex'] : 'contents')
            ->setLabel('Contents index name')
            ->setAttribute('class', 'form-control');

        $damIndexField = (new Text('damIndex'))
            ->setAttribute('Required', true)
            ->setValue(isset($params['damIndex']) ? $params['damIndex'] : 'dam')
            ->setLabel('Dam index name')
            ->setAttribute('class', 'form-control');

        $userIndexField = (new Text('userIndex'))
            ->setAttribute('Required', true)
            ->setValue(isset($params['userIndex']) ? $params['userIndex'] : 'users')
            ->setLabel('Users index name')
            ->setAttribute('class', 'form-control');

        $dbForm = new Form();
        $dbForm->add($serverNameField);
        $dbForm->add($contentIndexField);
        $dbForm->add($damIndexField);
        $dbForm->add($userIndexField);

        $dbForm = self::setForm($dbForm);

        return $dbForm;
    }
}

