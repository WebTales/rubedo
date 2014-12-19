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
$paymentMeansPath = realpath(__DIR__ . "/paymentMeans/");
/**
 * List default Rubedo payment means
 */
return array(
    'check' => array(
        'name' => "Check",
        'controller' => 'Rubedo\\Payment\\Controller\\Check',
        'definitionFile' => $paymentMeansPath . '/check.json'
    ),
    'paypal' => array(
        'name' => "PayPal",
        'controller' => 'Rubedo\\Payment\\Controller\\Paypal',
        'definitionFile' => $paymentMeansPath . '/paypal.json'
    ),
    'creditTransfer' => array(
        'name' => "Credit Transfer",
        'controller' => 'Rubedo\\Payment\\Controller\\CreditTransfer',
        'definitionFile' => $paymentMeansPath . '/creditTransfer.json'
    )
);