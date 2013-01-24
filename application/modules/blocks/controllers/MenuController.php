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
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_MenuController extends Blocks_AbstractController
{

    /**
     * Default Action
     */
    public function indexAction ()
    {
        $blockConfig = $this->getParam('block-config', array());
 
        if (isset($blockConfig['rootPage'])) {
            $rootPage = $blockConfig['rootPage'];
        } else {
            $rootPage = $this->getParam('rootPage');
        }
		
		$output['rootPage'] = $rootPage;
		$output['pages'] = array();
		     
        $levelOnePages = Manager::getService('Pages')->readChild($output['rootPage']);

        foreach ($levelOnePages as $page) {
            $tempArray = array();
            $tempArray['url'] = $this->_helper->url->url(array(
                'pageId' => $page['id']
            ), null, true);
            $tempArray['title'] = $page['title'];
            $tempArray['id'] = $page['id'];
            $levelTwoPages = Manager::getService('Pages')->readChild($page['id']);
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
                
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/menu.html.twig");
        
        $css = array();
        $js = array();
        
        $this->_sendResponse($output, $template, $css, $js);
    }
}
