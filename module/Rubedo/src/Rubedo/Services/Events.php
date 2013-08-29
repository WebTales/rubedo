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
namespace Rubedo\Services;

/**
 * Events manager for Rubedo
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Events
{

    /**
     * Store the application Event Manager
     *
     * @var \Zend\EventManager\EventManagerInterface
     */
    protected static $eventManager;

    /**
     * Return the event manager
     *
     * @return \Zend\EventManager\EventManagerInterface
     */
    public static function getEventManager ()
    {
        return static::$eventManager;
    }

    /**
     * Set the event manager
     *
     * @param \Zend\EventManager\EventManagerInterface $eventManager            
     */
    public static function setEventManager (\Zend\EventManager\EventManagerInterface $eventManager)
    {
        static::$eventManager = $eventManager;
    }
}
