<?php

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(realpath(APPLICATION_PATH . '/../Core'),'/Users/jbourdin/Projects/phactory/lib/',  realpath(APPLICATION_PATH . '/../library'), get_include_path())));

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

require_once (APPLICATION_PATH . '/controllers/AbstractController.php');
require_once (APPLICATION_PATH . '/../tests/application/AbstractControllerTest.php');
