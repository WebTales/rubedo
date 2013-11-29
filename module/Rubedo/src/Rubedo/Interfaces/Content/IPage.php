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
namespace Rubedo\Interfaces\Content;

/**
 * Page service
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
Interface IPage
{

    /**
     * append a css file to the file list
     *
     * @param string $cssFile
     *            URL of the CSS added
     */
    public function appendCss($cssFile);

    /**
     * clear the included css files list
     */
    public function clearCss();

    /**
     * Return the list of css files
     *
     * @return array list of URL
     */
    public function getCss();

    /**
     * append a js file to the file list
     *
     * @param string $jsFile
     *            URL of the js added
     */
    public function appendJs($jsFile);

    /**
     * clear the included js files list
     */
    public function clearJs();

    /**
     * Return the list of js files
     *
     * @return array list of URL
     */
    public function getJs();

    /**
     * set the page title
     *
     * @param string $pageTitle
     *            page title
     */
    public function setPageTitle($pageTitle);

    /**
     * get the page title
     *
     * @return string page title
     */
    public function getPageTitle();

    /**
     * get the Current site
     *
     * @return string current site id
     */
    public function getCurrentSite();

    /**
     * set the current site
     *
     * @param string $siteId
     *            site id
     */
    public function setCurrentSite($siteId);

    /**
     * Get the description
     *
     * @return mixed
     */
    public function getDescription();

    /**
     * Get keywords
     *
     * @return mixed
     */
    public function getKeywords();

    /**
     * Set the description
     *
     * @param $_description
     * @return mixed
     */
    public function setDescription($_description);

    /**
     * Set keywords
     *
     * @param $_keywords
     * @return mixed
     */
    public function setKeywords($_keywords);


    /**
     * Get the current page
     *
     * @return mixed
     */
    public static function getCurrentPage();

    /**
     * Set the current page
     *
     * @param $_currentPage
     * @return mixed
     */
    public static function setCurrentPage($_currentPage);

    /**
     * Get the author
     *
     * @return mixed
     */
    public static function getAuthor();

    /**
     * Set the author
     *
     * @param $_author
     * @return mixed
     */
    public static function setAuthor($_author);
}
