<?php
$themePath = realpath(APPLICATION_PATH . '/public/templates');

return array(
    'cache' => APPLICATION_PATH . "/cache/twig",
    'rootTemplateDir' => $themePath . "/root",
    'templateDir' => APPLICATION_PATH . "/public/templates",
    'debug' => false,
    'auto_reload' => true,
    'namespaces' => array(),
    'overrideThemes' => array(),
    'themes' => array(
        'default' => array(
            'label' => 'Default',
            'basePath' => $themePath . '/default'
        ),
        'amelia' => array(
            'label' => 'Amelia',
            'basePath' => $themePath . '/amelia'
        ),
        'cerulean' => array(
            'label' => 'Cerulean',
            'basePath' => $themePath . '/cerulean'
        ),
        'customtheme' => array(
            'label' => 'Custom Theme',
            'basePath' => $themePath . '/customtheme'
        ),
        'cyborg' => array(
            'label' => 'Cyborg',
            'basePath' => $themePath . '/cyborg'
        ),
        'journal' => array(
            'label' => 'Journal',
            'basePath' => $themePath . '/journal'
        ),
        'readable' => array(
            'label' => 'Readable',
            'basePath' => $themePath . '/readable'
        ),
        'simplex' => array(
            'label' => 'Simplex',
            'basePath' => $themePath . '/simplex'
        ),
        'slate' => array(
            'label' => 'Slate',
            'basePath' => $themePath . '/slate'
        ),
        'spruce' => array(
            'label' => 'Spruce',
            'basePath' => $themePath . '/spruce'
        ),
        'superhero' => array(
            'label' => 'Superhero',
            'basePath' => $themePath . '/superhero'
        ),
        'united' => array(
            'label' => 'United',
            'basePath' => $themePath . '/united'
        )
    )
);