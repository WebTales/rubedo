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
namespace Rubedo\Internationalization;

Use Rubedo\Services\Manager, Rubedo\Interfaces\Internationalization\ITranslate;

/**
 * Implement translation for label in Rubedo
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Translate implements ITranslate
{

    /**
     * Default language to be used if none where choosen or user defined
     *
     * @var string
     */
    protected static $defaultLanguage = 'en';

    protected static $localizationJsonArray = array();

    protected static $translationArray = array();

    /**
     *
     * @return the $localizationJsonArray
     */
    public static function getLocalizationJsonArray ()
    {
        return Translate::$localizationJsonArray;
    }

    /**
     *
     * @param multitype: $localizationJsonArray            
     */
    public static function setLocalizationJsonArray (array $localizationJsonArray)
    {
        Translate::$localizationJsonArray = $localizationJsonArray;
    }

    /**
     *
     * @return the $defaultLanguage
     */
    public static function getDefaultLanguage ()
    {
        return Translate::$defaultLanguage;
    }

    /**
     *
     * @param string $defaultLanguage            
     */
    public static function setDefaultLanguage ($defaultLanguage)
    {
        Translate::$defaultLanguage = $defaultLanguage;
    }

    /**
     * translate a label given by its code and its default value
     * @param string $code
     * @param string $defaultLabel
     * @return string
     */
    public function translate ($code, $defaultLabel = "")
    {
        $language = Manager::getService('CurrentUser')->getLanguage();
        if ($language === null) {
            $language = self::$defaultLanguage;
        }
        
        $translatedValue = $this->getTranslation($code, $language);
        
        if ($translatedValue == null) {
            $translatedValue = $defaultLabel;
        }
        
        return $translatedValue;
    }

    public function getTranslation ($code, $language)
    {
        $this->loadLanguage($language);
        if (isset(self::$translationArray[$language][$code])) {
            return self::$translationArray[$language][$code];
        } else {
            return false;
        }
    }

    protected function loadLanguage ($language)
    {
        if (isset(self::$translationArray[$language])) {
            return true;
        }
        self::$translationArray[$language] = array();
        
        foreach (self::$localizationJsonArray as $jsonFilePath) {
            $realLanguagePath = APPLICATION_PATH . '/../' . str_replace('languagekey', $language, $jsonFilePath);
            if (is_file($realLanguagePath)) {
                $tempJson = file_get_contents($realLanguagePath);
                $tempArray = \Zend_Json::decode($tempJson);
                self::$translationArray[$language] = array_merge(self::$translationArray[$language], $tempArray);
            } else {
                $defaultLanguagePath = APPLICATION_PATH . '/../' . str_replace('languagekey', self::$defaultLanguage, $jsonFilePath);
                if (is_file($defaultLanguagePath)) {
                    $tempJson = file_get_contents($defaultLanguagePath);
                    $tempArray = \Zend_Json::decode($tempJson);
                    self::$translationArray[$language] = array_merge(self::$translationArray[$language], $tempArray);
                }
            }
        }
    }
}