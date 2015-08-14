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
 * Interface of service handling ContentViewLog
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
interface IContentViewLog extends IAbstractCollection
{

    /**
     * Log actions on contents
     *
     * @param string    $contentId     Mongo ID of the concerned content
     * @param string    $locale        Used language
     * @param int       $fingerprint   Unique user ID
     * @param int       $timestamp     Log date
     */
    public function log($contentId, $locale, $fingerprint, $timestamp);

}
