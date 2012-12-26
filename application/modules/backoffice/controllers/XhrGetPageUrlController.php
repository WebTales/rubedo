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
            throw new Zend_Controller_Exception('this action need a page-id');
        }
        $page = Manager::getService('Pages')->findById($pageId);
        if (! $page) {
            throw new Zend_Controller_Exception(
                    'the page-id doesn\'t match a page');
        }
        $pageUrl = Manager::getService('Url')->getPageUrl($pageId);
        $site = Manager::getService('Sites')->findById($page['site']);
        
        $url = 'http://' . $site['text'] . $pageUrl;
        
        $returnArray = array(
                'url' => $url
        );
        
        $this->_helper->json($returnArray);
    }
}
