<?php
// define a constant for the root dir of the application
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(__DIR__ . '/..'));

// automatically discover extensions for Rubedo
$extensionsArray = array();

$extensionsIterator = new DirectoryIterator('extensions');
foreach ($extensionsIterator as $item) {
    if ($item->isDot()) {
        continue;
    }
    if ($item->isDir()) {
        $extensionsArray[] = $item->getFilename();
    }
}
unset($extensionsIterator);

//return configuration array. Similar to standard ZF2 app with an "extensions directory" and a list of modules from this directory
return array(
    'modules' => array_merge(array(
        'Rubedo'
    ), $extensionsArray),
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
