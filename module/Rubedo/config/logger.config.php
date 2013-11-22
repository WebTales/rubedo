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
use Monolog\Logger;

/**
 * Configure all loggers for Rubedo
 */
return array(
    'handlers' => array(
        'ChromePHPHandler' => array(
            'class' => 'Monolog\\Handler\\ChromePHPHandler'
        ),
        'FirePHPHandler' => array(
            'class' => 'Monolog\\Handler\\FirePHPHandler'
        ),
        'MongoDBHandler' => array(
            'class' => 'Monolog\\Handler\\MongoDBHandler',
            'collection' => 'Logs',
            'database' => 'inherit'
        ),
        'StreamHandler' => array(
            'class' => 'Monolog\\Handler\\StreamHandler',
            'dirPath' => APPLICATION_PATH . '/log'
        )
    ),
    'enableHandler' => array(
        'ChromePHPHandler' => 0,
        'FirePHPHandler' => 0,
        'MongoDBHandler' => 0,
        'StreamHandler' => 0
    ),
    'errorLevel' => Logger::ERROR,
    'applicationLevel' => Logger::INFO
);