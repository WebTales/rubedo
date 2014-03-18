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
$paymentMeansPath = realpath(__DIR__ . "/paymentMeans/");
/**
 * List default Rubedo payment means
 */
return array(
    'check' => array(
        'name'=>"Check",
        'definitionFile' => $paymentMeansPath . '/check.json'
    ),
    'paypal' => array(
        'name'=>"PayPal",
        'definitionFile' => $paymentMeansPath . '/paypal.json'
    ),
    'creditTransfer' => array(
        'name'=>"Credit Transfer",
        'definitionFile' => $paymentMeansPath . '/creditTransfer.json'
    )
);