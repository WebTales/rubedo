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
namespace Rubedo\Content;

/**
 * Page service
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Context
{

    /**
     * Set to true if a request expects JSON without isXmlHttpRequest
     * 
     * @var bool
     */
    protected static $expectJson = false;

    /**
     * Do we preview draft contents
     * 
     * @var bool
     */
    protected static $isDraft = false;

    /**
     *
     * @return the $expectJson
     */
    public static function getExpectJson()
    {
        return Context::$expectJson;
    }

    /**
     *
     * @param boolean $expectJson            
     */
    public static function setExpectJson($expectJson = true)
    {
        Context::$expectJson = $expectJson;
    }

    /**
     * Return true if it's a draft
     *
     * @return boolean
     */
    public static function isDraft()
    {
        return filter_var(Context::$isDraft, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Return true if it's not a draft
     *
     * @return boolean
     */
    public static function isLive()
    {
        return !static::isDraft();
    }

    /**
     *
     * @param boolean $isDraft            
     */
    public static function setIsDraft($isDraft = true)
    {
        Context::$isDraft = $isDraft;
    }
}
