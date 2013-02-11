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

require_once('DataAccessController.php');
Use Rubedo\Controller\Action;
/**
 * Controller providing CRUD API for the Pages JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_PagesController extends Backoffice_DataAccessController
{
	/**
	 * Array with the read only actions
	 */
	protected $_readOnlyAction = array('index', 'find-one', 'read-child', 'tree', 'clear-orphan-pages','count-orphan-pages','model','get-content-list');
	
    public function init(){
		parent::init();
		
		// init the data access service
		$this -> _dataService = Rubedo\Services\Manager::getService('Pages');
	}
	
	/**
	 * Clear orphan terms in the collection
	 * 
	 * @return array Result of the request
	 */
	public function clearOrphanPagesAction() {
   		$result = $this->_dataService->clearOrphanPages();
		
		$this->_returnJson($result);
   	}
	
	public function countOrphanPagesAction() {
   		$result = $this->_dataService->countOrphanPages();
		
		$this->_returnJson($result);
   	}
	public function getContentListAction()
	{
		$returnArray=array();
		$total=0;
		$contentArray=array();
		
		$data=$this->getRequest()->getParams();
		$params["pagination"]=array("page"=>$data['page'],"start"=>$data["start"],"limit"=>$data["limit"]);
		$page=$this->_dataService->findById($data['id']);
		$pageBlocks=$page['blocks'];
		foreach($pageBlocks as $block)
		{
			 switch ($block['bType']) {
            case 'Carrousel':
                $controller = 'carrousel';
                break;
            case 'Liste de Contenus':
                $controller = 'content-list';
				break;
			case 'Détail de contenu':
                $controller = 'content-single';
					break;
			 }
			$params["block"]=$block['configBloc'];
			$response=Action::getInstance()->action('get-contents',$controller, 'blocks', $params);
		
		  $contentArray[]=$response->getBody();
		}
	
		if(!empty($contentArray)){
		foreach($contentArray as $key=>$content)
		{
			$content=Zend_Json::decode($content);
			if($content["success"]==true)
			{
			$total=$total+$content["total"];
			}else{
				unset($contentArray[$key]);
			}
		}
		$returnArray["total"]=$total;
		$returnArray["data"]=$contentArray;
		}else{
			$returnArray=array("success"=>false,"msg"=>"No contents found");
		}
		
		$this->_returnJson($returnArray);
	}
	
}