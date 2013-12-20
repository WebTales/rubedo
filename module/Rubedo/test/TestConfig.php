<?php
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(__DIR__ . '/../../..'));
defined('CONFIG_CACHE_DIR') || define('CONFIG_CACHE_DIR', realpath(__DIR__ . '/../../../cache/config'));
return array(
    'modules' => array(
        'Rubedo',
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            '../../../config/autoload/{,*.}{global,local}.php',
        ),
        'module_paths' => array(
            'module',
            'vendor',
        ),
    ),
);