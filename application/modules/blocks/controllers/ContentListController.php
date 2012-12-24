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
    	$this->_dateService = Manager::getService('Date');			
        $this->_dataReader = Manager::getService('Contents');
        $this->_typeReader = Manager::getService('ContentTypes');
<<<<<<< HEAD

        $blockConfig = $this->getRequest()->getParam('block-config');
	
	if(isset($blockConfig['query'])){
		
	$blockConfig=json_decode($blockConfig['query'],true);	
	//Zend_Debug::dump($blockConfig);die();
	foreach ($blockConfig['vocabularies'] as $key => $value) {
	$taxonomyTerms=$value['terms'];
	$taxonomyRule=$value['rule'];
        }
	
	$fieldRules=$blockConfig['fieldRules'];
	}
								
	$output = array();
	$operatorsArray=array('$lt'=>'<','$lte'=>'<=','$gt'=>'>','$gte'=>'>=','$ne'=>'!=','eq'=>'=');
	
        //$filterArray[] = array('property' => 'typeId', 'value' => '999999999999999999999999');
        //$filterArray[] = array('property' => 'typeId', 'value' => '507fea58add92a5108000000');
        //$filterArray[] = array('property' => 'typeId', 'value' => '50c0c8cf9a199dd40f000000');
        //$filterArray[] = array('property' => 'taxonomy.50c0cabc9a199dcc0f000002', 'value' => $taxanomyTerm);
       
        if(isset($taxonomyRule)){					
        	if($taxonomyRule=="some"){
        		$TaxonomyOperator='$in';
        	}elseif($taxonomyRule=="all"){
        		$taxonomyOperator='$all';
        	}
	$filterArray[]=array('operator'=>$TaxonomyOperator ,'property' => 'taxonomy', 'value' => $taxonomyTerms);																				
	}
	if(isset($fieldRules)){
		foreach ($blockConfig['fieldRules'] as $property => $value) {
	  		$ruleOperator=array_search($value['rule'], $operatorsArray);
	  		$filterArray[]=array('operator'=>$ruleOperator,'property'=>$property, 'value'=>$this->_dateService->convertToTimeStamp($value['value']));
       		 }
	}
	
        $filterArray[]=array('property' => 'typeId', 'value' => $blockConfig['contentTypes'][0]);							
	$filterArray[]=array('property' => 'status', 'value' => 'published');
=======
        $blockConfig = $this->getRequest()->getParam('block-config');

        $taxanomyTerm = array_pop($blockConfig['taxonomy']);

        $output = array();

        //$filterArray[] = array('property' => 'typeId', 'value' => '999999999999999999999999');
        //$filterArray[] = array('property' => 'typeId', 'value' => '507fea58add92a5108000000');
        $filterArray[] = array('property' => 'status', 'value' => 'published');

        $filterArray[] = array('property' => 'taxonomy.50c0cabc9a199dcc0f000002', 'value' => $taxanomyTerm);

>>>>>>> 31b572c02de15c2402f0f186929aea97ddfab22f
        $sort = array();
        $sort[] = array('property' => 'text', 'direction' => 'asc');					

        $pageData['limit'] = isset($blockConfig['pageSize']) ? $blockConfig['pageSize'] : 6;
        $pageData['currentPage'] = $this->getRequest()->getParam("page", 1);
<<<<<<< HEAD
        $contentArray = $this->_dataReader->getList($filterArray, $sort, (($pageData['currentPage'] - 1) * $pageData['limit']), $pageData['limit']);
=======

        $contentArray = $this->_dataReader->getList($filterArray, $sort, (($pageData['currentPage'] - 1) * $pageData['limit']), $pageData['limit']);

>>>>>>> 31b572c02de15c2402f0f186929aea97ddfab22f
        $nbItems = $contentArray["count"];
        if ($nbItems > 0) {
            $pageData['nbPages'] = (int)ceil(($nbItems) / $pageData['limit']);
            $pageData['limitPage'] = min(array($pageData['nbPages'],3));

            $typeArray = $this->_typeReader->getList();
            $contentTypeArray = array();
            foreach ($typeArray['data'] as $dataType) {
                $contentTypeArray[(string)$dataType['id']] = "root/blocks/shortsingle/" . preg_replace('#[^a-zA-Z]#', '', $dataType['type']) . ".html.twig";
            }

            foreach ($contentArray['data'] as $vignette) {
                $fields = $vignette['fields'];
                $fields['title'] = $fields['text'];
                unset($fields['text']);
                $fields['id'] = (string)$vignette['id'];
                $fields['type'] = $contentTypeArray[(string)$vignette['typeId']];
                $data[] = $fields;
            }

            $output["data"] = $data;
            $output["page"] = $pageData;
        }

        if (isset($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/contentlist.html.twig");

        }

        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }

}
