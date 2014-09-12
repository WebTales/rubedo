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
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

return array(
    'router' => array(
        'routes' => array(
            // route for different frontoffice controllers
            'api' => array(
                'type' => 'RubedoAPI\\Router\\ApiRouter',
                'options' => array(
                    'defaults' =>
                        array(
                            '__NAMESPACE__' => 'RubedoAPI\\Frontoffice\\Controller',
                            'controller' => 'Api',
                            'action' => 'index',
                        ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'RubedoAPI\\Frontoffice\\Controller\\Api' => 'RubedoAPI\\Frontoffice\\Controller\\ApiController',
        ),
    ),
    'service_manager' => array(
        'invokables' => array(
            'RubedoAPI\\Collection\\UserTokens' => 'RubedoAPI\\Collection\\UserTokens',
            'RubedoAPI\\Services\\Security\\Authentication' => 'RubedoAPI\\Services\\Security\\Authentication',
            'RubedoAPI\\Services\\Security\\Token' => 'RubedoAPI\\Services\\Security\\Token',
            'RubedoAPI\\Services\\Router\\Url' => 'RubedoAPI\\Services\\Router\\Url',
            'RubedoAPI\\Services\\User\\CurrentUser' => 'RubedoAPI\\Services\\User\\CurrentUser',
            'RubedoAPI\\Services\\Internationalization\\Current' => 'RubedoAPI\\Services\\Internationalization\\Current',
            'RubedoAPI\\Collection\\ShoppingCart' => 'RubedoAPI\\Collection\\ShoppingCart',
        ),
        'aliases' => array(
            'API\\Collection\\UserTokens' => 'RubedoAPI\\Collection\\UserTokens',
            'API\\Services\\Auth' => 'RubedoAPI\\Services\\Security\\Authentication',
            'AuthenticationService' => 'RubedoAPI\\Services\\Security\\Authentication',
            'API\\Services\\Token' => 'RubedoAPI\\Services\\Security\\Token',
            'API\\Services\\Url' => 'RubedoAPI\\Services\\Router\\Url',
            'API\\Services\\CurrentUser' => 'RubedoAPI\\Services\\User\\CurrentUser',
            'API\\Services\\CurrentLocalization' => 'RubedoAPI\\Services\\Internationalization\\Current',
            'CurrentLocalization' => 'RubedoAPI\\Services\\Internationalization\\Current',
            'CurrentUser' => 'RubedoAPI\\Services\\User\\CurrentUser',
            'API\\Collection\\ShoppingCart' => 'RubedoAPI\\Collection\\ShoppingCart',
            'ShoppingCart' => 'RubedoAPI\\Collection\\ShoppingCart',
        ),
    ),
);
