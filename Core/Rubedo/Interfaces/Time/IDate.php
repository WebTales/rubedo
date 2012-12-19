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
 * Date Service
 *
 * 
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IDate
{
	/**
	 * Convert to timestamp
	 * 
	 * @return string timestamp format datetime (number of second since unix dawn of time)
	 */
    public function convertToTimeStamp($dateString,$format=null);
	
}
