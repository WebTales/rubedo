<?php
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(__DIR__ . '/..'));

return array(
    'modules' => array(
        'Rubedo'
    ),
    'module_listener_options' => array(
        'config_cache_enabled' => true,
        'module_map_cache_enabled' => true,
        'cache_dir' => 'cache/config',
        'module_paths' => array(
            './module',
            './vendor',
            './extensions'
        ),
        'config_glob_paths' => array(
            'config/autoload/{,*.}{global,local}.php'
        )
    ),
    'phpSettings' => array(
        'display_startup_errors' => '1',
        'display_errors' => '1'
    )
);
