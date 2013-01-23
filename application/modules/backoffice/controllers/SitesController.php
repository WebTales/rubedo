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
 * Controller providing CRUD API for the sitesController JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_SitesController extends Backoffice_DataAccessController
{
    public function init(){
		parent::init();
		
		// init the data access service
		$this -> _dataService = Rubedo\Services\Manager::getService('Sites');
	}
	
	public function wizardCreateAction()
	{
	 $data = $this->getRequest()->getParam('data');

        if (!is_null($data)) {
            $insertData = Zend_Json::decode($data);
            if (is_array($insertData)) {
                $site= $this->_dataService->create($insertData, true);
            }}
		if($site['success']===true)
		{
			$maskObj=array("site"=>$site['data']['id'],'text'=>"Default-Mask");
			$mask=Rubedo\Services\Manager::getService('Masks')->create($maskObj,true);
			if($mask['success']===true)
			{
				$pageObj=array("site"=>$site['data']['id'],'title'=>"accueil","maskId"=>$mask['data']['id'],"parentId"=>'root');
				$page=Rubedo\Services\Manager::getService('Pages')->create($pageObj,true);
				if($page['success']===true)
				{
					$updateData=$site['data'];
					$updateData['homePage']=$page['data']['id'];
					
					$returnArray=$this->_dataService->update($updateData, true);
				}
			}
		} if (!$returnArray['success']) {
            $this->getResponse()->setHttpResponseCode(500);
        }
        $this->_returnJson($returnArray);
	}
	
}