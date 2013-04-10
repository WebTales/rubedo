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
class Blocks_ContentListController extends Blocks_AbstractController
{

    protected $_defaultTemplate = 'contentlist';
    
    

    public function indexAction ()
    {
        $output = $this->_getList();
        $blockConfig = $this->getRequest()->getParam('block-config');
        $output["blockConfig"]=$blockConfig;
        
        if (isset($blockConfig['displayType']) && !empty($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
                    "blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
                    "blocks/" . $this->_defaultTemplate . ".html.twig");
        }
        $css = array();
        $js = array(
                '/templates/' .
                         Manager::getService('FrontOfficeTemplates')->getFileThemePath(
                                "js/contentList.js")
        );
        $this->_sendResponse($output, $template, $css, $js);
    }

    protected function _getList ()
    {
        // init services
        $this->_dataReader = Manager::getService('Contents');
        $this->_typeReader = Manager::getService('ContentTypes');
        $this->_queryReader = Manager::getService('Queries');
        
        // get params & context
        $blockConfig = $this->getRequest()->getParam('block-config');
        $queryId = $this->getParam('query-id', $blockConfig['query']);
        
        // $queryConfig = $this->getQuery($queryId);
        // $queryType = $queryConfig['type'];
        $output = $this->getAllParams();
        
        // build query
        $filters = $this->_queryReader->getFilterArrayById($queryId);
        if ($filters !== false) {
            $queryType = $filters["queryType"];
            $query = $this->_queryReader->getQueryById($queryId);
            
            if($queryType === "manual" && $query != false && isset($query['query']) && is_array($query['query'])) {
            	$contentOrder = $query['query'];
            	$keyOrder = array();
            	$contentArray = array();
            		
            	// getList
            	$unorderedContentArray = $this->getContentList($filters, $this->setPaginationValues($blockConfig));
            	
            	foreach ($contentOrder as $value){
            		foreach ($unorderedContentArray['data'] as $subKey => $subValue){
            			if ($value === $subValue['id']){
            				$keyOrder[] = $subKey;
            			} 
            		}
            	}
            	
            	foreach ($keyOrder as $key => $value) {
            		$contentArray["data"][] = $unorderedContentArray["data"][$value]; 
            	}
            	
            	$contentArray["page"] = $unorderedContentArray["page"];
            	
            	$nbItems = $unorderedContentArray["count"];
            } else {
	            $contentArray = $this->getContentList($filters, $this->setPaginationValues($blockConfig));
	            $nbItems = $contentArray["count"];
            }
            
        } else {
            $nbItems = 0;
        }
        
        if ($nbItems > 0) {
            $contentArray['page']['nbPages'] = (int) ceil(
                    ($nbItems) / $contentArray['page']['limit']);
            $contentArray['page']['limitPage'] = min(
                    array(
                            $contentArray['page']['nbPages'],
                            10
                    ));
            $typeArray = $this->_typeReader->getList();
            $contentTypeArray = array();
            foreach ($typeArray['data'] as $dataType) {
                /*
                 * $dataType['type']= htmlentities($dataType['type'],
                 * ENT_NOQUOTES, 'utf-8');//Convert special chars to
                 * htmlentities $dataType['type']=
                 * preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#',
                 * '\1', $dataType['type']);//Replace all special char by normal
                 * char $dataType['type']=
                 * preg_replace('#&([A-za-z]{2})(?:lig);#', '\1',
                 * $dataType['type']); // to special char e.g. '&oelig;'
                 */
                
                $path = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
                        "/blocks/shortsingle/" .
                                 preg_replace('#[^a-zA-Z]#', '', 
                                        $dataType['type']) . ".html.twig");
                if (Manager::getService('FrontOfficeTemplates')->templateFileExists(
                        $path)) {
                    $contentTypeArray[(string) $dataType['id']] = $path;
                } else {
                    $contentTypeArray[(string) $dataType['id']] = Manager::getService(
                            'FrontOfficeTemplates')->getFileThemePath(
                            "/blocks/shortsingle/Default.html.twig");
                }
            }
            foreach ($contentArray['data'] as $vignette) {
                $fields = $vignette['fields'];
                $fields['title'] = $fields['text'];
                unset($fields['text']);
                $fields['id'] = (string) $vignette['id'];
                $fields['typeId'] = $vignette['typeId'];
                $fields['type'] = $contentTypeArray[(string) $vignette['typeId']];
                $data[] = $fields;
            }
            $output['blockConfig'] = $blockConfig;
            $output["data"] = $data;
            $output["query"]['type'] = $queryType;
            $output["query"]['id'] = $queryId;
            $output['prefix'] = $this->getRequest()->getParam('prefix');
            $output["page"] = $contentArray['page'];
            
            $defaultLimit = isset($blockConfig['pageSize']) ? $blockConfig['pageSize'] : 6;
            $output['limit'] = $this->getParam('limit', $defaultLimit);
            
            $singlePage = isset($blockConfig['singlePage']) ? $blockConfig['singlePage'] : $this->getParam(
                    'current-page');
            $output['singlePage'] = $this->getParam('single-page', $singlePage);
            $displayType = isset($blockConfig['displayType']) ? $blockConfig['displayType'] : $this->getParam(
                    'displayType', null);
            $output['displayType'] = $displayType;
            
            $output['xhrUrl'] = $this->_helper->url->url(
                    array(
                            'module' => 'blocks',
                            'controller' => 'content-list',
                            'action' => 'xhr-get-items'
                    ), 'default');
        }

