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
namespace Rubedo\Interfaces\Router;

/**
 * Front Office URL service
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
Interface IUrl
{

    /**
     * Return page id based on request URL
     *
     * @param string $url
     *            requested URL
     * @return string int
     */
    public function getPageId ($url, $host);

    /**
     * Generates an url given the name of a route.
     *
     * @access public
     * @param array $urlOptions
     *            Options passed to the assemble method of the
     *            Route object.
     * @param mixed $name
     *            The name of a Route to use. If null it will use the
     *            current Route
     * @param bool $reset
     *            Whether or not to reset the route defaults with those
     *            provided
     * @return string Url for the link href attribute.
     */
    public function url (array $urlOptions = array(), $name = null, $reset = false, $encode = true);

    /**
     * Return the path part of the URL of a page given by its ID
     *
     * @param string $pageId            
     * @return string
     */
    public function getPageUrl ($pageId,$locale);

    /**
     * Return the path part of the URL matching parameters given in $data array
     *
     * @param array $data            
     * @param bool $encode            
     * @return string
     */
    public function getUrl ($data, $encode = false);

    /**
     * Return the url of the single content page of the site if the single page
     * exist
     *
     * @param string $contentId
     *            Id of the content to display
     * @param string $type
     *            Type of the URL : "default" or "cononical"
     * @param string $siteId
     *            Id of the site
     *            
     * @return string Url
     */
    public function displayUrl ($contentId, $type = "default", $siteId = null, $defaultPage = null);
}
