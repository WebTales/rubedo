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
namespace Rubedo\Blocks\Controller;

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;

/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class NavBarController extends AbstractController
{

    public function indexAction()
    {
        $output = $this->params()->fromQuery();
        
        $blockConfig = $this->params()->fromQuery('block-config', array());
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
            $this->rootPage = $this->params()->fromQuery('rootPage');
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
        
        $site = $this->params()->fromQuery('site');
        $output['homePage'] = isset($site['homePage']) ? $site['homePage'] : null;
        
        $output['currentPage'] = $this->params()->fromQuery('currentPage');
        $this->currentPage = $output['currentPage'];
        
        $output['rootPage'] = Manager::getService('Pages')->findById($this->rootPage);
        $output['rootline'] = $this->rootline = $this->params()->fromQuery('rootline', array());
        $output['useSearchEngine'] = $useSearchEngine;
        $output['searchPage'] = $searchPage;
        $output['pages'] = array();
        $output['logo'] = isset($blockConfig['logo']) ? $blockConfig['logo'] : null;
        $output['displayRootPage'] = $displayRootPage;
        
        $this->excludeFromMenuCondition = Filter::factory('Not')->setName('excludeFromMenu')->setValue(true);
        
        $this->pageService = Manager::getService('Pages');
        
        $levelOnePages = $this->_getPagesByLevel($output['rootPage']['id'], $startLevel);
        
        $urlOptions = array(
            'encode' => true,
            'reset' => true
        );
        
        foreach ($levelOnePages as $page) {
            $tempArray = array();
            $tempArray['url'] = $this->url()->fromRoute(null, array(
                'pageId' => $page['id']
            ), $urlOptions);
            
            $tempArray['title'] = $page['title'];
            $tempArray['text'] = $page['text'];
            $tempArray['id'] = $page['id'];
            $levelTwoPages = $this->pageService->readChild($page['id'], $this->excludeFromMenuCondition);
            if (count($levelTwoPages)) {
                $tempArray['pages'] = array();
                foreach ($levelTwoPages as $subPage) {
                    $tempSubArray = array();
                    $tempSubArray['url'] = $this->url()->fromRoute(null, array(
                        'pageId' => $subPage['id']
                    ), $urlOptions);
                    
                    $tempSubArray['title'] = $subPage['title'];
                    $tempSubArray['text'] = $subPage['text'];
                    $tempSubArray['id'] = $subPage['id'];
                    $tempArray['pages'][] = $tempSubArray;
                }
            }
            
            $output['pages'][] = $tempArray;
        }
        
        $css = array();
        $js = array();
        return $this->_sendResponse($output, $template, $css, $js);
    }

    protected function _getPagesByLevel($rootPage, $targetLevel, $currentLevel = 1)
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
