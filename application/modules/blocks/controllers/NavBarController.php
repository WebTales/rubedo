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
    public function indexAction() {
        // images examples
        // TODO : load data from services
        $id = "987194";
        // block id
        $responsive = true;
        // responsive : true or false
        $position = "static-top";
        // position : none, fixed-top, fixed-bottom, static-top
        $brand = "Rubedo";
        // brand
        $options = array("loginform", "langselector", "themechooser", "search");
        $fr = array( array('id' => 1, 'type' => 'link', 'caption' => 'A propos', 'href' => '#about', 'colapse' => true, 'modal' => true, 'icon' => 'icon-info-sign'), array('id' => 2, 'type' => 'link', 'caption' => 'Contact', 'href' => '/index/contact', 'colapse' => true, 'modal' => false, 'icon' => 'icon-envelope'), array('id' => 3, 'type' => 'dropdown', 'caption' => 'Rubedo à la loupe', 'colapse' => true, 'modal' => false, 'icon' => 'icon-zoom-in', 'list' => array( array('caption' => 'Mobilité', 'href' => '/index/responsive'), array('caption' => 'Accessibilité', 'href' => '/index/accessible'), array('caption' => 'Performances', 'href' => '/index/performant'), array('caption' => 'Ergonomie', 'href' => '/index/ergonomic'), array('caption' => 'Richesse', 'href' => '/index/rich'), array('caption' => 'Extensibilité', 'href' => '/index/extensible'), array('caption' => 'Robustesse', 'href' => '/index/solid'), array('caption' => 'Pérénité', 'href' => '/index/durable'))));
        $en = array( array('id' => 1, 'type' => 'link', 'caption' => 'About', 'href' => '#about', 'colapse' => true, 'modal' => true, 'icon' => 'icon-info-sign'), array('id' => 2, 'type' => 'link', 'caption' => 'Contact', 'href' => '/index/contact', 'colapse' => true, 'modal' => false, 'icon' => 'icon-envelope'), array('id' => 3, 'type' => 'dropdown', 'caption' => 'Close-up on Rubedo', 'colapse' => true, 'modal' => false, 'icon' => 'icon-zoom-in', 'list' => array( array('caption' => 'Mobile', 'href' => '/index/responsive'), array('caption' => 'Accessible', 'href' => '/index/accessible'), array('caption' => 'Performant', 'href' => '/index/performant'), array('caption' => 'Ergonomic', 'href' => '/index/ergonomic'), array('caption' => 'Rich', 'href' => '/index/rich'), array('caption' => 'Extensible', 'href' => '/index/extensible'), array('caption' => 'Solid', 'href' => '/index/solid'), array('caption' => 'Durable', 'href' => '/index/durable'))));

        $session = Manager::getService('Session');
        $lang = $session->get('lang', 'fr');

        //$output["id"] = $id;
        //$output["responsive"] = $responsive;
        //$output["position"] = $position;
        //$output["brand"] = $brand;
        //$output["options"] = $options;
        //$output["components"] = $$lang;

        $output['currentPage'] = $this->getRequest()->getParam('currentPage');
        $output['rootPage'] = $this->getRequest()->getParam('rootPage');
        $output['pages'] = array();

        $levelOnePages = Manager::getService('Pages')->readChild($output['rootPage']);
        foreach ($levelOnePages as $page) {
            $tempArray = array();
            $tempArray['url'] = $this->_helper->url->url(array('pageId'=>$page['id']),null,true);
            $tempArray['title'] = $page['title'];
            $tempArray['id'] = $page['id'];
            $levelTwoPages = Manager::getService('Pages')->readChild($page['id']);
            if (count($levelTwoPages)) {
                $tempArray['pages'] = array();
                foreach ($levelTwoPages as $subPage) {
                    $tempSubArray = array();
                    $tempSubArray['url'] = $this->_helper->url->url(array('pageId'=>$subPage['id']),null,true);
                    $tempSubArray['title'] = $subPage['title'];
                    $tempSubArray['id'] = $subPage['id'];
					$tempArray['pages'][] = $tempSubArray;
                }
                
            }

            $output['pages'][] = $tempArray;
        }

        //Zend_Debug::dump($output['pages']);die();

        $twigVar["data"] = $output;

        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/navbar.html");

        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }

}
