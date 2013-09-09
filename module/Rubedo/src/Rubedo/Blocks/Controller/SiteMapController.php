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
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
class SiteMapController extends AbstractController
{

    protected $_defaultTemplate = 'sitemap';

    public function indexAction ()
    {
        $params = $this->params()->fromQuery();
        $blockConfig = $params['block-config'];
        $output = array();
        
        $output['rootPage'] = isset($blockConfig['rootPage']) ? $blockConfig['rootPage'] : $params['site']['homePage'];
        $output['displayLevel'] = isset($blockConfig['displayLevel']) ? $blockConfig['displayLevel'] : null;
        $output['displayTitle'] = isset($params['displayTitle']) ? $params['displayTitle'] : false;
        $output['blockTitle'] = $params['blockTitle'];
        
        $filters = Filter::factory('Not')->setName('excludeFromMenu')->setValue(true);
        $levelOnePages = Manager::getService('Pages')->readChild($output['rootPage'], $filters);
        
        $rootPage = Manager::getService('Pages')->findById($output['rootPage']);
        $urlOptions = array(
            'encode' => true,
            'reset' => true
        );
        $output['pages'] = array();
        
        $output['pages'][] = array(
            "url" => $this->url()->fromRoute(null, array(
                'pageId' => $rootPage['id']
            ), $urlOptions),
            "title" => $rootPage["title"],
            "id" => $rootPage["id"]
        );
        
        foreach ($levelOnePages as $page) {
            $tempArray = array();
            $tempArray['url'] = $this->url()->fromRoute(null, array(
                'pageId' => $page['id']
            ), $urlOptions);
            
            $tempArray['title'] = $page['title'];
            $tempArray['id'] = $page['id'];
            
            $levelTwoPages = Manager::getService('Pages')->readChild($page['id'], $filters);
            if (count($levelTwoPages)) {
                $this->_getPages($tempArray, $levelTwoPages);
            }
            
            $output['pages'][0]["pages"][] = $tempArray;
        }
        
        if (isset($blockConfig['displayType']) && ! empty($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $this->_defaultTemplate . ".html.twig");
        }
        
        $css = array();
        $js = array(
            $this->getRequest()->getBasePath() . '/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/tree.js")
        );
        
        return $this->_sendResponse($output, $template, $css, $js);
    }

    protected function _getPages (&$page, $childs)
    {
        $page['pages'] = array();
        $urlOptions = array(
            'encode' => true,
            'reset' => true
        );
        foreach ($childs as $subPage) {
            $tempSubArray = array();
            $tempSubArray['url'] = $this->url()->fromRoute(null, array(
                'pageId' => $subPage['id']
            ), $urlOptions);
            $tempSubArray['title'] = $subPage['title'];
            $tempSubArray['id'] = $subPage['id'];
            
            $filters = Filter::factory('Not')->setName('excludeFromMenu')->setValue(false);
            $pageChilds = Manager::getService('Pages')->readChild($subPage['id'], $filters);
            if (count($pageChilds)) {
                $this->_getPages($tempSubArray, $pageChilds);
            }
            
            $page['pages'][] = $tempSubArray;
        }
    }
}
