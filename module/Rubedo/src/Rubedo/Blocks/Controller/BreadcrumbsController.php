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

Use Rubedo\Services\Manager;

/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class BreadcrumbsController extends AbstractController
{

    public function indexAction ()
    {
        $output = $this->params()->fromQuery();
        
        $blockConfig = $this->params()->fromQuery('block-config', array());
        $output['displayBlock'] = false;
        if (($site = $this->params()->fromQuery('site', false)) && isset($site['homePage'])) {
            $rootPage = $site['homePage'];
        }
        
        $currentPage = $this->params()->fromQuery('currentPage');
        
        $output['currentPage'] = Manager::getService('Pages')->findById($currentPage);
        
        $rootline = $this->params()->fromQuery('rootline', array());
        $rootline = array_reverse($rootline);
        $rootlineArray = array();
        
        foreach ($rootline as $pageId) {
            if ($pageId == $rootPage) {
                $output['displayBlock'] = true;
            }
            if ($pageId == $currentPage && ! $this->params()->fromQuery('content-id')) {
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
        if ($this->params()->fromQuery('content-id')){
            $currentContent=Manager::getService("Contents")->findById("52cc2322f05c1d8c1100000c", true, false);
            if ($currentContent){
                $output['text']=$currentContent['text'];
            }
        }
        
        if (isset($blockConfig['displayType']) && ! empty($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/breadcrumbs.html.twig");
        }
        
        $css = array();
        $js = array();
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
