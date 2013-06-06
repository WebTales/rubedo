<?php

/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
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
namespace Rubedo\Interfaces\Time;

/**
 * Current Time Service
 *
 * Get current time.
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface ICurrentTime
{

    /**
     * Return the current time
     *
     * @return string current timestamp format datetime (number of second since unix dawn of time)
     */
    public function getCurrentTime ();

    public function setSimulatedTime ($time);
}
