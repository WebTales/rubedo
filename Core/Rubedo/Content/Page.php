<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */
namespace Rubedo\Content;

Use Rubedo\Services\Manager;
use Rubedo\Interfaces\Content\IPage;
/**
 * Page service
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Page implements  IPage
{
    /**
     * CSS to be included in the page
     *
     * @var array
     */
    protected static $_css = array();

    /**
     * JS to be included in the page
     *
     * @var array
     */
    protected static $_js = array();

    /**
     * Page title
     *
     * @var string
     */
    protected static $_title = '';

    /**
     * Return page infos based on its ID
     *
     * @param string|int $pageId requested URL
     * @return array
     */
    public function getPageInfo($pageId) {

        $pageService = Manager::getService('Masks');

        switch($pageId) {
            case "index" :
                $pageInfo['template'] = 'index.html';
                $pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'HeadLine', 'Input' => null, 'Output' => 'headline_content'), array('Module' => 'ContentList', 'Input' => null, 'Output' => 'contentlist_content'), array('Module' => 'Carrousel', 'Input' => null, 'Output' => 'carousel_content'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "contact" :
                $pageInfo['template'] = 'contact.html';
                $pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'SimpleContent', 'Input' => '300', 'Output' => 'bloc1'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'IFrame', 'Input' => null, 'Output' => 'bloc2'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "responsive" :
                $pageInfo['template'] = 'responsive.html';
                $pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "accessible" :
                $pageInfo['template'] = 'page.html';
                $pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "performant" :
                $pageInfo['template'] = 'page.html';
                $pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "ergonomic" :
                $pageInfo['template'] = 'page.html';
                $pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "rich" :
                $pageInfo['template'] = 'page.html';
                $pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_whoarewe'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "extensible" :
                $pageInfo['template'] = 'page.html';
                $pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "solid" :
                $pageInfo['template'] = 'page.html';
                $pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "durable" :
                $pageInfo['template'] = 'page.html';
                $pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "search" :
                $pageInfo['template'] = 'result.html';
                $pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'), array('Module' => 'Search', 'Input' => null, 'Output' => 'search'));
                break;
            case "newpage" :
                $pageInfo = $pageService->findById('50ab7ee29a199dd107000000');
                //\Zend_Debug::dump($pageInfo['rows']);
                $pageInfo['rows'] = $this->_getRowsInfos($pageInfo['rows']);
                $pageInfo['template'] = 'root/page.html';

                //$page = $pageService->findById('50ab7ee29a199dd107000000');
                //\Zend_Debug::dump($pageInfo);
                //die();
                break;
        }

        $pageInfo['css'] = self::$_css;
        $pageInfo['js'] = self::$_js;
        $pageInfo['title'] = self::$_title;

        return $pageInfo;
    }

    protected function _getColumnsInfos(array $columns = null) {
        if ($columns === null) {
            return null;
        }
        $returnArray = $columns;
        foreach ($columns as $key => $column) {
            if (is_array($column['bloc'])) {
                $returnArray[$key]['blocks'] = $this->_getBlocksInfos($column['bloc']);
                unset($returnArray[$key]['bloc']);
            } else {
                $returnArray[$key]['rows'] = $this->_getRowsInfos($column['rows']);
            }
        }
        return $returnArray;
    }

    protected function _getBlocksInfos(array $blocks) {
        $returnArray = array();
        foreach ($blocks as $block) {
	           $returnArray[]= Manager::getService('Block')->getBlockData($block);
        }
        return $returnArray;
    }

    protected function _getRowsInfos(array $rows = null) {
        if ($rows === null) {
            return null;
        }
        $returnArray = $rows;
        foreach ($rows as $key => $row) {
            if (is_array($row['columns'])) {
                $returnArray[$key]['columns'] = $this->_getColumnsInfos($row['columns']);
            }
        }
        return $returnArray;
    }

    /**
     * append a css file to the file list
     * @param string $cssFile URL of the CSS added
     */
    public function appendCss($cssFile) {
        self::$_css[] = $cssFile;
    }

    /**
     * clear the included css files list
     */
    public function clearCss() {
        self::$_css = array();
    }

    /**
     * Return the list of css files
     * @return array list of URL
     */
    public function getCss() {
        return self::$_css;
    }

    /**
     * append a js file to the file list
     * @param string $jsFile URL of the js added
     */
    public function appendJs($jsFile) {
        self::$_js[] = $jsFile;
    }

    /**
     * clear the included js files list
     */
    public function clearJs() {
        self::$_js = array();
    }

    /**
     * Return the list of js files
     * @return array list of URL
     */
    public function getJs() {
        return self::$_js;
    }

    /**
     * set the page title
     *
     * @param string $pageTitle page title
     */
    public function setPageTitle($pageTitle) {
        self::$_title = $pageTitle;
    }

    /**
     * get the page title
     *
     * @return string page title
     */
    public function getPageTitle() {
        return self::$_title;
    }

}
