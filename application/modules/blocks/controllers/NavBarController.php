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
Use Rubedo\Services\Manager;

require_once ('AbstractController.php');

/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_NavBarController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $blockConfig = $this->getParam('block-config', array());

        if (isset($blockConfig['rootPage'])) {
            $rootPage = $blockConfig['rootPage'];
        } else {
            $rootPage = $this->getParam('rootPage');
        }
        
        if (isset($blockConfig['useSearchEngine'])) {
            $useSearchEngine = $blockConfig['useSearchEngine'];
        } else {
            $useSearchEngine = false;
        }
        
        if (isset($blockConfig['searchPage'])) {
            $searchPage = $blockConfig['searchPage'];
        } else {
            $searchPage = null;
        }
        
        $site = $this->getParam('site');
        $output['homePage'] = isset($site['homePage']) ? $site['homePage'] : null;
        
        $responsive = true;
        
        // responsive : true or false
        
        $position = "static-top";
        
        // position : none, fixed-top, fixed-bottom, static-top
        $brand = "Rubedo";
        
        $session = Manager::getService('Session');
        $lang = $session->get('lang', 'fr');
        
        $output['currentPage'] = $this->getRequest()->getParam('currentPage');
        $output['rootPage'] = $rootPage;
        $output['rootline'] = $this->getRequest()->getParam('rootline', array());
        $output['useSearchEngine'] = $useSearchEngine;
        $output['searchPage'] = $searchPage;
        $output['pages'] = array();
        $output['logo']= isset($blockConfig['logo'])?$blockConfig['logo']:null;
        
        $excludeFromMenuCondition = array('operator'=>'$ne','property'=>'excludeFromMenu','value'=>true);
        
        $levelOnePages = Manager::getService('Pages')->readChild($output['rootPage'],array($excludeFromMenuCondition));
        foreach ($levelOnePages as $page) {
            $tempArray = array();
            $tempArray['url'] = $this->_helper->url->url(array(
                'pageId' => $page['id']
            ), null, true);
            $tempArray['title'] = $page['title'];
            $tempArray['id'] = $page['id'];
            $levelTwoPages = Manager::getService('Pages')->readChild($page['id'],array($excludeFromMenuCondition));
            if (count($levelTwoPages)) {
                $tempArray['pages'] = array();
                foreach ($levelTwoPages as $subPage) {
                    $tempSubArray = array();
                    $tempSubArray['url'] = $this->_helper->url->url(array(
                        'pageId' => $subPage['id']
                    ), null, true);
                    $tempSubArray['title'] = $subPage['title'];
                    $tempSubArray['id'] = $subPage['id'];
                    $tempArray['pages'][] = $tempSubArray;
                }
            }
            
            $output['pages'][] = $tempArray;
        }
                
        $twigVar["data"] = $output;
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/navbar.html.twig");
        
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
}
