<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(__DIR__ . '/../../..'));
defined('CONFIG_CACHE_DIR') || define('CONFIG_CACHE_DIR', realpath(__DIR__ . '/../../../cache/config'));
return array(
    'modules' => array(
        'Rubedo',
        'RubedoAPI',
        'ZendDeveloperTools',
    ),
    'module_listener_options' => array(
        'config_glob_paths' => array(
            '../../../config/autoload/{,*.}{global,local}.php',
        ),
        'module_paths' => array(
            'module',
            'vendor',
        ),
    ),
);