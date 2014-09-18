<?php
/**
 * Rubedo -- ECM solution Copyright (c) 2014, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
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
            'basePath' => realpath(APPLICATION_PATH . '/public/components/webtales/rubedo-frontoffice')
        ),
    ),
);