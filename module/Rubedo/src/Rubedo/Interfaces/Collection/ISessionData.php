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
 * Interface of service handling SessionData
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
interface ISessionData extends IAbstractCollection
{

    /**
     * Convert session data string into associative array
     *
     * @param   string  $session_data Session data string
     * @return  array
     */
    public function decode($session_data);

    /**
     * Return the session name
     *
     * @return string
     */
    public function getSessionName();

    /**
     * Set session name
     *
     * @param   string  $sessionName
     */
    public static function setSessionName($sessionName);

}
