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

use Monolog\Logger;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Form\Fieldset;
use Zend\Form\Form;

/**
 * Form for DB Config
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class PhpSettingsForm extends BootstrapForm
{

    public static function getForm($params)
    {
        $displayExceptions = (new Checkbox('display_exceptions'))
            ->setValue(isset($params['view_manager']['display_exceptions']) ? $params['view_manager']['display_exceptions'] : false)
            ->setLabel('Display application exceptions');

        $displayExceptionsFieldSet = (new Fieldset('view_manager'))
            ->add($displayExceptions)
            ->setAttribute('legend', 'Exception screen');

        $zendToolbarEnabled = (new Checkbox('enabled'))
            ->setValue(isset($params['zenddevelopertools']['toolbar']['enabled']) ? $params['zenddevelopertools']['toolbar']['enabled'] : false)
            ->setLabel('Show zend toolbar');

        $zendToolbar = (new Fieldset('toolbar'))
            ->add($zendToolbarEnabled);

        $zenddevelopertools = (new Fieldset('zenddevelopertools'))
            ->add($zendToolbar)
            ->setAttribute('legend', 'Zend developer tools');

        $chromePHPHandler = (new Checkbox('ChromePHPHandler'))
            ->setLabel('ChromePHPHandler')
            ->setValue($params['logger']['enableHandler']['ChromePHPHandler']);

        $firePHPHandler = (new Checkbox('FirePHPHandler'))
            ->setLabel('FirePHPHandler')
            ->setValue($params['logger']['enableHandler']['FirePHPHandler']);

        $mongoDBHandler = (new Checkbox('MongoDBHandler'))
            ->setLabel('MongoDBHandler')
            ->setValue($params['logger']['enableHandler']['MongoDBHandler']);

        $streamHandler = (new Checkbox('StreamHandler'))
            ->setLabel('Files')
            ->setValue($params['logger']['enableHandler']['StreamHandler']);

        $enableLoggerFieldSet = (new Fieldset('enableHandler'))
            ->setAttribute('legend', 'Error log Handler')
            ->add($chromePHPHandler)
            ->add($firePHPHandler)
            ->add($mongoDBHandler)
            ->add($streamHandler);

        $levels = array_flip(Logger::getLevels());
        $levelSelect = (new Select('errorLevel'))
            ->setLabel('Reporting Level')
            ->setValue($params['logger']['errorLevel'])
            ->setOptions(array(
                'value_options' => $levels
            ))
            ->setAttribute('class', 'form-control');

        $loggerFieldSet = (new Fieldset('logger'))
            ->add($enableLoggerFieldSet)
            ->add($levelSelect);

        $minify = (new Checkbox('minify'))
            ->setValue(isset($params['rubedo_config']['minify']) ? $params['rubedo_config']['minify'] : 1)
            ->setLabel('Minify CSS & Js');

        $cachePage = (new Checkbox('cachePage'))
            ->setValue(isset($params['rubedo_config']['cachePage']) ? $params['rubedo_config']['cachePage'] : 1)
            ->setLabel('Cache page');

        $apiCache = (new Checkbox('apiCache'))
            ->setValue(isset($params['rubedo_config']['apiCache']) ? $params['rubedo_config']['apiCache'] : 1)
            ->setLabel('API cache');

        $useCdn = (new Checkbox('useCdn'))
            ->setValue(isset($params['rubedo_config']['useCdn']) ? $params['rubedo_config']['useCdn'] : 0)
            ->setLabel('Use CDN');

        $extDebug = (new Checkbox('extDebug'))
            ->setValue(isset($params['rubedo_config']['extDebug']) ? $params['rubedo_config']['extDebug'] : null)
            ->setLabel('Use debug mode of ExtJs');

        $eCommerce = (new Checkbox('addECommerce'))
            ->setValue(isset($params['rubedo_config']['addECommerce']) ? $params['rubedo_config']['addECommerce'] : 1)
            ->setLabel('Activate e-commerce features');

        $magicActivator = (new Checkbox('activateMagic'))
            ->setValue(isset($params['rubedo_config']['activateMagic']) ? $params['rubedo_config']['activateMagic'] : 0)
            ->setLabel('Activate Magic Queries');

        $sessionName = (new Text('name'))
            ->setAttribute('Required', true)
            ->setValue(isset($params['session']['name']) ? $params['session']['name'] : 'rubedo')
            ->setLabel('Name of the session cookie')
            ->setAttribute('class', 'form-control');

        $authLifetime = (new Text('authLifetime'))
            ->setAttribute('Required', true)
            ->setValue(isset($params['session']['remember_me_seconds']) ? $params['session']['remember_me_seconds'] : '3600')
            ->setLabel('Session lifetime')
            ->setAttribute('class', 'form-control');

        $sessionFieldset = (new Fieldset('session'))
            ->setAttribute('legend', 'Session parameters')
            ->add($sessionName)
            ->add($authLifetime);

        $defaultBackofficeHost = (new Text('defaultBackofficeHost'))
            ->setAttribute('Required', true)
            ->setValue(isset($params['rubedo_config']['defaultBackofficeHost']) ? $params['rubedo_config']['defaultBackofficeHost'] : $_SERVER['HTTP_HOST'])
            ->setLabel('Default backoffice domain')
            ->setAttribute('class', 'form-control');

        $isBackofficeSSL = (new Checkbox('isBackofficeSSL'))
            ->setValue(isset($params['rubedo_config']['isBackofficeSSL']) ? $params['rubedo_config']['isBackofficeSSL'] : isset($_SERVER['HTTPS']))
            ->setLabel('Use SSL for BackOffice');

        $enableEmailNotification = (new Checkbox('enableEmailNotification'))
            ->setValue(isset($params['rubedo_config']['enableEmailNotification']) ? $params['rubedo_config']['enableEmailNotification'] : false)
            ->setLabel('Enable email notifications');

        $fromEmailNotification = (new Text('fromEmailNotification'))
            ->setValue(isset($params['rubedo_config']['fromEmailNotification']) ? $params['rubedo_config']['fromEmailNotification'] : null)
            ->setLabel('Sender address')
            ->setAttribute('class', 'form-control');
        $fromEmailNotificationName = (new Text('fromEmailNotificationName'))
            ->setValue(isset($params['rubedo_config']['fromEmailNotificationName']) ? $params['rubedo_config']['fromEmailNotificationName'] : null)
            ->setLabel('Sender name')
            ->setAttribute('class', 'form-control');

        $recaptchaPublicKey = (new Text('public_key'))
            ->setValue(isset($params['rubedo_config']['recaptcha']['public_key']) ? $params['rubedo_config']['recaptcha']['public_key'] : null)
            ->setLabel('Public key')
            ->setAttribute('class', 'form-control');

        $recaptchaPrivateKey = (new Text('private_key'))
            ->setValue(isset($params['rubedo_config']['recaptcha']['private_key']) ? $params['rubedo_config']['recaptcha']['private_key'] : null)
            ->setLabel('Private key')
            ->setAttribute('class', 'form-control');

        $recaptchaFieldSet = (new Fieldset('recaptcha'))
            ->add($recaptchaPublicKey)
            ->add($recaptchaPrivateKey)
            ->setAttribute('legend', 'Recaptcha parameters');

        $rubedoConfigFieldset = (new Fieldset('rubedo_config'))
            ->setAttribute('legend', 'Specific Rubedo options')
            ->add($minify)
            ->add($cachePage)
            ->add($apiCache)
            ->add($useCdn)
            ->add($extDebug)
            ->add($eCommerce)
            ->add($magicActivator)
            ->add($defaultBackofficeHost)
            ->add($isBackofficeSSL)
            ->add($enableEmailNotification)
            ->add($fromEmailNotification)
            ->add($fromEmailNotificationName)
            ->add($recaptchaFieldSet);

        $dbForm = (new Form())
            ->add($displayExceptionsFieldSet)
            ->add($zenddevelopertools)
            ->add($loggerFieldSet)
            ->add($sessionFieldset)
            ->add($rubedoConfigFieldset);


        return self::setForm($dbForm);
    }
}

