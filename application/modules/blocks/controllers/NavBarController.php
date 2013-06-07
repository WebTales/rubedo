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
Use Rubedo\Services\Manager, WebTales\MongoFilters\Filter;

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
        $output = $this->getAllParams();
        
        $blockConfig = $this->getParam('block-config', array());
        if (isset($blockConfig['menuLevel'])) {
            $startLevel = $blockConfig['menuLevel'];
        } else {
            $startLevel = 1;
        }
        
        if (isset($blockConfig['displayType']) && ! empty($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            if (isset($blockConfig['style']) && $blockConfig['style'] == 'Vertical') {
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/verticalMenu.html.twig");
            } else {
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/navbar.html.twig");
            }
        }
        
        if (isset($blockConfig['rootPage'])) {
            $this->rootPage = $blockConfig['rootPage'];
        } else {
            $this->rootPage = $this->getParam('rootPage');
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
        if (isset($blockConfig['displayRootPage'])) {
            $displayRootPage = $blockConfig['displayRootPage'];
        } else {
            $displayRootPage = true;
        }
        
        $site = $this->getParam('site');
        $output['homePage'] = isset($site['homePage']) ? $site['homePage'] : null;
        
        $session = Manager::getService('Session');
        
        $output['currentPage'] = $this->getRequest()->getParam('currentPage');
        $this->currentPage = $output['currentPage'];
        
        $output['rootPage'] = Manager::getService('Pages')->findById($this->rootPage);
        $output['rootline'] = $this->rootline = $this->getRequest()->getParam('rootline', array());
        $output['useSearchEngine'] = $useSearchEngine;
        $output['searchPage'] = $searchPage;
        $output['pages'] = array();
        $output['logo'] = isset($blockConfig['logo']) ? $blockConfig['logo'] : null;
        $output['displayRootPage'] = $displayRootPage;
        
        $this->excludeFromMenuCondition = Filter::Factory('Not')->setName('excludeFromMenu')->setValue(true);
        
        $this->pageService = Manager::getService('Pages');
        
        $levelOnePages = $this->_getPagesByLevel($output['rootPage']['id'], $startLevel);
        
        foreach ($levelOnePages as $page) {
            $tempArray = array();
            $tempArray['url'] = $this->_helper->url->url(array(
                'pageId' => $page['id']
            ), null, true);
            $tempArray['title'] = $page['title'];
            $tempArray['id'] = $page['id'];
            $levelTwoPages = $this->pageService->readChild($page['id'], $this->excludeFromMenuCondition);
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
        
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }

    protected function _getPagesByLevel ($rootPage, $targetLevel, $currentLevel = 1)
    {
        $pages = $this->pageService->readChild($rootPage, $this->excludeFromMenuCondition);
        if ($currentLevel === $targetLevel) {
            return $pages;
        }
        foreach ($pages as $page) {
            if (in_array($page['id'], $this->rootline)) {
                return $this->_getPagesByLevel($page['id'], $targetLevel, $currentLevel + 1);
            }
        }
        return array();
    }
}
