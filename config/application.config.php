<?php
// define a constant for the root dir of the application
use Zend\Debug\Debug;

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(__DIR__ . '/..'));
defined('CONFIG_CACHE_DIR') || define('CONFIG_CACHE_DIR', realpath(__DIR__ . '/../cache/config'));
//$configCacheDir = realpath(__DIR__ . '/../cache/config');

if (file_exists(CONFIG_CACHE_DIR . '/extensions.array.php')) {
    $extensionsArray = include_once CONFIG_CACHE_DIR . '/extensions.array.php';
} else {
    // automatically discover extensions for Rubedo
    
    // you can manually add extensions if not handled by composer
    $extensionsArray = array();
    
    $ignoreExtName = array(
        'composer',
        'bin'
    );
    
    $extensionsIterator = new DirectoryIterator('extensions');
    foreach ($extensionsIterator as $item) {
        if ($item->isDot()) {
            continue;
        }
        if ($item->isDir()) {
            if (in_array($item->getFilename(), $ignoreExtName)) {
                continue;
            }
            $subIterator = new DirectoryIterator($item->getRealPath());
            foreach ($subIterator as $subItem) {
                if ($subItem->isDot() || strpos($subItem->getFilename(), '.') === 0) {
                    continue;
                }
                if (file_exists($subItem->getRealPath() . '/Module.php')) {
                    $namespace = array();
                    preg_match(
                        '#\/(\w+)\/\w+.php#Ui',
                        file_get_contents($subItem->getRealPath() . '/Module.php'),
                        $namespace
                    );
                    if ($namespace[1]) {
                        $extensionsArray[] = $namespace[1];
                    } else {
                        $extensionsArray[] = ucfirst($subItem->getFilename());
                    }
                }
            }
        }
    }
    unset($extensionsIterator);
    $configContent = "<?php \n return ".var_export($extensionsArray,true).";";
    file_put_contents(CONFIG_CACHE_DIR . '/extensions.array.php', $configContent);
}

// return configuration array. Similar to standard ZF2 app with an "extensions directory" and a list of modules from this directory
return array(
    'modules' => array_merge(array(
        'Rubedo',
        'ZF\\DevelopmentMode',
        'ZF\\Apigility',
        'ZF\\Apigility\\Provider',
        'ZF\\Apigility\\Documentation',
        'AssetManager',
        'ZF\\ApiProblem',
        'ZF\\Configuration',
        'ZF\\MvcAuth',
        'ZF\\OAuth2',
        'ZF\\Hal',
        'ZF\\ContentNegotiation',
        'ZF\\ContentValidation',
        'ZF\\Rest',
        'ZF\\Rpc',
        'ZF\\Versioning',
        'RubedoAPI'
    ), $extensionsArray),
    'module_listener_options' => array(
        'config_cache_enabled' => true,
        'module_map_cache_enabled' => true,
        'cache_dir' => CONFIG_CACHE_DIR,
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
