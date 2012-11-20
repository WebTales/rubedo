<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
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
     * Return page infos based on its ID
     *
     * @param string|int $pageId requested URL
     * @return array
     */
    public function getPageInfo($pageId);

    /**
     * append a css file to the file list
     * @param string $cssFile URL of the CSS added
     */
    public function appendCss($cssFile);

    /**
     * clear the included css files list
     */
    public function clearCss();

    /**
     * Return the list of css files
     * @return array list of URL
     */
    public function getCss();

    /**
     * append a js file to the file list
     * @param string $jsFile URL of the js added
     */
    public function appendJs($jsFile);

    /**
     * clear the included js files list
     */
    public function clearJs();

    /**
     * Return the list of js files
     * @return array list of URL
     */
    public function getJs();

    /**
     * set the page title
     *
     * @param string $pageTitle page title
     */
    public function setPageTitle($pageTitle);

    /**
     * get the page title
     *
     * @return string page title
     */
    public function getPageTitle();

}
