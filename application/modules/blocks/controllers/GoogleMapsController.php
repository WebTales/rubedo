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


require_once ('ContentListController.php');
/**
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_GoogleMapsController extends Blocks_ContentListController
{

	protected $_defaultTemplate = 'googleMaps';

	
	public function indexAction ()
	{
		$output = $this->_getList();
		
		$blockConfig = $this->getRequest()->getParam('block-config');
		$output["blockConfig"]=$blockConfig;
	
		$positionFieldName = $blockConfig['positionField'];
		foreach($output['data'] as &$item){
			$item['jsonLocalisation'] = Zend_Json::encode($item[$positionFieldName]);	
		}
		
		if (isset($blockConfig['displayType'])) {
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
	
				$nbItems = $unorderedContentArray["count"];
			} else {
				// getList
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
	

		
	
		$data = array(
				'data' => $twigVars['data']		);
		$this->_helper->json($data);
	}
}
