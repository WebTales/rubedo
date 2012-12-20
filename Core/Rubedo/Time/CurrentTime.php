<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Time;
use Rubedo\Interfaces\Time\ICurrentTime;

/**
 * Current Time Service
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 * @todo define and implements real time service
 */
class CurrentTime implements ICurrentTime
{

    protected static $_simulatedTime = false;

    /**
     * Return the current time
     *
     * @return string current timestamp format datetime (number of second since
     *         unix dawn of time)
     */
    public function getCurrentTime ()
    {
        if (self::$_simulatedTime > 0) {
            return self::$_simulatedTime;
        }
        return time();
    }

    public function setSimulatedTime ($time)
    {
        self::$_simulatedTime = intval($time);
    }
}
