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
 
/**
 * Controller providing CRUD API for the field types JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_ContentTypesController extends Backoffice_DataAccessController
{
    /**
	 * Array with the read only actions
	 */
	protected $_readOnlyAction = array('index', 'find-one', 'read-child', 'tree','model', 'get-readable-content-types','is-used','is-changeable');	
		
    public function init(){
		parent::init();
		
		// init the data access service
		$this -> _dataService = Rubedo\Services\Manager::getService('ContentTypes');
	}
	
	public function getReadableContentTypesAction() {
		return $this->_returnJson($this->_dataService->getReadableContentTypes());
	}
	public function isUsedAction()
	{
		$id = $this->getRequest()->getParam('id');
		$listResult=Rubedo\Services\Manager::getService('Contents')->getListByTypeId($id);
		$resultArray = (is_array($listResult) && $listResult['count']>0) ? array("used"=>true) : array("used"=>false);
		$this->_returnJson($resultArray);
	}
	public function isChangeableAction()
	{
		$newData=$this->getRequest()->getParam('data');
		$newData=Zend_Json::decode($newData);
		$newData['fields']=$newData['champs'];
		unset ($newData['champs']);
		$data=$this->_dataService->findById($newData['id']);
		
		$listResult=Rubedo\Services\Manager::getService('Contents')->getListByTypeId($newData['id']);
		
		if(is_array($listResult) && $listResult['count']>0)
		{
			$resultArray=array("modify"=>"ok");
		}
		else 
		{
			
			if(count($data)>count($newData)){
				$greaterData=$data;
				$tinierData=$newData;
			}elseif(count($data)<count($newData)){
				$greaterData=$newData;
				$tinierData=$data;
			}
			else{
				$greaterData=$data;
				$tinierData=$newData;
			}
			$unauthorized=0;
			$authorizedModif=array("first"=>array('506441f8c648043912000017','506441f8c648043912000018','506441f8c648043912000019'),
			"second"=>array('506441f8c64804391200001d','506441f8c64804391200001e','506441f8c64804391200001f')
			);
			foreach($greaterData['fields'] as $field)
			{
				foreach($tinierData['fields'] as $newfield)
				{
					if($field['config']['name']==$newfield['config']['name'])
					{
						if($field['protoId']!=$newfield['protoId'])
						{
							if(in_array($field['protoId'],$authorizedModif['first']))
							{
								if(!in_array($newfield['protoId'],$authorizedModif['first']))
								{
									$unauthorized++;
								}
							}elseif(in_array($field['protoId'],$authorizedModif['second']))
							{
								if(!in_array($newfield['protoId'],$authorizedModif['second']))
								{
									$unauthorized++;
								}
							}
							else {
								$unauthorized++;
							}
							
						}
					}
				}
			}
			$resultArray = ($unauthorized!=0) ? array("modify"=>"no") : array("modify"=>"possible");
			
		}
		$this->_returnJson($resultArray);
		
	
		
	}

}