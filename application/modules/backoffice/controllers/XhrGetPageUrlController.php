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
use Rubedo\Services\Manager;

/**
 * Get Page URL Controller
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
            throw new \Rubedo\Exceptions\NotFound(
                    'the page-id doesn\'t match a page');
        }
        $pageUrl = Manager::getService('Url')->getPageUrl($pageId);
        
        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'];
        $httpProtocol = $isHttps ? 'HTTPS' : 'HTTP';
        
        $targetSite = Manager::getService('Sites')->findById($page['site']);
        if (! is_array($targetSite['protocol']) ||
                 count($targetSite['protocol']) == 0) {
            throw new Rubedo\Exceptions\Server(
                    'Protocol is not set for current site');
        }
        $protocol = in_array($httpProtocol, $targetSite['protocol']) ? $httpProtocol : array_pop(
                $targetSite['protocol']);
        $protocol = strtolower($protocol);
        
        $url = $protocol . '://' .
                 Manager::getService('Sites')->getHost($page['site']) . '/' .
                 ltrim($pageUrl,'/');
        
        $returnArray = array(
                'url' => $url
        );
        
        $this->_helper->json($returnArray);
    }
}
