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

use Rubedo\Interfaces\Time\IDate;

/**
 * Current Time Service
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 * @todo define and implements real time service
 */
class Date implements IDate {
	
	/**
	 * Convert to timestamp
	 * 
	 * @return string timestamp format datetime (number of second since unix dawn of time)
	 */
    public function convertToTimeStamp($dateString,$format=null)
    {
	$date= new \DateTime($dateString);
	//$date->format("U");
	return $date->getTimestamp();
    }


}