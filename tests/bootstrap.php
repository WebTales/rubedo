<?php

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));

// Path used for composer Libraries
defined('VENDOR_PATH') || define('VENDOR_PATH', realpath(dirname(__FILE__) . '/../vendor/'));

// Ensure VENDOR_PATH is on include_path
if(!defined('INCLUDE_PATH_CONFIGURED')){
	set_include_path(implode(PATH_SEPARATOR, array(realpath(APPLICATION_PATH . '/../Core'),realpath(VENDOR_PATH), get_include_path())));
	define('INCLUDE_PATH_CONFIGURED',true);
}


require_once 'autoload.php';

Zend_Session::$_unitTestEnabled = true;

function testBootstrap(){
    $optionsObject = new Zend_Config(require APPLICATION_PATH . '/configs/application.config.php', array(
        'allowModifications' => true
    ));
    if (is_file(APPLICATION_PATH . '/configs/local/config.json')) {
        $localOptionsObject = new Zend_Config_Json(APPLICATION_PATH . '/configs/local/config.json');
        $optionsObject->merge($localOptionsObject);
    }elseif (is_file(APPLICATION_PATH . '/configs/local.ini')) {
        $localOptionsObject = new Zend_Config_Ini(APPLICATION_PATH . '/configs/local.ini');
        $optionsObject->merge($localOptionsObject);
    }
    
    $options = $optionsObject->toArray();
    
    $bootstrap = new Zend_Application(APPLICATION_ENV, $options);
    $bootstrap->bootstrap();

    Zend_Loader_Autoloader::getInstance();
    
    Rubedo\Collection\AbstractCollection::disableUserFilter();
    return $bootstrap;
}

testBootstrap();
Rubedo\Exceptions\AbstractException::setDoNotTranslate(true);

require_once (APPLICATION_PATH . '/../tests/application/AbstractControllerTest.php');
