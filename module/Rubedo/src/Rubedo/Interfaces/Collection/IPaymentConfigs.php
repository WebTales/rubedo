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
namespace Rubedo\Interfaces\Collection;

/**
 * Interface of service handling PaymentConfigs
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
interface IPaymentConfigs extends IAbstractCollection
{

    /**
     * Gets the config for a specific payment means, makes sure payment means is installed, autocreates an inactive one if non existent
     *
     * @param   string  $pmName     Name of the payment mean (check, paypal, credit card)
     * @return  array   Configuration of this payment mean
     */
    public function getConfigForPM($pmName);

    /**
     * Return currently activated payment means
     *
     * @return array    Activated payment means configuration
     */
    public function getActivePMConfigs();

}
