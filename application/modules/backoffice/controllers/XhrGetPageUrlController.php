<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    yet to be written
 * @version    $Id:
 */
use Rubedo\Services\Manager;

/**
 * Backoffice authentication Controller
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Backoffice_XhrGetPageUrlController extends Zend_Controller_Action
{

    public function indexAction ()
    {
        $pageId = $this->getRequest()->getParam('page-id');
        if (! $pageId) {
            throw new \Rubedo\Exceptions\User('this action need a page-id');
        }
        $page = Manager::getService('Pages')->findById($pageId);
        if (! $page) {
            throw new \Rubedo\Exceptions\NotFound('the page-id doesn\'t match a page');
        }
        $pageUrl = Manager::getService('Url')->getPageUrl($pageId);
        
        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'];
        $httpProtocol = $isHttps ? 'HTTPS' : 'HTTP';
        
        $targetSite = Manager::getService('Sites')->findById($page['site']);
        $protocol = in_array($httpProtocol, $targetSite['protocol'])?$httpProtocol:array_pop($targetSite['protocol']);
        $protocol = strtolower($protocol);
        
        $url = $protocol.'://' . Manager::getService('Sites')->getHost($page['site']) . '/' . $pageUrl;
        
        $returnArray = array(
            'url' => $url
        );
        
        $this->_helper->json($returnArray);
    }
}
