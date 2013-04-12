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
Use Rubedo\Services\Manager;

require_once ('AbstractController.php');

/**
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_SiteMapController extends Blocks_AbstractController
{
    
    protected $_defaultTemplate = 'sitemap';

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $params = $this->getAllParams();
        $blockConfig = $params['block-config'];
        $output = array();
        
        $output['rootPage'] = isset($blockConfig['rootPage']) ? $blockConfig['rootPage'] : $params['site']['homePage'];
        $output['displayLevel'] = isset($blockConfig['displayLevel']) ? $blockConfig['displayLevel'] : null;
        $output['displayTitle'] = isset($params['displayTitle']) ? $params['displayTitle'] : false;
        $output['blockTitle'] = $params['blockTitle'];
        
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
                $this->_getPages($tempArray, $levelTwoPages);
            }
        
            $output['pages'][] = $tempArray;
        }
        if (isset($blockConfig['displayType']) && !empty($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/".$this->_defaultTemplate.".html.twig");
        }
        
        $css = array();
        $js = array('/templates/'.Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/tree.js"));
        
        $this->_sendResponse($output, $template, $css, $js);
    }
    
    protected function _getPages(&$page, $childs) {
        $page['pages'] = array();
        
        foreach ($childs as $subPage) {
            $tempSubArray = array();
            $tempSubArray['url'] = $this->_helper->url->url(array(
                'pageId' => $subPage['id']
            ), null, true);
            $tempSubArray['title'] = $subPage['title'];
            $tempSubArray['id'] = $subPage['id'];
            
            $excludeFromMenuCondition = array('operator'=>'$ne','property'=>'excludeFromMenu','value'=>true);
            $pageChilds = Manager::getService('Pages')->readChild($subPage['id'],array($excludeFromMenuCondition));
            if (count($pageChilds)) {
                $this->_getPages($tempSubArray, $pageChilds);
            }
            
            $page['pages'][] = $tempSubArray;
        }
    }
}
