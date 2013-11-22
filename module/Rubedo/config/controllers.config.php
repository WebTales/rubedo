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

/**
 * Combine all controllers lists
 */
$backControllers = include 'backoffice.controllers.config.php';
$frontControllers = include 'frontoffice.controllers.config.php';
$installControllers = include 'install.controllers.config.php';
$blocksControllers = include 'blocks.controllers.config.php';
return array_merge($backControllers, $frontControllers, $installControllers, $blocksControllers);