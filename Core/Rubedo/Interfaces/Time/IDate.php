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
     * Convert a date string to a timestamp
     *
     * @see http://www.php.net/manual/datetime.formats.php
     * @param
     *            $dateString
     * @return string timestamp format datetime (number of second since unix
     *         dawn of time)
     */
    public function convertToTimeStamp ($dateString);

    /**
     * Return an array of a month (current or including given timestamp)
     *
     * This array contains the complete weeks containing at least on day of the
     * month
     * each week is an array containing the ordinal of the seven days.
     *
     * i.e. mars 2013
     *
     * 25	26	27	28	01	02	03
     * 04	05	06	07	08	09	10
     * 11	12	13	14	15	16	17
     * 18	19	20	21	22	23	24
     * 25	26	27	28	29	30	31
     *
     *
     * @param string $timestamp            
     * @return array
     */
    public function getMonthArray ($timestamp = null);

    /**
     * Return the localized list of the days of the week.
     * (short name)
     *
     * @return array
     */
    public function getShortDayList ();

    /**
     * get a localized date with format and timestamp arguments
     *
     * @param string $format            
     * @param string $timestamp            
     */
    public function getLocalised ($format = null, $timestamp = null);
}
