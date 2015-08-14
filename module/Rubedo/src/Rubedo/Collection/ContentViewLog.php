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
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IContentViewLog;

/**
 * Service to handle content view logging
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class ContentViewLog extends AbstractCollection implements IContentViewLog
{

    public function __construct()
    {
        $this->_collectionName = 'ContentViewLog';
        parent::__construct();
    }

    public function init()
    {

    }

    /**
     * @see \Rubedo\Interfaces\Collection\IContentViewLog::log
     */
    public function log($contentId, $locale, $fingerprint, $timestamp)
    {
        $this->_dataService->directCreate(array(
            "contentId" => $contentId,
            "viewedLocale" => $locale,
            "userFingerprint" => $fingerprint,
            "timestamp" => $timestamp
        ));
    }
}
