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
     * Current page parameters
     * @var array
     */
    protected $_pageInfo = array();

    /**
     * Return page infos based on its ID
     *
     * @param string|int $pageId requested URL
     * @return array
     */
    public function getPageInfo($pageId) {

        switch($pageId) {
            case "index" :
                $this->_pageInfo['template'] = 'index.html';
                $this->_pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'HeadLine', 'Input' => null, 'Output' => 'headline_content'), array('Module' => 'ContentList', 'Input' => null, 'Output' => 'contentlist_content'), array('Module' => 'Carrousel', 'Input' => null, 'Output' => 'carousel_content'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "contact" :
                $this->_pageInfo['template'] = 'contact.html';
                $this->_pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'SimpleContent', 'Input' => '300', 'Output' => 'bloc1'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'IFrame', 'Input' => null, 'Output' => 'bloc2'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "responsive" :
                $this->_pageInfo['template'] = 'responsive.html';
                $this->_pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "accessible" :
                $this->_pageInfo['template'] = 'page.html';
                $this->_pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "performant" :
                $this->_pageInfo['template'] = 'page.html';
                $this->_pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "ergonomic" :
                $this->_pageInfo['template'] = 'page.html';
                $this->_pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "rich" :
                $this->_pageInfo['template'] = 'page.html';
                $this->_pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_whoarewe'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "extensible" :
                $this->_pageInfo['template'] = 'page.html';
                $this->_pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "solid" :
                $this->_pageInfo['template'] = 'page.html';
                $this->_pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "durable" :
                $this->_pageInfo['template'] = 'page.html';
                $this->_pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'));
                break;
            case "search" :
                $this->_pageInfo['template'] = 'result.html';
                $this->_pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'BreadCrumb', 'Input' => null, 'Output' => 'liens'), array('Module' => 'PopIn', 'Input' => 1, 'Output' => 'popin_about'), array('Module' => 'PopIn', 'Input' => 2, 'Output' => 'popin_connect'), array('Module' => 'PopIn', 'Input' => 3, 'Output' => 'popin_confirm'), array('Module' => 'Search', 'Input' => null, 'Output' => 'search'));
                break;
            case "newpage" :
                $this->_pageInfo['template'] = 'root/page.html';
                $this->_pageInfo['blocks'] = array( array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'), array('Module' => 'HeadLine', 'Input' => null, 'Output' => 'headline_content'), array('Module' => 'ContentList', 'Input' => null, 'Output' => 'contentlist_content'), array('Module' => 'Carrousel', 'Input' => null, 'Output' => 'carousel_content'));
                break;
        }

        $this->_pageInfo['css'] = array();
        $this->_pageInfo['js'] = array();
        $this->_pageInfo['title'] = '';

        return $this->_pageInfo;
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
