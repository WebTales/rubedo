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
class CurrentTime implements ICurrentTime {

	/**
	 * Return the current time
	 * 
	 * @return string current timestamp format datetime (number of second since unix dawn of time)
	 */
	public function getCurrentTime() {
		return time();
	}
	
}
