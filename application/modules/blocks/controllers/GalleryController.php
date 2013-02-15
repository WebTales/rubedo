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

require_once ('ContentListController.php');
/**
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_GalleryController extends Blocks_ContentListController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction()
    {
        $isDraft = Zend_Registry::get('draft');
		$this->_dataService=Manager::getservice('Dam');
		/*
		 * Get queryId, blockConfig and Datalist
		 */
		$blockConfig = $this->getRequest()->getParam('block-config');
		$currentPage=$this->getRequest()->getParam('page',1);
		$query=Zend_Json::decode($blockConfig["query"]);
		$filter=$this->setFilters($query);
		$limit=(isset($blockConfig["pageSize"]))?$blockConfig['pageSize']:5;
		$mediaArray=$this->_dataService->getList($filter['filter'],$filter['sort'],(($currentPage - 1) * $limit),$limit);
		
		  foreach ($mediaArray['data'] as $media) {
               $fields["image"]=(string)$media['id'];
			  $data[]=$fields;
            }
			
			$output['items']=$data;
			$output['count']=$mediaArray['count'];
			$output['pageSize']=$limit;
			$output["image"]["width"]=$blockConfig['imageThumbnailWidth'];
 			$output["image"]["height"]=$blockConfig['imageThumbnailHeight'];
			$output['currentPage']=$currentPage;
			
		  
		  if (isset($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/gallery.html.twig");
        }
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
	}
	protected function setFilters($query)
	{
		if($query!=null)
		{
           /* Add filters on TypeId and publication */
            $filterArray[] = array(
                'operator' => '$in',
                'property' => 'typeId',
                'value' => $query['DAMTypes']
            );
			/* Add filter on taxonomy */
            foreach ($query['vocabularies'] as $key => $value) {
                if (isset($value['rule'])) {
                    if ($value['rule'] == "some") {
                        $taxOperator = '$in';
                    } elseif ($value['rule'] == "all") {
                        $taxOperator = '$all';
                    } elseif ($value['rule'] == "someRec") {
                        if (count($value['terms']) > 0) {
                            foreach ($value['terms'] as $child) {
                                $terms = $this->_taxonomyReader->fetchAllChildren($child);
                                foreach ($terms as $taxonomyTerms) {
                                    $value['terms'][] = $taxonomyTerms["id"];
                                }
                            }
                        }
                        $taxOperator = '$in';
                    }else{
                    	$taxOperator = '$in';
                    }
                } else {
                   $taxOperator = '$in';
                }
                if (count($value['terms']) > 0) {
                    $filterArray[] = array(
                        'operator' => $taxOperator,
                        'property' => 'taxonomy.' . $key,
                        'value' => $value['terms']
                    );
                }
            }
 /*
                 * Add Sort
                 */
                 if (isset($query['fieldRules'])) {
                 foreach($query['fieldRules'] as $field=>$rule)
				 {
				 	$sort[]=array("property"=>$field,'direction'=>$rule['sort']);
				 }
				 }else {
                    $sort[] = array(
                        'property' => 'id',
                        'direction' => 'DESC'
                    );
                }
			
		}else{
			return array();
		}
		$returnArray=array("filter"=>$filterArray,"sort"=>$sort);
		return $returnArray;
	}

}
