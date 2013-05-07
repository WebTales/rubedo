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
	
	public function deleteAction() {	
		$data = $this->getRequest()->getParam('data');

        if (!is_null($data)) {
            $data = Zend_Json::decode($data);
            if (is_array($data)) {
					$returnArray=$this->_dataService->destroy($data);
            } else {
                $returnArray = array('success' => false, "msg" => 'Not an array');
            }

        } else {
            $returnArray = array('success' => false, "msg" => 'Invalid Data');
        }
        if (!$returnArray['success']) {
            $this->getResponse()->setHttpResponseCode(500);
        }
        $this->_returnJson($returnArray);
	}

	public function wizardCreateAction()
	{
	 $data = $this->getRequest()->getParam('data');
	 $returnArray = array('success' => false, "msg" => 'no data recieved');
	 if (!is_null($data)) {
	 	$insertData = Zend_Json::decode($data);
	 	if ((isset($insertData['builtOnEmptySite']))&&($insertData['builtOnEmptySite'])){
	 		$returnArray=$this->createFromEmpty($insertData);
	 	} else if ((isset($insertData['builtOnModelSiteId']))&&(!empty($insertData['builtOnModelSiteId']))){
	 		$returnArray=$this->createFromModel($insertData);
	 	} else {
	 		$returnArray=array('success' => false, "msg" => 'no site model provided');
	 	}
	 }
	 if (!$returnArray['success']) {
	 	$this->getResponse()->setHttpResponseCode(500);
	 }
	 $this->_returnJson($returnArray);
	}
	
	protected function createFromModel($insertData){
		$model=$this->_dataService->findById($insertData['builtOnModelSiteId']);
		if (empty($model)){
			$returnArray=array('success' => false, "msg" => 'site model not found');
			return($returnArray);
		}
		$masksService=Rubedo\Services\Manager::getService('Masks');
		$pagesService=Rubedo\Services\Manager::getService('Pages');
		$oldIdArray=array();
		$theBigString="";
		
		$modelId=$model['id'];
		$oldIdArray[]=$modelId;
		$theBigString=$theBigString.Zend_Json::encode($model);
		$theBigString=$theBigString."SEntityS";
		$oldMasksArray=$masksService->getList(array(array("property"=>"site", "value"=>$modelId)));
		
		foreach ($oldMasksArray['data'] as $key=>$value){
			$oldIdArray[]=$value['id'];
			$theBigString=$theBigString.Zend_Json::encode($oldMasksArray['data'][$key]);
			$theBigString=$theBigString."SMaskS";
		}
		$theBigString.="SEntityS";
		$oldPagesArray=$pagesService->getList(array(array("property"=>"site", "value"=>$modelId)));
		foreach ($oldPagesArray['data'] as $key=>$value){
			$oldIdArray[]=$value['id'];
			$theBigString=$theBigString.Zend_Json::encode($oldPagesArray['data'][$key]);
			$theBigString=$theBigString."SPageS";
		}
		$newIdArray=array();
		
		foreach ($oldIdArray as $value){
			$MongoId = new MongoId();
			$MongoId = (string) $MongoId;
			$newIdArray[]=$MongoId;
			$theBigString=str_replace($value, $MongoId, $theBigString);
		}
		$explodedBigString=array();
		$explodedBigString=explode("SEntityS", $theBigString);
		
		$newSite=Zend_Json::decode($explodedBigString[0]);
		$newMasksJsonArray=explode("SMaskS", $explodedBigString[1]);
		$newPagesJsonArray=explode("SPageS", $explodedBigString[2]);
		foreach ($insertData as $key=>$value){
			if (!empty($value)){
				$newSite[$key]=$value;
			}
		}
		$newSite['_id']=new MongoId($newSite['id']);
		unset($newSite['id']);
		unset($newSite['version']); 
		$returnArray=$this->_dataService->create($newSite);
		foreach ($newMasksJsonArray as $key=>$value){
			$newMask=Zend_Json::decode($newMasksJsonArray[$key]);
			if (is_array($newMask)){
				$newMask['_id']=new MongoId($newMask['id']);
				unset($newMask['id']);
				unset($newMask['version']);
				$masksService->create($newMask);
			}
		}
		foreach ($newPagesJsonArray as $key=>$value){
			$newPage=Zend_Json::decode($newPagesJsonArray[$key]);
			if (is_array($newPage)){
				$newPage['_id']=new MongoId($newPage['id']);
				unset($newPage['id']);
				unset($newPage['version']);
				$pagesService->create($newPage);
			}
		}
		
		return($returnArray);
	}
	
	protected function createFromEmpty($insertData){
		
		if (is_array($insertData)) {
			$site= $this->_dataService->create($insertData);
		}
		
		
		if($site['success']===true)
		{
			//Make the mask skeleton
			$jsonMask=realpath(APPLICATION_PATH."/../data/default/site/mask.json");
			$maskObj=(Zend_Json::decode(file_get_contents($jsonMask),true));
			$maskObj['site']=$site['data']['id'];
				
			//Home mask
			$homeMask = $maskObj;
				
			$homeFirstColumnId=(string) new MongoId();
			$homeSecondColumnId=(string) new MongoId();
				
			$homeMask['rows'][0]['id']=(string) new MongoId();
			$homeMask['rows'][1]['id']=(string) new MongoId();
			$homeMask['rows'][0]['columns'][0]['id']=$homeFirstColumnId;
			$homeMask['rows'][1]['columns'][0]['id']=$homeSecondColumnId;
				
			$homeMask['mainColumnId']=$homeSecondColumnId;
				
			$homeMask['blocks'][0]['id']=(string) new MongoId();
			$homeMask['blocks'][0]['parentCol']=$homeFirstColumnId;
				
			$homeMask['text'] = "Masque de la page d'accueil";
			$homeMaskCreation=Rubedo\Services\Manager::getService('Masks')->create($homeMask);
				
			//Detail mask
			$detailMask = $maskObj;
				
			$detailFirstColumnId=(string) new MongoId();
			$detailSecondColumnId=(string) new MongoId();
		
			$detailMask['rows'][0]['id']=(string) new MongoId();
			$detailMask['rows'][1]['id']=(string) new MongoId();
			$detailMask['rows'][0]['columns'][0]['id']=$detailFirstColumnId;
			$detailMask['rows'][1]['columns'][0]['id']=$detailSecondColumnId;
		
			$detailMask['mainColumnId']=$detailSecondColumnId;
		
			$detailMask['blocks'][0]['id']=(string) new MongoId();
			$detailMask['blocks'][0]['parentCol']=$detailFirstColumnId;
				
			$detailMask['text'] = "Masque de la page détail";
			$detailMaskCreation=Rubedo\Services\Manager::getService('Masks')->create($detailMask);
				
			//Search mask
			$searchMask = $maskObj;
				
			$searchFirstColumnId=(string) new MongoId();
			$searchSecondColumnId=(string) new MongoId();
				
			$searchMask['rows'][0]['id']=(string) new MongoId();
			$searchMask['rows'][1]['id']=(string) new MongoId();
			$searchMask['rows'][0]['columns'][0]['id']=$searchFirstColumnId;
			$searchMask['rows'][1]['columns'][0]['id']=$searchSecondColumnId;
				
			$searchMask['mainColumnId']=$searchSecondColumnId;
				
			$searchMask['blocks'][0]['id']=(string) new MongoId();
			$searchMask['blocks'][0]['parentCol']=$searchFirstColumnId;
				
			$searchMask['text'] = "Masque de la page de recherche";
			$searchMaskCreation=Rubedo\Services\Manager::getService('Masks')->create($searchMask);
				
			if($homeMaskCreation['success'] && $detailMaskCreation['success'] && $searchMaskCreation['success'])
			{
				/*Create Home Page*/
				$jsonHomePage=realpath(APPLICATION_PATH."/../data/default/site/homePage.json");
				$homePageObj=(Zend_Json::decode(file_get_contents($jsonHomePage),true));
				$homePageObj['site']=$site['data']['id'];
				$homePageObj['maskId']=$homeMaskCreation['data']['id'];
				$homePage=Rubedo\Services\Manager::getService('Pages')->create($homePageObj);
		
				/*Create Single Page*/
				$jsonSinglePage=realpath(APPLICATION_PATH."/../data/default/site/singlePage.json");
				$singlePageObj=(Zend_Json::decode(file_get_contents($jsonSinglePage),true));
				$singlePageObj['site']=$site['data']['id'];
				$singlePageObj['maskId']=$detailMaskCreation['data']['id'];
				$singlePageObj['blocks'][0]['id']=(string) new MongoId();
				$singlePageObj['blocks'][0]['parentCol']=$detailSecondColumnId;
				$page=Rubedo\Services\Manager::getService('Pages')->create($singlePageObj);
		
				/*Create Search Page*/
				$jsonSearchPage=realpath(APPLICATION_PATH."/../data/default/site/searchPage.json");
				$searchPageObj=(Zend_Json::decode(file_get_contents($jsonSearchPage),true));
				$searchPageObj['site']=$site['data']['id'];
				$searchPageObj['maskId']=$searchMaskCreation['data']['id'];
				$searchPageObj['blocks'][0]['id']=(string) new MongoId();
				$searchPageObj['blocks'][0]['parentCol']=$searchSecondColumnId;
				$searchPage=Rubedo\Services\Manager::getService('Pages')->create($searchPageObj);
		
				if($page['success'] && $homePage['success'] && $searchPage['success'])
				{
					$updateMask=$homeMaskCreation['data'];
					$updateMask["blocks"][0]['configBloc']=array("useSearchEngine"=>true,"rootPage"=>$homePage['data']['id'],"searchPage"=>$searchPage['data']['id']);
					$updateMaskReturn=Rubedo\Services\Manager::getService('Masks')->update($updateMask);
					if($updateMaskReturn['success']===true)
					{
						$updateData=$site['data'];
						$updateData['homePage']=$homePage['data']['id'];
						$updateData['defaultSingle']=$page['data']['id'];
						$updateSiteReturn=$this->_dataService->update($updateData);
						if($updateSiteReturn['success']===true)
						{
							$returnArray=$updateSiteReturn;
						}else{
							$returnArray = array('success' => false, "msg" => 'error during site update');
						}
		
					}else{
						$returnArray = array('success' => false, "msg" => 'error during mask update');
					}
						
				}else {
					$returnArray = array('success' => false, "msg" => 'error during pages creation');
				}
			}else{
				$returnArray = array('success' => false, "msg" => 'error during masks creation');
			}
		}else
		{
			$returnArray = array('success' => false, "msg" => 'error during site creation');
		}
		if (!$returnArray['success']) {
			$siteId=$site['data']['id'];
			$resultPages = Rubedo\Services\Manager::getService('Pages')->deleteBySiteId($siteId);
			$resultMasks = Rubedo\Services\Manager::getService('Masks')->deleteBySiteId($siteId);
			if($resultPages['ok'] == 1 && $resultMasks['ok'] == 1){
				$returnArray['delete'] = $this->_dataService->deleteById($siteId);
			}else {
				$returnArray['delete'] = array('success' => false, "msg" => 'Error during the deletion of masks and pages');
			}
		}
		return($returnArray);
	}
	
}