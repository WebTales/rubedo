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
use Zend\Debug\Debug;

/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class CategoryController extends AbstractController
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
        $showLevel2Categories=isset($blockConfig['showLevel2Categories']) ? $blockConfig['showLevel2Categories'] : false;
        if (isset($blockConfig['displayType']) && ! empty($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/category.html.twig");
        }
        if ((isset($blockConfig['rootPage']))&&(!empty($blockConfig['rootPage']))){
            $this->rootPage = $blockConfig['rootPage'];
        } else {
            $this->rootPage = $this->params()->fromQuery('current-page');
        }
        if ((isset($blockConfig['nbOfColumns']))&&(!empty($blockConfig['nbOfColumns']))){
            $output['nbOfColumns'] = $blockConfig['nbOfColumns'];
        } else {
            $output['nbOfColumns']=1;
        }
        if ((isset($blockConfig['imageHeight']))&&(!empty($blockConfig['imageHeight']))){
            $output['imageHeight'] = $blockConfig['imageHeight'];
        } else {
            $output['imageHeight']=100;
        }
        if ((isset($blockConfig['imageWidth']))&&(!empty($blockConfig['imageWidth']))){
            $output['imageWidth'] = $blockConfig['imageWidth'];
        } else {
            $output['imageWidth']=100;
        }
        $this->pageService = Manager::getService('Pages');
        $output['rootline'] = $this->rootline = $this->params()->fromQuery('rootline', array());
        $this->excludeFromMenuCondition = Filter::factory('Not')->setName('excludeFromMenu')->setValue(true);
        $output['pages'] = array();
        $levelOnePages = $this->_getPagesByLevel($this->rootPage, $startLevel);
        $urlOptions = array(
            'encode' => true,
            'reset' => true
        );
        foreach ($levelOnePages as $page) {
            if ((isset($page['eCTitle']))&&(!empty($page['eCTitle']))){
                $tempArray = array();
                $tempArray['url'] = $this->url()->fromRoute(null, array(
                    'pageId' => $page['id']
                ), $urlOptions);

                $tempArray['title'] = $page['eCTitle'];
                $tempArray['description'] = isset($page['eCDescription']) ? $page['eCDescription'] : null;
                $tempArray['image'] = isset($page['eCImage']) ? $page['eCImage'] : null;
                $tempArray['id'] = $page['id'];
                $levelTwoPages = $this->pageService->readChild($page['id'], $this->excludeFromMenuCondition);
                if ((count($levelTwoPages))&&$showLevel2Categories) {
                   $tempArray['pages'] = array();
                        foreach ($levelTwoPages as $subPage) {
                            if ((isset($subPage['eCTitle']))&&(!empty($subPage['eCTitle']))){
                                $tempSubArray = array();
                                $tempSubArray['url'] = $this->url()->fromRoute(null, array(
                                    'pageId' => $subPage['id']
                                ), $urlOptions);

                                $tempSubArray['title'] = $subPage['eCTitle'];
                                $tempSubArray['id'] = $subPage['id'];
                                $tempArray['pages'][] = $tempSubArray;
                        }
                    }
                }
                $output['pages'][] = $tempArray;
            }
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
