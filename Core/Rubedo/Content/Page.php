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
                $this->_pageInfo['blocks'] = array( 
                									array('Module' => 'NavBar', 'Input' => null, 'Output' => 'navbar_content'),
                									array('Module' => 'HeadLine', 'Input' => null, 'Output' => 'headline_content'), 
                									array('Module' => 'ContentList', 'Input' => null, 'Output' => 'contentlist_content'), 
                									array('Module' => 'Carrousel', 'Input' => null, 'Output' => 'carousel_content'));
				break;
        }

        return $this->_pageInfo;
    }

}
