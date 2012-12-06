<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    yet to be written
 * @version    $Id:
 */

Use Rubedo\Services\Manager;

require_once ('AbstractController.php');
/**
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_ContentListController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction() {
        $this->_dataReader = Manager::getService('Contents');
		$this->_typeReader = Manager::getService('ContentTypes');

        $output = array();
		
		//$filterArray[] = array('property' => 'typeId', 'value' => '999999999999999999999999');
		$filterArray[] = array('property' => 'typeId', 'value' => '507fea58add92a5108000000');
        $filterArray[] = array('property' => 'status', 'value' => 'published');
		
        $sort = array();
        $sort[] = array('property'=>'text', 'direction' => 'asc' );
			
            $pageData['limit']=6;
		   $pageData['currentPage']=$this->getRequest()->getParam("page",1);
				
			$contentArray = $this->_dataReader->getList($filterArray,$sort,(($pageData['currentPage']-1)* $pageData['limit']), $pageData['limit']);
			$nbItems=$contentArray["count"];
			$pageData['nbPages']=(int)ceil(($nbItems)/$pageData['limit']);
            $pageData['limitPage']=$pageData['currentPage']+9;
			
            $typeArray=$this-> _typeReader->getList();
            $contentTypeArray=array();
            foreach ($typeArray['data'] as $dataType) {
			$contentTypeArray[(string)$dataType['id']]="root/blocks/shortsingle/".preg_replace('#[^a-zA-Z]#', '', $dataType['type']).".html";
		}	
			
        foreach ($contentArray['data'] as $vignette) {
            $fields = $vignette['fields'];
            $fields['title'] = $fields['text'];
            unset($fields['text']);
            $fields['id'] = (string)$vignette['id'];
			$fields['type']=$contentTypeArray[(string)$vignette['typeId']];
            $data[] = $fields;
        }
		
        $output["data"] = $data;
		$output["page"]=$pageData;
	//Zend_Debug::dump($output);die();
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/contentlist.html");

        $css = array('/css/rubedo.css', '/css/bootstrap-responsive.css');
        $js = array("/js/jquery.js", "/js/bootstrap-transition.js", "/js/bootstrap-alert.js", "/js/bootstrap-modal.js", "/js/bootstrap-dropdown.js", "/js/bootstrap-scrollspy.js", "/js/bootstrap-tab.js", "/js/bootstrap-tooltip.js", "/js/bootstrap-popover.js", "/js/bootstrap-button.js", "/js/bootstrap-collapse.js", "/js/bootstrap-carousel.js", "/js/bootstrap-typeahead.js", );

        $this->_sendResponse($output, $template, $css, $js);
    }

}
