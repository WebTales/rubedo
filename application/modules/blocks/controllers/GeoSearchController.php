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
class Blocks_GeoSearchController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        
        
        // get search parameters
        $params = $this->getRequest()->getParams();
        $params['pagesize'] = "all";
        $params['pager'] = $this->getParam('pager',0);
        
        if(isset($params['block-config']['constrainToSite']) && $params['block-config']['constrainToSite']){
            $site = $this->getRequest()->getParam('site');
            $siteId = $site['id'];
            $params['navigation'][]=$siteId;
            $serverParams['navigation'][]=$siteId;
        }
        //apply predefined facets
        $facetsToHide=array();
        if(isset($params['block-config']['predefinedFacets'])){
        	$predefParamsArray = \Zend_Json::decode($params['block-config']['predefinedFacets']);
        	foreach ($predefParamsArray as $key => $value){
        		$params[$key] = $value;
        		$facetsToHide[]=$key;
        	}
        }
        
        Rubedo\Elastic\DataSearch::setIsFrontEnd(true);
        
        $query = Manager::getService('ElasticDataSearch');
        $query->init();
        
        $results = $query->search($params,'geo');
        	
        
        $results['currentSite'] = isset($siteId)?$siteId:null;
        if(isset($params['block-config']['constrainToSite']) && $params['block-config']['constrainToSite']==true){
            $results['constrainToSite'] = true;
        }
        // Pagination
        
        if ($params['pagesize'] != "all") {
            $pagecount = intval( ($results['total']-1) / $params['pagesize']+1);
        } else {
            $pagecount = 1;
        }
        $results['blockConfig']=$params['block-config'];
        $results['facetsToHide']=$facetsToHide;
		$results['current']=$params['pager'];
        $results['pagecount'] = $pagecount;
		$results['limit']=min(array(
                $pagecount-1,
                10
            ));
		
		$results['displayTitle']=$this->getParam('displayTitle');
		$results['blockTitle']=$this->getParam('blockTitle');
		
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/geoSearch.html.twig");
        
        $css = array();
        $js = array();
        
        $this->_sendResponse($results, $template, $css, $js);
    }
    
    protected $_option = 'geo';
    
    
    public function xhrSearchAction () {
    
    	// get params
    	$params = $this->getRequest()->getParams();
    
    	// get option : all, dam, content, geo
    	if (isset($params['option'])) {
    		$this->_option = $params['option'];
    	}
    	$facetsToHide=array();
	    if(isset($params['constrainToSite']) && $params['constrainToSite']==='true'){
	    	    $currentPageId = $this->getRequest()->getParam('current-page');
	    	    $currentPage = Rubedo\Services\Manager::getService('Pages')->findById($currentPageId);
	            $siteId = $currentPage['site'];
	            $params['navigation'][]=$siteId;
	            $facetsToHide[]="navigation";
	            $serverParams['navigation'][]=$siteId; 
	        }
        //apply predefined facets
        if(isset($params['predefinedFacets'])){
        	$predefParamsArray = \Zend_Json::decode($params['predefinedFacets']);
        	foreach ($predefParamsArray as $key => $value){
        		$params[$key] = $value;
        		$facetsToHide[]=$key;
        	}
        }
    	Rubedo\Elastic\DataSearch::setIsFrontEnd(true);
    	
    	$query = Manager::getService('ElasticDataSearch');
    
    	$query->init();
    	$results = $query->search($params,$this->_option,false);
    	$results['facetsToHide']=$facetsToHide;
    	 
    	$activeFacetsTemplate = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
    			"blocks/geoSearch/activeFacets.html.twig");
    	$facetsTemplate = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
    			"blocks/geoSearch/facets.html.twig");
    	
    	$results['activeFacetsHtml'] = Manager::getService('FrontOfficeTemplates')->render($activeFacetsTemplate,
    			$results);
    	$results['facetsHtml'] = Manager::getService('FrontOfficeTemplates')->render($facetsTemplate,
    			$results);
    	$results['success']=true;
    	$results['message']='OK';
    	
    
    	$this->_helper->json($results);
    
    }
    public function xhrGetDetailAction () {
    	
    	//get params
    	$id= $this->getRequest()->getParam('id');
    	$type= $this->getRequest()->getParam('type');
    	
    	$result=array();
    	if ($type=="content") {
    		$entity=Rubedo\Services\Manager::getService('Contents')->findById($id);
    	} else {
    		$entity=Rubedo\Services\Manager::getService('Dam')->findById($id);
    	}
    	if (isset($entity)){
    		if ($type=="content") {
    			$entity['type']=Rubedo\Services\Manager::getService('ContentTypes')->findById($entity['typeId'])['type'];
    		} else {
    			$entity['type']=Rubedo\Services\Manager::getService('ContentTypes')->findById($entity['typeId'])['type'];
    		}
    		$contentOrDamTemplate = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
    				"blocks/geoSearch/contentOrDam.html.twig");
    		$entity['objectType']=$type;
    		$twigVars=array();
    		$twigVars['result']=$entity;
    		$result["data"]=Manager::getService('FrontOfficeTemplates')->render($contentOrDamTemplate,
    			$twigVars);
    		$result['success']=true;
    		$result['message']='OK';
    	} else {
    		$result['success']=false;
    		$result['message']="No entity found";
    	}
    	$this->_helper->json($result);
    	
    }
    
   
}
