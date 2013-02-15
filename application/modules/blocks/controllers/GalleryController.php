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
        $this->_dataReader = Manager::getService('Contents');
        $isDraft = Zend_Registry::get('draft');
		$this->_queryReader=Manager::getService('Queries');
		/*
		 * Get queryId, blockConfig and Datalist
		 */
		$blockConfig = $this->getRequest()->getParam('block-config');
		//Zend_Debug::dump($blockConfig);die();
		$blockConfig["size"]=$blockConfig["pageSize"];
		$blockConfig["pageSize"]=$blockConfig["pageSize"]*2;
		$query=parent::getQuery($blockConfig['query']);
		$contentArray=parent::getContentList($this->setFilters($query), $this->setPaginationValues($blockConfig)); 
		$data = array();
        foreach ($contentArray['data'] as $vignette) {
            $fields = $vignette['fields'];
			
			if(isset($vignette['taxonomy']) && $vignette['taxonomy']!=array())
			{
			$terms = array_pop($vignette['taxonomy']);
			$termsArray = array();
			foreach ($terms as $term) {
				if($term=='50c0caeb9a199d1e11000001'){
					continue;
				}
				$termsArray[] = Manager::getService('TaxonomyTerms')->getTerm($term);
			}
			$fields['terms']=$termsArray;
			}
			
            $fields['title'] = $fields['text'];
			$fields['image']=$fields['Nouveau_champ_Champ DAM'];
			unset($fields['Nouveau_champ_Champ DAM']);
            unset($fields['text']);
            $fields['id'] = (string)$vignette['id'];
            $data[] = $fields;
        }
        $output["items"] = $data;
		$output["count"]=count($data);
		$output["image"]["width"]=$blockConfig['imageThumbnailWidth'];
		$output["image"]["height"]=$blockConfig['imageThumbnailHeight'];
		$output["pageSize"]=$blockConfig['size'];
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/gallery.html.twig");
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
		
    }
	public function getContentsAction()
	{
		$this->_dataReader=Manager::getService('Contents');
		$data=$this->getRequest()->getParams();
		if(isset($data['block']['query']))
		{
		$query=$this->getQuery($data['block']['query']);
		$filters=$this->setFilters($query);
		$contentList=$this->_dataReader->getOnlineList($filters['filter'],$filters["sort"],(($data['pagination']['page']-1)*$data['pagination']['limit']),intval($data['pagination']['limit']));
		if($contentList["count"]>0)
		{
		foreach($contentList['data'] as $content)
		{
			$returnArray[]=array('title'=>$content['text'],'id'=>$content['id']);
		}
		$returnArray['total']=count($returnArray);
		$returnArray["success"]=true;
		}else{
			$returnArray=array("success"=>false,"msg"=>"No contents found");
		}
		}else{
				$returnArray=array("success"=>false,"msg"=>"No query found");
			}
			$this->getHelper('Layout')->disableLayout();
            $this->getHelper('ViewRenderer')->setNoRender();
            $this->getResponse()->setBody(Zend_Json::encode($returnArray), 'data');
	}

}
