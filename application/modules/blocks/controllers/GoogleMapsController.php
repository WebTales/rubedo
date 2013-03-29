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
}
