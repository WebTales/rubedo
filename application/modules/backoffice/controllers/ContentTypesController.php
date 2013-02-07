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
	protected $_readOnlyAction = array('index', 'find-one', 'read-child', 'tree','model', 'get-readable-content-types','is-used');	
		
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
		if(is_array($listResult) && $listResult['count']>0)
		{
			$result=array("used"=>true);
		}
		else {
			$result=array("used"=>false);
		}
		$this->_returnJson($result);
	}

}