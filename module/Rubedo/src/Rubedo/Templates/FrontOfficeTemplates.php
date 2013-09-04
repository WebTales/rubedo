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
namespace Rubedo\Templates;

use Rubedo\Interfaces\Templates\IFrontOfficeTemplates;
use Rubedo\Services\Manager;
use Zend\Json\Json;

/**
 * Front Office URL service
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class FrontOfficeTemplates implements IFrontOfficeTemplates
{

    protected static $config;

    /**
     * twig environnelent object
     *
     * @var \Twig_Environment
     */
    protected $_twig;

    /**
     * Twig options array
     *
     * @var array
     */
    protected $options = array();

    /**
     * Directory containing twig templates
     *
     * @var string
     */
    protected static $templateDir = null;

    /**
     * Current theme name
     *
     * @var string
     */
    protected static $_currentTheme = null;

    /**
     * Custom theme id
     *
     * @var string
     */
    protected static $_customThemeId = null;

    /**
     * had main theme been set ?
     *
     * @var boolean
     */
    protected static $_themeHasBeenSet = false;

    /**
     *
     * @return the $config
     */
    public function getConfig()
    {
        return FrontOfficeTemplates::$config;
    }

    /**
     *
     * @param field_type $config            
     */
    public static function setConfig($config)
    {
        FrontOfficeTemplates::$config = $config;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_init();
    }

    /**
     * initialise Twig Context
     *
     * @param string $lang
     *            current language
     */
    protected function _init()
    {
        $config = $this->getConfig();
        
        $this->options = array(
            'templateDir' => $config['templateDir'],
            'cache' => $config['cache'],
            'debug' => $config['debug'],
            'auto_reload' => $config['auto_reload']
        );
                
        $loader = new \Twig_Loader_Filesystem($this->options['templateDir'] . '/' . $this->getCurrentTheme());
        $loader->addPath($this->options['templateDir'] . '/root', 'Root');
        $loader->addPath($this->options['templateDir'] . '/root');
        $this->_twig = new \Twig_Environment($loader, $this->options);
        
        foreach ($config['workspaces'] as $name => $path) {
            $loader->prependPath($path, $name);
        }
        
        $this->_twig->addExtension(new \Twig_Extension_Debug());
        
        $this->_twig->addExtension(new BackOfficeTranslate());
        $this->_twig->addExtension(new FrontOfficeTranslate());
                
        $this->_twig->addExtension(new \Twig_Extensions_Extension_Intl());
        
        $this->_twig->addFilter('cleanHtml', new \Twig_Filter_Function('\\Rubedo\\Templates\\FrontOfficeTemplates::cleanHtml', array(
            'is_safe' => array(
                'html'
            )
        )));
        
        $this->_twig->addFunction('url', new \Twig_Function_Function('\\Rubedo\\Templates\\FrontOfficeTemplates::url'));
        $this->_twig->addFunction('displayUrl', new \Twig_Function_Function('\\Rubedo\\Templates\\FrontOfficeTemplates::displayUrl'));
        $this->_twig->addFunction('getPageTitle', new \Twig_Function_Function('\\Rubedo\\Templates\\FrontOfficeTemplates::getPageTitle'));
        $this->_twig->addFunction('getLinkedContents', new \Twig_Function_Function('\\Rubedo\\Templates\\FrontOfficeTemplates::getLinkedContents'));
        $this->_twig->addFunction('getTaxonomyTerm', new \Twig_Function_Function('\\Rubedo\\Templates\\FrontOfficeTemplates::getTaxonomyTerm'));
        $this->_twig->addFunction('getDam', new \Twig_Function_Function('\\Rubedo\\Templates\\FrontOfficeTemplates::getDam'));
        $this->_twig->addFunction('getContent', new \Twig_Function_Function('\\Rubedo\\Templates\\FrontOfficeTemplates::getContent'));
        $this->_twig->addFunction('isInRootline', new \Twig_Function_Function('\\Rubedo\\Templates\\FrontOfficeTemplates::isInRootline'));
        $this->_twig->addFunction('getMediaType', new \Twig_Function_Function('\\Rubedo\\Templates\\FrontOfficeTemplates::getMediaType'));
        $this->_twig->addFilter(new \Twig_SimpleFilter('ucfirst', '\\Rubedo\\Templates\\FrontOfficeTemplates::mbucfirst'));
    }

    /**
     * render a twig template given an array of data
     *
     * @param string $template
     *            template name
     * @param array $vars
     *            array of data to be rendered
     * @return string HTML produced by twig
     */
    public function render($template, array $vars)
    {
        $templateObj = $this->_twig->loadTemplate($template);
        return $templateObj->render($vars);
    }

    /**
     * return the template directory
     *
     * @return string
     */
    public function getTemplateDir()
    {
        if (! isset(self::$templateDir)) {
            $config = $this->getConfig();
            $this->options = array(
                'templateDir' => $config['templateDir'],
                'cache' => $config['cache'],
                'debug' => $config['debug'],
                'auto_reload' => $config['auto_reload']
            );
            
            self::$templateDir = $this->options['templateDir'];
        }
        return self::$templateDir;
    }

    /**
     * Return the actual path of a twig subpart in the current theme
     *
     * Check if it exist in current theme, return default path if not
     *
     * @return string
     */
    public function getFileThemePath($path)
    {
        // no longer use this function for twig : use advanced twig_loader config
        if (pathinfo($path, PATHINFO_EXTENSION) == 'twig') {
            return $path;
        }
        
        if (is_file($this->getTemplateDir() . '/' . $this->getCurrentTheme() . '/' . $path)) {
            return '' . $this->getCurrentTheme() . '/' . $path;
        } else {
            return 'root/' . $path;
        }
    }

    public function templateFileExists($path)
    {
        return is_file($this->getTemplateDir() . '/' . $path);
    }

    /**
     * Get the current theme name
     *
     * @return string
     */
    public function getCurrentTheme()
    {
        if (! isset(self::$_currentTheme)) {
            self::$_currentTheme = 'default';
        }
        return self::$_currentTheme;
    }

    /**
     * Get the custom theme id
     *
     * @return string
     */
    public function getCustomThemeId()
    {
        return self::$_customThemeId;
    }

    /**
     * Set the current theme name
     *
     * @param string $theme            
     */
    public function setCurrentTheme($theme)
    {
        // check if it is a custom theme
        if (preg_match('/[\dabcdef]{24}/', $theme) == 1) {
            self::$_currentTheme = "customtheme";
            self::$_customThemeId = $theme;
            self::$_themeHasBeenSet = true;
        } else {
            self::$_currentTheme = $theme;
            self::$_themeHasBeenSet = true;
        }
    }

    public function themeHadBeenSet()
    {
        return self::$_themeHasBeenSet;
    }

    /**
     * Call the Html Cleaner Service
     */
    public static function cleanHtml($html)
    {
        return Manager::getService('HtmlCleaner')->clean($html);
    }

    public static function url(array $urlOptions = array(), $reset = false, $encode = true, $route = null)
    {
        return Manager::getService('Url')->url($urlOptions, $route, $reset, $encode);
    }

    public static function displayUrl($contentId, $type = "default", $siteId = null, $defaultUrl = null)
    {
        return Manager::getService('Url')->displayUrl($contentId, $type, $siteId, $defaultUrl);
    }

    public static function getPageTitle($contentId)
    {
        $page = Manager::getService('Pages')->findByID($contentId);
        if ($page) {
            return $page['title'];
        } else {
            return null;
        }
    }

    public static function getLinkedContents($contentId, $typeId, $fieldName, $sort = null)
    {
        return Manager::getService('Contents')->getReflexiveLinkedContents($contentId, $typeId, $fieldName, $sort);
    }

    public static function getTaxonomyTerm($id)
    {
        return Manager::getService('TaxonomyTerms')->findById($id);
    }

    public function getAvailableThemes()
    {
        $templateDirIterator = new \DirectoryIterator($this->getTemplateDir());
        if (! isset($templateDirIterator)) {
            throw new \Rubedo\Exceptions\Server('Can not instanciate iterator for template dir', "Exception67");
        }
        
        $themeInfosArray = array();
        
        // get real file themes
        foreach ($templateDirIterator as $directory) {
            if ($directory->isDot() || ! $directory->isDir()) {
                continue;
            }
            // ignore generic custom theme folder
            if ($directory->getFilename() == "customtheme") {
                continue;
            }
            $jsonFilePath = $directory->getPathname() . '/theme.json';
            if (is_file($jsonFilePath)) {
                $themeJson = file_get_contents($jsonFilePath);
                $themeInfos = Json::decode($themeJson, Json::TYPE_ARRAY);
                $themeInfosArray[] = $themeInfos;
            }
        }
        // get database custom themes
        $customThemesArray = Manager::getService('CustomThemes')->getList();
        $customThemesArray = $customThemesArray['data'];
        foreach ($customThemesArray as &$value) {
            $value['text'] = $value['id'];
            $value['label'] = $value['name'];
            $themeInfosArray[] = $value;
        }
        
        $response = array();
        $response['total'] = count($themeInfosArray);
        $response['data'] = $themeInfosArray;
        $response['success'] = TRUE;
        $response['message'] = 'OK';
        
        return $response;
    }

    public function getThemeInfos($name)
    {
        $jsonFilePath = $this->getTemplateDir() . '/' . $name . '/theme.json';
        if (is_file($jsonFilePath)) {
            $themeJson = file_get_contents($jsonFilePath);
            $themeInfos = Json::decode($themeJson, Json::TYPE_ARRAY);
            return $themeInfos;
        } else {
            return null;
        }
    }

    public function getCurrentThemeInfos()
    {
        return $this->getThemeInfos($this->getCurrentTheme());
    }

    /**
     * Find a dam by its id
     *
     * @param string $damtId
     *            Contain the id of the requested dam
     */
    public static function getDam($damId)
    {
        $damService = Manager::getService("Dam");
        
        return $damService->findById($damId);
    }

    /**
     * Find a content by its id
     *
     * @param string $contentId
     *            Contain the id of the requested content
     */
    public static function getContent($contentId)
    {
        $contentService = Manager::getService("Contents");
        
        $return = $contentService->findById($contentId, true, false);
        return $return;
    }

    /**
     * Return true if the given page is in the current rootline
     *
     * @param string $pageId
     *            id of the page
     * @return boolean
     */
    public static function isInRootline($pageId)
    {
        return Manager::getService("Pages")->isInRootline($pageId);
    }

    /**
     * Get the media type
     */
    public static function getMediaType($mediaId)
    {
        return Manager::getService("Dam")->getMediaType($mediaId);
    }

    public static function mbucfirst($string)
    {
        $e = 'utf-8';
        if (function_exists('mb_strtoupper') && function_exists('mb_substr') && ! empty($string)) {
            $string = mb_strtolower($string, $e);
            $upper = mb_strtoupper($string, $e);
            preg_match('#(.)#us', $upper, $matches);
            $string = $matches[1] . mb_substr($string, 1, mb_strlen($string, $e), $e);
        } else {
            $string = ucfirst($string);
        }
        return $string;
    }
}
