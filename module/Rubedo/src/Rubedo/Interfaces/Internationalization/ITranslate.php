<?php

/**
 * Rubedo -- ECM solution Copyright (c) 2013, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Interfaces\Internationalization;

/**
 * Implement translation for label in Rubedo
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface ITranslate
{

    /**
     * Get the localization array
     *
     * @return array $localizationJsonArray
     */
    public static function getLocalizationJsonArray();

    /**
     * Set the localization array
     *
     * @param array $localizationJsonArray
     */
    public static function setLocalizationJsonArray(array $localizationJsonArray);

    /**
     * Get the default language
     *
     * @return string $defaultLanguage
     */
    public static function getDefaultLanguage();

    /**
     * Set the default language
     *
     * @param string $defaultLanguage
     */
    public static function setDefaultLanguage($defaultLanguage);

    /**
     * translate a label given by its code and its default value
     *
     * @param string $code
     * @param string $defaultLabel
     * @return string
     */
    public function translate($code, $defaultLabel = "");
}