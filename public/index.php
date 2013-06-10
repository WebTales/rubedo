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

// Define default timezone
date_default_timezone_set('Europe/Paris');

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Path used for composer Library
defined('VENDOR_PATH') || define('VENDOR_PATH', realpath(dirname(__FILE__) . '/../vendor/'));

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure VENDOR_PATH is on include_path
if (! defined('INCLUDE_PATH_CONFIGURED')) {
    set_include_path(implode(PATH_SEPARATOR, array(
        realpath(APPLICATION_PATH . '/../Core'),
        realpath(VENDOR_PATH),
        get_include_path()
    )));
    define('INCLUDE_PATH_CONFIGURED', true);
}

require_once 'autoload.php';

$optionsObject = new Zend_Config(require APPLICATION_PATH . '/configs/application.config.php', array(
    'allowModifications' => true
));




if (is_file(APPLICATION_PATH . '/configs/local/config.json')) {
    $localOptionsObject = new Zend_Config_Json(
            APPLICATION_PATH . '/configs/local/config.json');
    $optionsObject->merge($localOptionsObject);
} elseif (is_file(APPLICATION_PATH . '/configs/local.ini')) {
    $localOptionsObject = new Zend_Config_Ini(
            APPLICATION_PATH . '/configs/local.ini');
    $optionsObject->merge($localOptionsObject);
}

$options = $optionsObject->toArray();

// Create application, bootstrap, and run
$application = new Zend_Application(APPLICATION_ENV, $options);
$application->bootstrap()->run();