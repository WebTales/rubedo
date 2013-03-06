<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
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
class Blocks_SearchController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        
        // get search parameters
        $params = $this->getRequest()->getParams();
        $params['pagesize'] = $this->getParam('pagesize', 10);
        $params['pager'] = $this->getParam('pager',0);
        
        if($params['constrainToSite']){
            $site = $this->getRequest()->getParam('site');
            $params['Navigation'][]=$site['text'];
            $serverParams['Navigation'][]=$site['text'];
        }
		
        
        $query = Manager::getService('ElasticDataSearch');
        $query->init();
        
        $results = $query->search($params);
        
        // Pagination
        
        if ($params['pagesize'] != "all") {
            $pagecount = intval( $results['total'] / $params['pagesize']+1);
        } else {
            $pagecount = 1;
        }
		
		$results['current']=$params['pager'];
        $results['pagecount'] = $pagecount;
		$results['limit']=min(array(
                $pagecount-1,
                10
            ));
		
		$results['displayTitle']=$this->getParam('displayTitle');
		$results['blockTitle']=$this->getParam('blockTitle');
		
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/search.html.twig");
        
        $css = array();
        $js = array();
        
        $this->_sendResponse($results, $template, $css, $js);
    }
}
