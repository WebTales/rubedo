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
$themePath = realpath(APPLICATION_PATH . '/templates');

/**
 * Configuration for templates (twig environment)
 */
return array(
    'cache' => APPLICATION_PATH . "/cache/twig",
    'rootTemplateDir' => $themePath . "/root",
    'templateDir' => APPLICATION_PATH . "/templates",
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