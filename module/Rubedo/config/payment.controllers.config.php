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
 * Payment controllers list
 */
return array(
    'Rubedo\\Payment\\Controller\\Check' => 'Rubedo\\Payment\\Controller\\CheckController',
    'Rubedo\\Payment\\Controller\\CreditTransfer' => 'Rubedo\\Payment\\Controller\\CreditTransferController',
    'Rubedo\\Payment\\Controller\\Paypal' => 'Rubedo\\Payment\\Controller\\PaypalController'
);