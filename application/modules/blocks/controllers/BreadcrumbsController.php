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
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_BreadcrumbsController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $output = $this->getAllParams();
        
        $blockConfig = $this->getParam('block-config', array());
        $output['displayBlock'] = false;
        if (($site = $this->getParam('site', false)) && isset($site['homePage'])) {
            $rootPage = $site['homePage'];
        }
        
        $currentPage = $this->getRequest()->getParam('currentPage');
        
        $output['currentPage'] = Manager::getService('Pages')->findById($currentPage);
        
        $rootline = $this->getRequest()->getParam('rootline', array());
        $rootline = array_reverse($rootline);
        $rootlineArray = array();
        
        foreach ($rootline as $pageId) {
            if ($pageId == $rootPage) {
                $output['displayBlock'] = true;
            }
            if ($pageId == $currentPage && ! $this->getParam('content-id')) {
                continue;
            }
            $rootlineArray[] = Manager::getService('Pages')->findById($pageId);
            if ($pageId == $rootPage) {
                break;
            }
        }
        $rootlineArray = array_reverse($rootlineArray);
        
        $output['rootPage'] = $rootPage;
        $output['rootline'] = $rootlineArray;
        
        if (isset($blockConfig['displayType']) && ! empty($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/breadcrumbs.html.twig");
        }
        
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
}