        return $output;
    }

    public function xhrGetItemsAction ()
    {
        $twigVars = $this->_getList();
        
        $displayType = $this->getParam('displayType', false);
        if ($displayType) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
                    "blocks/contentList/" . $displayType . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
                    "blocks/contentList/list.html.twig");
        }
        
        $html = Manager::getService('FrontOfficeTemplates')->render($template, 
                $twigVars);
        $pager = Manager::getService('FrontOfficeTemplates')->render(
                Manager::getService('FrontOfficeTemplates')->getFileThemePath(
                        "blocks/contentList/pager.html.twig"), $twigVars);
        
        $data = array(
                'html' => $html,
                'pager' => $pager
        );
        $this->_helper->json($data);
    }
    
    /*
     * @ todo: PHPDoc
     */
    protected function getContentList ($filters, $pageData)
    {
        $filter = array(
                'property' => 'target',
                'operator' => '$in',
                'value' => array(
                        $this->_workspace,
                        'all'
                )
        );
        
        $filters["filter"][] = $filter;
        
        $contentArray = $this->_dataReader->getOnlineList($filters["filter"], 
                $filters["sort"], 
                (($pageData['currentPage'] - 1) * $pageData['limit']), 
                $pageData['limit']);
        $contentArray['page'] = $pageData;
        return $contentArray;
    }

    protected function setPaginationValues ($blockConfig)
    {
        //Zend_Debug::dump($blockConfig);die();
        $defaultLimit = isset($blockConfig['pageSize']) ? $blockConfig['pageSize'] : 6;
        $pageData['limit'] = $this->getParam('limit', $defaultLimit);
        $pageData['currentPage'] = $this->getRequest()->getParam("page", 1);
        return $pageData;
    }

    public function getContentsAction ()
    {
        $this->_dataReader = Manager::getService('Contents');
        $data = $this->getRequest()->getParams();
        if (isset($data['block']['query'])) {
            
            $filters = Manager::getService('Queries')->getFilterArrayById(
                    $data['block']['query']);
            if ($filters !== false) {
                $contentList = $this->_dataReader->getOnlineList(
                        $filters['filter'], $filters["sort"], 
                        (($data['pagination']['page'] - 1) *
                                 $data['pagination']['limit']), 
                                intval($data['pagination']['limit']));
            } else {
                $contentList = array(
                        'count' => 0
                );
            }
            if ($contentList["count"] > 0) {
                foreach ($contentList['data'] as $content) {
                    $returnArray[] = array(
                            'text' => $content['text'],
                            'id' => $content['id']
                    );
                }
                $returnArray['total'] = count($returnArray);
                $returnArray["success"] = true;
            } else {
                $returnArray = array(
                        "success" => false,
                        "msg" => "No contents found"
                );
            }
        } else {
            $returnArray = array(
                    "success" => false,
                    "msg" => "No query found"
            );
        }
        
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setBody(Zend_Json::encode($returnArray));
    }
}
