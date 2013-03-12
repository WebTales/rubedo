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
use Rubedo\Services\Manager, Rubedo\Interfaces\Time\IDate, DateTime, DateInterval, IntlDateFormatter;

/**
 * Current Time Service
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 * @todo define and implements real time service
 */
class Date implements IDate
{

    protected static $_startOnSunday = false;

    protected static $_lang = null;

    /**
     * (non-PHPdoc)
     * 
     * @see \Rubedo\Interfaces\Time\IDate::convertToTimeStamp()
     */
    public function convertToTimeStamp ($dateString)
    {
        $date = new DateTime($dateString);
        return $date->getTimestamp();
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \Rubedo\Interfaces\Time\IDate::getMonthArray()
     */
    public function getMonthArray ($timestamp = null)
    {
        if (! $timestamp) {
            $timestamp = Manager::getService('CurrentTime')->getCurrentTime();
        }
        $dayOfWeekFormat = self::$_startOnSunday ? 'w' : 'N';
        
        // init the year and month info
        $year = date('Y', $timestamp);
        $month = date('m', $timestamp);
        
        // define the first day to display based on the first day of the month
        // and it position in the week
        $firstDayOfMonthTimeStamp = mktime(0, 0, 0, $month, 1, $year);
        $firstDayOfMonthInWeek = date($dayOfWeekFormat, 
                $firstDayOfMonthTimeStamp);
        $firstDay = new DateTime();
        $firstDay->setTimestamp($firstDayOfMonthTimeStamp);
        if (self::$_startOnSunday) {
            $offset = $firstDayOfMonthInWeek;
        } else {
            $offset = $firstDayOfMonthInWeek - 1;
        }
        $firstDay->sub(new DateInterval('P' . $offset . 'D'));
        $dayIterator = clone ($firstDay);
        
        // define the last day to display based on the last day of the month and
        // it position in the week
        $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
        $lastDayOfMonthTimeStamp = mktime(0, 0, 0, $month, $days_in_month, 
                $year);
        $lastDayOfMonthInWeek = date($dayOfWeekFormat, $lastDayOfMonthTimeStamp);
        $lastDay = new DateTime();
        $lastDay->setTimestamp($lastDayOfMonthTimeStamp);
        if (self::$_startOnSunday) {
            $offset = 6 - $lastDayOfMonthInWeek;
        } else {
            $offset = 7 - $lastDayOfMonthInWeek;
        }
        $lastDay->add(new DateInterval('P' . $offset . 'D'));
        
        $finalTimestamp = $lastDay->getTimestamp();
        
        $max = 0;
        $aDay = new DateInterval('P1D');
        
        $returnArray = array();
        $currentWeek = 1;
        $previous = 0;
        // iterate day by day up to the last day of the month
        while ((($iterateTimestamp = $dayIterator->getTimestamp()) <=
                 $finalTimestamp) && ($max < 45)) {
                    $max ++;
            $number = date('d', $iterateTimestamp);
            $dayOfWeek = date($dayOfWeekFormat, $iterateTimestamp);
            $inMonth = (date('m', $iterateTimestamp) == $month);
            if ($previous > intval($dayOfWeek)) {
                $currentWeek ++;
            }
            $returnArray[$currentWeek][$dayOfWeek] = array(
                    'value' => $number,
                    'inMonth' => $inMonth
            );
            $previous = $dayOfWeek;
            $dayIterator->add($aDay);
        }
        return $returnArray;
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \Rubedo\Interfaces\Time\IDate::getShortDayList()
     */
    public function getShortDayList ()
    {
        $daysOfWeek = array(
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday'
        );
        if (self::$_startOnSunday) {
            $daysOfWeek[0] = 'Sunday';
        } else {
            $daysOfWeek[7] = 'Sunday';
        }
        foreach ($daysOfWeek as $key => $day) {
            $dayDateTime = new DateTime("last $day");
            $nameArray[$key] = $this->getLocalised('EE', 
                    $dayDateTime->getTimestamp());
        }
        ksort($nameArray);
        return $nameArray;
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \Rubedo\Interfaces\Time\IDate::getLocalised()
     */
    public function getLocalised ($format = null, $timestamp = null)
    {
        if (! $timestamp) {
            $timestamp = Manager::getService('CurrentTime')->getCurrentTime();
        }
        
        $formatter = new IntlDateFormatter($this->_getLang(), 
                IntlDateFormatter::FULL, IntlDateFormatter::NONE);
        if ($format) {
            $formatter->setPattern($format);
        }
        $date = new \DateTime();
        $date->setTimestamp($timestamp);
        return $formatter->format($date);
    }

    /**
     * return the current language
     *
     * @return string
     */
    protected function _getLang ()
    {
        if (! isset(self::$_lang)) {
            self::$_lang = Manager::getService('Session')->get('lang', 'en');
        }
        return self::$_lang;
    }
}