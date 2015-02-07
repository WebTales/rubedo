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
namespace Rubedo\Payment;

use Rubedo\Services\Manager;

/**
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
abstract class AbstractPayment
{

    /**
     * name of the the payment means
     *
     * @var string
     */
    protected $paymentMeans;

    /**
     * native config for this payment means
     *
     * @var array
     */
    protected $nativePMConfig;



    public function __construct()
    {
        if (empty($this->paymentMeans)) {
            throw new \Rubedo\Exceptions\Server("Payment means name is not set");
        }
        $pmConfig = Manager::getService("PaymentConfigs")->getConfigForPM($this->paymentMeans);
        if (!$pmConfig['success']) {
            throw new \Rubedo\Exceptions\Server("Unable to retrieve payment means config config");
        }
        if (!$pmConfig['data']['active']) {
            throw new \Rubedo\Exceptions\Server("Payment means is not activated");
        }
        $this->nativePMConfig = $pmConfig['data']['nativePMConfig'];
    }

}
