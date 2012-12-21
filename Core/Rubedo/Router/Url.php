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
    public function getPageId ($url)
    {
        $page = "index";
        
        $matches = array();
        $regex = '~/index/([^/?]*)~i';
        if (preg_match($regex, $url, $matches)) {
            $page = $matches[1];
        } else {
            $regex = '~/([^/?]*)~i';
            if (preg_match($regex, $url, $matches)) {
                $page = $matches[1];
            }
        }
        
        if (in_array($page, 
                array(
                        '',
                        'index'
                ))) {
            $page = 'accueil';
        }
        $page = Manager::getService('Pages')->findByName($page);
        if ($page['id'] == '') {
            return null;
        }
        
        return $page['id'];
    }
    
    public function disableNavigation(){
        self::$_disableNav = true;
    }

    public function getUrl ($data, $encode = false)
    {
        if(self::$_disableNav){
            $currentUri = \Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();

            return trim($currentUri.'#',self::URI_DELIMITER);
        }
        $url = self::URI_DELIMITER;
        
        if (! isset($data['pageId'])) {
            throw new \Zend_Controller_Router_Exception('no page given');
        }
        
        $page = Manager::getService('Pages')->findById($data['pageId']);
        unset($data['pageId']);
        
        if (! isset($page['text'])) {
            throw new \Zend_Controller_Router_Exception('no page found');
        }
        
        if (! ctype_alpha($page['text'])) {
            throw new \Zend_Controller_Router_Exception(
                    'page name should be alphanum');
        }
        $url .= $page['text'];
        $queryStringArray = array();
        
        foreach ($data as $key => $value) {
            if (in_array($key, 
                    array(
                            'controller',
                            'action'
                    ))) {
                continue;
            }
            $key = ($encode) ? urlencode($key) : $key;
            if (is_array($value)) {
                foreach ($value as $arrayValue) {
                    $arrayValue = ($encode) ? urlencode($arrayValue) : $arrayValue;
                    $queryStringArray[] = $key . '=' . $arrayValue;
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
     *        
     * @param array $urlOptions
     *            Options passed to the assemble method of the Route object.
     * @param mixed $name
     *            The name of a Route to use. If null it will use the current
     *            Route
     * @param bool $reset
     *            Whether or not to reset the route defaults with those provided
     * @return string Url for the link href attribute.
     */
    public function url (array $urlOptions = array(), $name = null, $reset = false, 
            $encode = true)
    {
        $router = \Zend_Controller_Front::getInstance()->getRouter();
       
        
        return $router->assemble($urlOptions, $name, $reset, $encode);
    }
}
