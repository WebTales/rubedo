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
        
        if (in_array($page, array(
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

    public function getUrl ($data, $encode = false)
    {
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
            if(in_array($key, array('controller','action'))){
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

}
