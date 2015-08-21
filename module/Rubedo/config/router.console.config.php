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

/**
 * Configure all routes for Rubedo
 */
return array(
    'routes' => array(
        'clear-cache' => array(
            'options' => array(
                'route'    => 'cache clear [config|files|mongo|url|api]:name',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Cache',
                    'action'     => 'clear'
                )
            )
        ),
        'count-cache' => array(
            'options' => array(
                'route'    => 'cache count',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Cache',
                    'action'     => 'count'
                )
            )
        ),
    )
);
