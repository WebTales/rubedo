<?php
/**
 * Rubedo -- ECM solution Copyright (c) 2012, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Router;

use Rubedo\Interfaces\Router\IUrl;
use Rubedo\Services\Manager;

/**
 * Front Office URL service
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Url implements IUrl
{

    protected static $_useCache = true;

    /**
     * param delimiter
     */
    const PARAM_DELIMITER = '&';

    /**
     * URI delimiter
     */
    const URI_DELIMITER = '/';

    protected static $_disableNav = false;

    /**
     * Return page id based on request URL
     *
     * @param string $url
     *            requested URL
     * @return string int
     */
    public function getPageId($url, $host) {
        if (false !== strpos($url, '?')) {

            list($url, $querystring) = explode('?', $url);
        }

        $site = Manager::getService('Sites')->findByHost($host);
        if (null == $site) {
            $siteArray = Manager::getService('Sites')->getList();
            $site = current($siteArray['data']);
        }

        $siteId = $site['id'];
        //unset($site);
        $cachedUrl = Manager::getService('UrlCache')->findByUrl($url, $siteId);
        if (self::$_useCache && null != $cachedUrl) {
            return $cachedUrl['pageId'];
        }

        $urlSegments = explode(self::URI_DELIMITER, trim($url, self::URI_DELIMITER));
        $lastMatchedNode = 'root';
        if (empty($urlSegments[0])) {
            if(isset($site['homePage'])){
                return $site['homePage'];
            }else{
                return null;
            }
        }

        $nbSegments = count($urlSegments);
        $nbMatched = 0;

        foreach ($urlSegments as $value) {
            $matchedNode = Manager::getService('Pages')->matchSegment($value, $lastMatchedNode, $siteId);
            if (null === $matchedNode) {
                break;
            } else {
                $lastMatchedNode = $matchedNode['id'];
            }
            $nbMatched++;
        }

        if ($nbMatched == 0) {
            return null;
        }

        if ($nbSegments > $nbMatched) {
            $partial = true;
        } else {
            $partial = false;
            $urlToCache = array('pageId' => $lastMatchedNode, 'url' => $url, 'siteId' => $siteId);
            if (self::$_useCache) {
                Manager::getService('UrlCache')->create($urlToCache);
            }
        }

        return $lastMatchedNode;
    }

    public function disableNavigation() {
        self::$_disableNav = true;
    }

    /**
     * (non-PHPdoc)
     * @see \Rubedo\Interfaces\Router\IUrl::getPageUrl()
     */
    public function getPageUrl($pageId) {
        $cachedUrl = Manager::getService('UrlCache')->findByPageId($pageId);
        if (!self::$_useCache || null === $cachedUrl) {
            $url = '';
            $page = Manager::getService('Pages')->findById($pageId);

            if (!isset($page['text'])) {
                throw new \Zend_Controller_Router_Exception('no page found');
            }

            if (!ctype_alpha($page['text'])) {
                throw new \Zend_Controller_Router_Exception('page name should be alphanum');
            }

            $siteId = $page['site'];

            $rootline = Manager::getService('Pages')->getAncestors($page);

            foreach ($rootline as $value) {
                $url .= self::URI_DELIMITER;
                $url .= $value['text'];
            }

            $url .= self::URI_DELIMITER;
            $url .= $page['text'];
            $urlToCache = array('pageId' => $pageId, 'url' => $url, 'siteId' => $siteId);
            if (self::$_useCache) {
                Manager::getService('UrlCache')->create($urlToCache);
            }

            return $url;
        } else {
            return $cachedUrl['url'];
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Rubedo\Interfaces\Router\IUrl::getUrl()
     */
    public function getUrl($data, $encode = false) {
        if (self::$_disableNav) {
            $currentUri = \Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();

            return trim($currentUri . '#', self::URI_DELIMITER);
        }

        if (!isset($data['pageId'])) {
            throw new \Zend_Controller_Router_Exception('no page given');
        }

        $url = $this->getPageUrl($data['pageId']);
        unset($data['pageId']);
        $queryStringArray = array();
        
        if(isset($data['prefix'])){
            
            $prefix = $data['prefix'];
            unset($data['prefix']);
        }else{
            $prefix = '';
        }

        foreach ($data as $key => $value) {
            if (in_array($key, array('controller', 'action'))) {
                continue;
            }
            
            $key = ($encode) ? urlencode($key) : $key;
            if($prefix){
                $key = $prefix.'['.$key.']';
            }
            if (is_array($value)) {
                foreach ($value as $arrayValue) {
                    $arrayValue = ($encode) ? urlencode($arrayValue) : $arrayValue;
                    $queryStringArray[] = $key . '[]=' . $arrayValue;
                }
            } else {
                if ($encode)
                    $value = urlencode($value);
                $queryStringArray[] = $key . '=' . $value;
            }
        }
        if (count($queryStringArray) > 0) {
            $url .= '?' . implode(self::PARAM_DELIMITER, $queryStringArray);
        }

        return ltrim($url, self::URI_DELIMITER);
    }

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
    public function url(array $urlOptions = array(), $name = null, $reset = false, $encode = true) {
        $router = \Zend_Controller_Front::getInstance()->getRouter();

        return $router->assemble($urlOptions, $name, $reset, $encode);
    }

    /**
     * Return the url of the single content page of the site if the single page exist
     *
     * @param string $contentId
     * 	Id of the content to display
     * @param string $siteId
     * 	Id of the site
     *
     * @return string Url
     */
    public function displaySingleUrl($contentId, $siteId = null) {
        if ($siteId === null) {
            $doNotAddSite = true;
            $siteId = Manager::getService('PageContent')->getCurrentSite();
        } else {
            $doNotAddSite = false;
        }
        $page = Manager::getService('Pages')->findByNameAndSite('single', $siteId);

        if ($page) {
            $data = array('pageId' => $page['id'], 'content-id' => $contentId);
            $pageUrl = $this->url($data,null,true);
            if ($doNotAddSite) {
                return $pageUrl;
            } else {

                return 'http://' . Manager::getService('Sites')->getHost($siteId) . $pageUrl;
            }
        } else {
            return '#';
        }

    }

}
