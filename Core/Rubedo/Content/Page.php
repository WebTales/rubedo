<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Content;

Use Rubedo\Services\Manager;
use Rubedo\Interfaces\Content\IPage;
/**
 * Page service
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Page implements  IPage
{
    /**
     * CSS to be included in the page
     *
     * @var array
     */
    protected static $_css = array();

    /**
     * JS to be included in the page
     *
     * @var array
     */
    protected static $_js = array();

    /**
     * Page title
     *
     * @var string
     */
    protected static $_title = '';

    protected static $_description ='';
    
    protected static $_keywords = array();
    
    protected static $_currentPage = null;
    
    /**
     * Current Site
     *
     * @var string
     */
    protected static $_currentSite = '';

    /**
     * append a css file to the file list
     * @param string $cssFile URL of the CSS added
     */
    public function appendCss($cssFile) {
        if (!in_array($cssFile, self::$_css)) {
            self::$_css[] = $cssFile;
        }
    }

    /**
     * clear the included css files list
     */
    public function clearCss() {
        self::$_css = array();
    }

    /**
     * Return the list of css files
     * @return array list of URL
     */
    public function getCss() {
        return self::$_css;
    }

    /**
     * append a js file to the file list
     * @param string $jsFile URL of the js added
     */
    public function appendJs($jsFile) {
        if (!in_array($jsFile, self::$_js)) {
            self::$_js[] = $jsFile;
        }
    }

    /**
     * clear the included js files list
     */
    public function clearJs() {
        self::$_js = array();
    }

    /**
     * Return the list of js files
     * @return array list of URL
     */
    public function getJs() {
        return self::$_js;
    }

    /**
     * set the page title
     *
     * @param string $pageTitle page title
     */
    public function setPageTitle($pageTitle) {
        self::$_title = $pageTitle;
    }

    /**
     * get the page title
     *
     * @return string page title
     */
    public function getPageTitle() {
        return self::$_title;
    }

    /**
     * get the current site
     *
     * @return string current site
     */
    public function getCurrentSite() {
        return self::$_currentSite;
    }

    /**
     * set the current site
     *
     * @param string $siteId current site
     */
    public function setCurrentSite($siteId) {
        self::$_currentSite = $siteId;
    }
    
	/**
     * @return the $_description
     */
    public function getDescription ()
    {
        return Page::$_description;
    }

	/**
     * @return the $_keywords
     */
    public function getKeywords ()
    {
        return Page::$_keywords;
    }

	/**
     * @param string $_description
     */
    public function setDescription ($_description)
    {
        Page::$_description = $_description;
    }

	/**
     * @param multitype: $_keywords
     */
    public function setKeywords ($_keywords)
    {
        Page::$_keywords = $_keywords;
    }
    
	/**
     * @return the $_currentPage
     */
    public static function getCurrentPage ()
    {
        return Page::$_currentPage;
    }

	/**
     * @param field_type $_currentPage
     */
    public static function setCurrentPage ($_currentPage)
    {
        Page::$_currentPage = $_currentPage;
    }


    
    
    

}
