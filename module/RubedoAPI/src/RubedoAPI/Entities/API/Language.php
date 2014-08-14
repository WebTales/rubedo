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
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace RubedoAPI\Entities\API;


use RubedoAPI\Exceptions\APIEntityException;

/**
 * Class Language
 * @package RubedoAPI\Entities\API
 */
class Language
{
    /**
     * The locale
     *
     * @var string
     */
    protected $locale;

    /**
     * Locale to fallback if locale is missing
     *
     * @var string
     */
    protected $fallback;

    /**
     * Construct and check the language param, must be "en" or "en|en" where the second code is the fallback
     *
     * @param $languageString
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    function __construct($languageString)
    {
        $lengthString = strlen($languageString);
        if (2 == $lengthString) {
            $this->locale = $languageString;
            return;
        }
        if (5 == $lengthString) {
            $this->locale = substr($languageString, 0, 2);
            $this->fallback = substr($languageString, -2, 2);
            return;
        }
        throw new APIEntityException('Language value is not compliant', 400);
    }

    /**
     * Return fallback
     *
     * @return string|null
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * Return locale
     *
     * @return string|null
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Return true if fallback is defined
     *
     * @return bool
     */
    public function hasFallback()
    {
        return isset($this->fallback);
    }
} 