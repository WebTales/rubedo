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
    public function getPageInfo($pageId)
    {

        $pageService = Manager::getService('Pages');
        $pageInfo = $pageService->findById($pageId);
        $this->setPageTitle($pageInfo['text']);
        $pageInfo['rows'] = $this->_getRowsInfos($pageInfo['rows']);
        $pageInfo['template'] = 'root/page.html';

        $pageInfo['css'] = self::$_css;
        $pageInfo['js'] = self::$_js;
        $pageInfo['title'] = self::$_title;

        return $pageInfo;
    }

    protected function _getColumnsInfos(array $columns = null)
    {
        if ($columns === null) {
            return null;
        }
        $returnArray = $columns;
        foreach ($columns as $key => $column) {
            if (is_array($column['blocks'])) {
                $returnArray[$key]['blocks'] = $this->_getBlocksInfos($column['blocks']);
                //unset($returnArray[$key]['bloc']);
            } else {
                $returnArray[$key]['rows'] = $this->_getRowsInfos($column['rows']);
            }
        }
        return $returnArray;
    }

    protected function _getBlocksInfos(array $blocks)
    {
        $returnArray = array();
        foreach ($blocks as $block) {
            $returnArray[] = Manager::getService('BlockContent')->getBlockData($block);
        }
        return $returnArray;
    }

    protected function _getRowsInfos(array $rows = null)
    {
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
    public function appendCss($cssFile)
    {
        if (!in_array($cssFile, self::$_css)) {
            self::$_css[] = $cssFile;
        }
    }

    /**
     * clear the included css files list
     */
    public function clearCss()
    {
        self::$_css = array();
    }

    /**
     * Return the list of css files
     * @return array list of URL
     */
    public function getCss()
    {
        return self::$_css;
    }

    /**
     * append a js file to the file list
     * @param string $jsFile URL of the js added
     */
    public function appendJs($jsFile)
    {
        if (!in_array($jsFile, self::$_js)) {
            self::$_js[] = $jsFile;
        }
    }

    /**
     * clear the included js files list
     */
    public function clearJs()
    {
        self::$_js = array();
    }

    /**
     * Return the list of js files
     * @return array list of URL
     */
    public function getJs()
    {
        return self::$_js;
    }

    /**
     * set the page title
     *
     * @param string $pageTitle page title
     */
    public function setPageTitle($pageTitle)
    {
        self::$_title = $pageTitle;
    }

    /**
     * get the page title
     *
     * @return string page title
     */
    public function getPageTitle()
    {
        return self::$_title;
    }

}
