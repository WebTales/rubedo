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

use Rubedo\Services\Manager;
use Rubedo\Interfaces\Internationalization\ITranslate;
use Zend\Json\Json;
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

    protected static $localizationJsonArray;

    protected static $translationArray = array();

    /**
     *
     * @return the $localizationJsonArray
     */
    public static function getLocalizationJsonArray ()
    {
        if(!isset(self::$localizationJsonArray)){
            self::lazyLoadConfig();
        }
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
        if(!isset(self::$localizationJsonArray)){
            self::lazyLoadConfig();
        }
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

    public function __construct(){
        if(!isset(self::$localizationJsonArray)){
            self::lazyLoadConfig();
        }
    }
    
    /**
     * translate a label given by its code and its default value
     * 
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
            $translatedValue = $this->getTranslation($code, "en");
        }
        
        if ($translatedValue == null) {
            $translatedValue = $defaultLabel;
        }
        
        return $translatedValue;
    }
    
    /**
     * translate a label given by its code and its default value
     *
     * @param string $code
     * @param string $defaultLabel
     * @return string
     */
    public function translateInWorkingLanguage ($code, $defaultLabel = "")
    {
        $language = \Rubedo\Collection\AbstractLocalizableCollection::getWorkingLocale();
        if ($language === null) {
            $language = self::$defaultLanguage;
        }
    
        $translatedValue = $this->getTranslation($code, $language);
        if ($translatedValue == null) {
            $translatedValue = $this->getTranslation($code, "en");
        }
    
        if ($translatedValue == null) {
            $translatedValue = $defaultLabel;
        }
    
        return $translatedValue;
    }

    public function getTranslation($code, $language, $fallBack = null, $placeholders = array())
    {
        if(isset($language)){
            $this->loadLanguage($language);
        }
        if(isset($fallBack)){
            $this->loadLanguage($fallBack);
        }
        
        $this->loadLanguage('en');

        $translated = false;
        if (isset($language) && isset(self::$translationArray[$language][$code])) {
            $translated = self::$translationArray[$language][$code];
        } elseif (isset($fallBack) && isset(self::$translationArray[$fallBack][$code])) {
            $translated = self::$translationArray[$fallBack][$code];
        } elseif (isset(self::$translationArray['en'][$code])) {
            $translated = self::$translationArray['en'][$code];
        }

        if ($translated != false) {
            $translated = $this->replacePlaceHolders($translated, $placeholders);
        }
        return $translated;
    }

    /**
     * Replace placeholders by real values
     *
     * @param string $stringToReplace String where replace placeholders
     * @param array $placeholders Keys to replace by values
     * @return string
     */
    protected function replacePlaceHolders($stringToReplace, $placeholders = array())
    {
        if (!empty($placeholders)) {
            return str_replace(
                array_keys($placeholders),
                array_values($placeholders),
                $stringToReplace
            );
        }
        return $stringToReplace;
    }
    protected function loadLanguage ($language)
    {
        if (isset(self::$translationArray[$language])) {
            return true;
        }
        self::$translationArray[$language] = array();
        
        foreach (self::$localizationJsonArray as $jsonFilePath) {
            $realLanguagePath = APPLICATION_PATH . '/' . str_replace('languagekey', $language, $jsonFilePath);
            if (is_file($realLanguagePath)) {
                $tempJson = file_get_contents($realLanguagePath);
                $tempArray = Json::decode($tempJson,Json::TYPE_ARRAY);
                self::$translationArray[$language] = array_merge(self::$translationArray[$language], $tempArray);
            } 
        }
    }
    
    /**
     * Read configuration from global application config and load it for the current class
     */
    public static function lazyLoadConfig(){
        $config = Manager::getService('config');
        $options = $config['localisationfiles'];
        if (isset($options)) {
            self::setLocalizationJsonArray($options);
        }
    }
}