<?php
$themePath = realpath(APPLICATION_PATH.'/public/templates');

return array(
    'cache' => APPLICATION_PATH . "/cache/twig",
    'templateDir' => APPLICATION_PATH . "/public/templates",
    'debug' => false,
    'auto_reload' => true,
    'workspaces' => array(),
    'overrideThemes' => array(),
    'themes'=>array()
);