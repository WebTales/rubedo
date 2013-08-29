<?php
/**
 * Rubedo -- ECM solution Copyright (c) 2013, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
$serviceMapArray = include (__DIR__ . '/services.config.php');
$controllerArray = include (__DIR__ . '/controllers.config.php');
$viewArray = include (__DIR__ . '/views.config.php');
$localizationConfig = include (__DIR__ . '/localization.config.php');
$router = include (__DIR__ . '/router.config.php');

foreach ($serviceMapArray as $key => $value) {
    $serviceSharedMapArray[$key] = false;
}

$config = array(
    'router' => $router,
    'controllers' => array(
        'invokables' => $controllerArray
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => $viewArray,
        'template_path_stack' => array(
            __DIR__ . '/../view'
        ),
        'strategies' => array(
            'ViewJsonStrategy'
        )
    ),
    'service_manager' => array(
        'invokables' => $serviceMapArray,
        'shared' => $serviceSharedMapArray
    ),
    'backoffice' => array(
        'extjs' => array(
            'debug' => '0',
            'network' => 'local',
            'version' => '4.1.1'
        )
    ),
    'localisationfiles' => $localizationConfig,
    'site' => array()
);

$sessionLifeTime = 3600;

$config['session'] = array(
    'remember_me_seconds' => $sessionLifeTime,
    'use_cookies' => true,
    'cookie_httponly' => false,
    'gc_maxlifetime' => $sessionLifeTime,
    'name' => 'rubedo',
    'cookie_httponly' => true
);

$config['datastream'] = array();

$config['datastream']['mongo'] = array(
    'server' => 'localhost',
    'port' => '27017',
    'db' => 'rubedo',
    'login' => '',
    'password' => ''
);

$config['elastic'] = array(
    "host" => "localhost",
    "port" => "9200",
    "contentIndex" => "contents",
    "damIndex" => "dam"
);

return $config;
