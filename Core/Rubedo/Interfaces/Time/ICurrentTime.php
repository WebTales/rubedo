<?php

/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
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
    public function getCurrentTime();
	
}
