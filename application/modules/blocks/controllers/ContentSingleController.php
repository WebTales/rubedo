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
class Blocks_ContentSingleController extends Blocks_AbstractController
{

	protected $_defaultTemplate = 'singlecontent';
	
    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $this->_dataReader = Manager::getService('Contents');
        $this->_typeReader = Manager::getService('ContentTypes');
        
        $blockConfig = $this->getRequest()->getParam('block-config');
        $output["blockConfig"]=$blockConfig;
        
        if (isset($blockConfig['displayType'])) {
        	$template = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
        			"blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
        	$template = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
        			"blocks/" . $this->_defaultTemplate . ".html.twig");
        }
        
        $mongoId = $this->getRequest()->getParam('content-id');
        
        if (isset($mongoId) && $mongoId != 0) {
            $content = $this->_dataReader->findById($mongoId, true, false);
            $data = $content['fields'];
            $termsArray = array();
            if (isset($content['taxonomy'])) {
                if (is_array($content['taxonomy'])) {
                    foreach ($content['taxonomy'] as $key => $terms) {
                        if($key == 'navigation'){
                            continue;
                        }
                        foreach ($terms as $term) {
                            $termsArray[] = Manager::getService('TaxonomyTerms')->getTerm($term);
                        }
                    }
                }
            }
            $data['terms'] = $termsArray;
            $data["id"] = $mongoId;
            
            $type = $this->_typeReader->findById($content['typeId'], true, false);
            $cTypeArray = array();
            foreach ($type["fields"] as $value) {
                
                $cTypeArray[$value['config']['name']] = $value["cType"];
            }
            $templateName = preg_replace('#[^a-zA-Z]#', '', $type["type"]);
            $templateName .= ".html.twig";
            $output = $this->getAllParams();
            $output["data"] = $data;
            $output["type"] = $cTypeArray;
            
        } else {
            $output = array();
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/single/noContent.html.twig");
            $js = array();
        }
        
        $css = array();
        $this->_sendResponse($output, $template, $css, $js);
    }

    public function getContentsAction ()
    {
        $this->_dataReader = Manager::getService('Contents');
        $returnArray = array();
        $data = $this->getRequest()->getParams();
        if (isset($data['block']['contentId'])) {
            $content = $this->_dataReader->findById($data['block']['contentId']);
            $returnArray[] = array(
                'text' => $content['text'],
                'id' => $content['id']
            );
            $returnArray['total'] = count($returnArray);
            $returnArray["success"] = true;
        } else {
            $returnArray = array(
                "success" => false,
                "msg" => "No query found"
            );
        }
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setBody(Zend_Json::encode($returnArray), 'data');
    }
}
