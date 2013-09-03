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
namespace Rubedo\Backoffice\Controller;

use WebTales\MongoFilters\Filter;
use Rubedo\Services\Manager;
use Zend\Json\Json;

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
class SitesController extends DataAccessController
{

    public function __construct ()
    {
        parent::__construct();
        
        // init the data access service
        $this->_dataService = Manager::getService('Sites');
    }

    public function deleteAction ()
    {
        $data = $this->params()->fromPost('data');
        
        if (! is_null($data)) {
            $data = Json::decode($data,Json::TYPE_ARRAY);
            if (is_array($data)) {
                $returnArray = $this->_dataService->destroy($data);
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'Not an array'
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => 'Invalid Data'
            );
        }
        if (! $returnArray['success']) {
            $this->getResponse()->setStatusCode(500);
        }
        return $this->_returnJson($returnArray);
    }

    public function wizardCreateAction ()
    {
        $data = $this->params()->fromPost('data');
        $returnArray = array(
            'success' => false,
            "msg" => 'no data recieved'
        );
        if (! is_null($data)) {
            $insertData = Json::decode($data,Json::TYPE_ARRAY);
            if ((isset($insertData['builtOnEmptySite'])) && ($insertData['builtOnEmptySite'])) {
                $returnArray = $this->createFromEmpty($insertData);
            } else 
                if ((isset($insertData['builtOnModelSiteId'])) && (! empty($insertData['builtOnModelSiteId']))) {
                    $returnArray = $this->createFromModel($insertData);
                } else {
                    $returnArray = array(
                        'success' => false,
                        "msg" => 'no site model provided'
                    );
                }
        }
        if (! $returnArray['success']) {
            $this->getResponse()->setStatusCode(500);
        }
        return $this->_returnJson($returnArray);
    }
    
    /**
     * @todo move to a service
     * @param unknown $insertData
     * @return multitype:boolean string |unknown
     */
    protected function createFromModel ($insertData)
    {
        $model = $this->_dataService->findById($insertData['builtOnModelSiteId']);
        if (empty($model)) {
            $returnArray = array(
                'success' => false,
                "msg" => 'site model not found'
            );
            return ($returnArray);
        }
        $masksService = Manager::getService('Masks');
        $pagesService = Manager::getService('Pages');
        $queriesService = Manager::getService('Queries');
        $contentsService = Manager::getService('Contents');
        $oldIdArray = array();
        $theBigString = "";
        
        $modelId = $model['id'];
        $oldIdArray[] = $modelId;
        $theBigString = $theBigString . Json::encode($model);
        $theBigString = $theBigString . "SEntityS";
        
        $oldMaskFilters = Filter::factory('value')->setName('site')->setValue($modelId);
        $oldMasksArray = $masksService->getList($oldMaskFilters);
        
        foreach ($oldMasksArray['data'] as $key => $value) {
            if (isset($value['blocks'])&&is_array($value['blocks'])){
            foreach($value['blocks'] as $subkey => $someBlock){
                    unset($oldMasksArray['data'][$key]['blocks'][$subkey]['id']);
                    unset($oldMasksArray['data'][$key]['blocks'][$subkey]['_id']);
                    $oldMasksArray['data'][$key]['blocks'][$subkey]['id']=(string) new \MongoId();
                }
            }
            $oldIdArray[] = $value['id'];
            $theBigString = $theBigString . Json::encode($oldMasksArray['data'][$key]);
            $theBigString = $theBigString . "SMaskS";
        }
        $theBigString .= "SEntityS";
        $oldPagesArray = $pagesService->getList($oldMaskFilters);
        foreach ($oldPagesArray['data'] as $key => $value) {
            if (isset($value['blocks'])&&is_array($value['blocks'])){
                foreach($value['blocks'] as $subkey => $someBlock){
                    unset($oldPagesArray['data'][$key]['blocks'][$subkey]['id']);
                    unset($oldPagesArray['data'][$key]['blocks'][$subkey]['_id']);
                    $oldPagesArray['data'][$key]['blocks'][$subkey]['id']=(string)new \MongoId();
                }
            }
            $oldIdArray[] = $value['id'];
            $theBigString = $theBigString . Json::encode($oldPagesArray['data'][$key]);
            $theBigString = $theBigString . "SPageS";
        }
        $newIdArray = array();
        
        //duplicate simple or manual queries and system contents
        
        $systemContentTypesFilter = Filter::Factory('Value')->setName('system')->setValue(true);
        $systemContentTypesLIst = Manager::getService('ContentTypes')->getList($systemContentTypesFilter);
        
        $systemTypesArray = array();
        foreach ($systemContentTypesLIst['data'] as $contentTypes){
            $systemTypesArray[]=$contentTypes['id'];
        }
        
        $systemContentFilter = Filter::Factory('In')->setName('typeID')->setValue($systemTypesArray);
        $systemContentList = $contentsService->getList($systemContentFilter);
        
        $queriesFilter=Filter::Factory('In')->setName('type')->setValue(array("simple","manual"));
        $queriesList=$queriesService->getList($queriesFilter);
        foreach ($queriesList['data'] as $someQuery){
            if(strpos($theBigString, $someQuery['id'])){
                $MongoId = new \MongoId();
                $MongoIdString = (string) $MongoId;
                $theBigString = str_replace($someQuery['id'], $MongoIdString, $theBigString);
                $someQuery['_id'] = $MongoId;
                unset($someQuery['id']);
                unset($someQuery['version']);
                $queriesService->create($someQuery);
            }
        }
        foreach ($systemContentList['data'] as $systemContent){
            if(strpos($theBigString, $systemContent['id'])){
                $MongoId = new \MongoId();
                $MongoIdString = (string) $MongoId;
                $theBigString = str_replace($systemContent['id'], $MongoIdString, $theBigString);
                $systemContent['_id'] = $MongoId;
                unset($systemContent['id']);
                unset($systemContent['version']);
                $contentsService->create($systemContent);
            }
        }
        
        
        foreach ($oldIdArray as $value) {
            $MongoId = new \MongoId();
            $MongoId = (string) $MongoId;
            $newIdArray[] = $MongoId;
            $theBigString = str_replace($value, $MongoId, $theBigString);
        }
        $explodedBigString = array();
        $explodedBigString = explode("SEntityS", $theBigString);
        
        $newSite = Json::decode($explodedBigString[0],Json::TYPE_ARRAY);
        $newMasksJsonArray = explode("SMaskS", $explodedBigString[1]);
        $newPagesJsonArray = explode("SPageS", $explodedBigString[2]);
        foreach ($insertData as $key => $value) {
            if (! empty($value)) {
                $newSite[$key] = $value;
            }
        }
        $newSite['_id'] = new \MongoId($newSite['id']);
        unset($newSite['id']);
        unset($newSite['version']);
        $returnArray = $this->_dataService->create($newSite);
        foreach ($newMasksJsonArray as $key => $value) {
            $newMask = Json::decode($newMasksJsonArray[$key],Json::TYPE_ARRAY);
            if (is_array($newMask)) {
                $newMask['_id'] = new \MongoId($newMask['id']);
                unset($newMask['id']);
                unset($newMask['version']);
                $masksService->create($newMask);
            }
        }
        foreach ($newPagesJsonArray as $key => $value) {
            $newPage = Json::decode($newPagesJsonArray[$key],Json::TYPE_ARRAY);
            if (is_array($newPage)) {
                $newPage['_id'] = new \MongoId($newPage['id']);
                unset($newPage['id']);
                unset($newPage['version']);
                $pagesService->create($newPage);
            }
        }
        
        return ($returnArray);
    }

    /**
     * @todo move to a service
     * @param unknown $insertData
     * @return multitype:boolean string multitype:boolean string  NULL
     */
    protected function createFromEmpty ($insertData)
    {
        $this->translateService = Manager::getService('Translate');
        
        $this->locale = $insertData['defaultLanguage'];
        $insertData['nativeLanguage'] = $this->locale;
        
        if (is_array($insertData)) {
            $site = $this->_dataService->create($insertData);
        }
        
        if ($site['success'] === true) {
            // Make the mask skeleton
            $jsonMask = realpath(APPLICATION_PATH . "/data/default/site/mask.json");
            $maskObj = Json::decode(file_get_contents($jsonMask), Json::TYPE_ARRAY);
            $maskObj['site'] = $site['data']['id'];
            $maskObj['nativeLanguage']=$this->locale;
            
            // Home mask
            $homeMaskCreation = $this->createMask($maskObj,'NewSite.homepage.title',1);
            

            // Detail mask
            $detailSecondColumnId = (string) new \MongoId();
            $detailMaskCreation = $this->createMask($maskObj,'NewSite.single.title',1,$detailSecondColumnId);
            
            // Search mask
            $searchColumnId = (string) new \MongoId();
            $searchMaskCreation = $this->createMask($maskObj,'NewSite.search.title',1,$searchColumnId);
            
            
            if ($homeMaskCreation['success'] && $detailMaskCreation['success'] && $searchMaskCreation['success']) {
                /* Create Home Page */
                $jsonHomePage = realpath(APPLICATION_PATH . "/data/default/site/homePage.json");
                $itemJson = file_get_contents($jsonHomePage);
                $itemJson = preg_replace_callback('/###(.*)###/U', array(
                    $this,
                    'replaceWithTranslation'
                ), $itemJson);
                $homePageObj = Json::decode($itemJson, Json::TYPE_ARRAY);
                $homePageObj['site'] = $site['data']['id'];
                $homePageObj['maskId'] = $homeMaskCreation['data']['id'];
                $homePageObj['nativeLanguage']=$site['data']['nativeLanguage'];
                $homePageObj['i18n']=array($site['data']['nativeLanguage']=>array(
                    "text"=>$homePageObj['text'],
                    "title"=>$homePageObj['title'],
                    "description"=>$homePageObj['description']
                
                ));
                $homePage = Manager::getService('Pages')->create($homePageObj);
                
                /* Create Single Page */
                $jsonSinglePage = realpath(APPLICATION_PATH . "/data/default/site/singlePage.json");
                $itemJson = file_get_contents($jsonSinglePage);
                $itemJson = preg_replace_callback('/###(.*)###/U', array(
                    $this,
                    'replaceWithTranslation'
                ), $itemJson);
                $singlePageObj = Json::decode($itemJson,Json::TYPE_ARRAY);
                $singlePageObj['site'] = $site['data']['id'];
                $singlePageObj['maskId'] = $detailMaskCreation['data']['id'];
                $singlePageObj['nativeLanguage']=$site['data']['nativeLanguage'];
                $singlePageObj['i18n']=array($site['data']['nativeLanguage']=>array(
                    "text"=>$singlePageObj['text'],
                    "title"=>$singlePageObj['title'],
                    "description"=>$singlePageObj['description']
                
                ));
                $singlePageObj['blocks'][0]['id'] = (string) new \MongoId();
                $singlePageObj['blocks'][0]['parentCol'] = $detailSecondColumnId;
                $page = Manager::getService('Pages')->create($singlePageObj);
                
                /* Create Search Page */
                $jsonSearchPage = realpath(APPLICATION_PATH . "/data/default/site/searchPage.json");
                $itemJson = file_get_contents($jsonSearchPage);
                $itemJson = preg_replace_callback('/###(.*)###/U', array(
                    $this,
                    'replaceWithTranslation'
                ), $itemJson);
                $searchPageObj = Json::decode($itemJson,Json::TYPE_ARRAY);
                $searchPageObj['nativeLanguage']=$site['data']['nativeLanguage'];
                $searchPageObj['i18n']=array($site['data']['nativeLanguage']=>array(
                    "text"=>$searchPageObj['text'],
                    "title"=>$searchPageObj['title'],
                    "description"=>$searchPageObj['description']
                    
                ));
                $searchPageObj['site'] = $site['data']['id'];
                $searchPageObj['maskId'] = $searchMaskCreation['data']['id'];
                $searchPageObj['blocks'][0]['id'] = (string) new \MongoId();
                $searchPageObj['blocks'][0]['parentCol'] = $searchColumnId;
                $searchPage = Manager::getService('Pages')->create($searchPageObj);
                
                if ($page['success'] && $homePage['success'] && $searchPage['success']) {

                    $updateMaskReturn = $this->updateMenuForMask($homeMaskCreation['data'],$homePage['data']['id'],$searchPage['data']['id']);
                    $updateMaskReturn = $this->updateMenuForMask($searchMaskCreation['data'],$homePage['data']['id'],$searchPage['data']['id']);
                    $updateMaskReturn = $this->updateMenuForMask($detailMaskCreation['data'],$homePage['data']['id'],$searchPage['data']['id']);
                    
                    //add 1 to 3 colmumns masks
                    for($i=1;$i<=3;$i++){
                        $mask = $this->createMask($maskObj,'NewSite.'.$i.'col.title',$i);
                        $this->updateMenuForMask($mask['data'],$homePage['data']['id'],$searchPage['data']['id']);
                    }
                    
                    if ($updateMaskReturn['success'] === true) {
                        $updateData = $site['data'];
                        $updateData['homePage'] = $homePage['data']['id'];
                        $updateData['defaultSingle'] = $page['data']['id'];
                        $updateSiteReturn = $this->_dataService->update($updateData);
                        if ($updateSiteReturn['success'] === true) {
                            $returnArray = $updateSiteReturn;
                        } else {
                            $returnArray = array(
                                'success' => false,
                                "msg" => 'error during site update'
                            );
                        }
                    } else {
                        $returnArray = array(
                            'success' => false,
                            "msg" => 'error during mask update'
                        );
                    }
                } else {
                    $returnArray = array(
                        'success' => false,
                        "msg" => 'error during pages creation'
                    );
                }
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'error during masks creation'
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => 'error during site creation'
            );
        }
        if (! $returnArray['success']) {
            $siteId = $site['data']['id'];
            $resultPages = Manager::getService('Pages')->deleteBySiteId($siteId);
            $resultMasks = Manager::getService('Masks')->deleteBySiteId($siteId);
            if ($resultPages['ok'] == 1 && $resultMasks['ok'] == 1) {
                $returnArray['delete'] = $this->_dataService->deleteById($siteId);
            } else {
                $returnArray['delete'] = array(
                    'success' => false,
                    "msg" => 'Error during the deletion of masks and pages'
                );
            }
        }
        return ($returnArray);
    }
    
    /**
     * @todo move to a service
     * @param unknown $matches
     * @throws \Rubedo\Exceptions\Server
     * @return unknown
     */
    protected function replaceWithTranslation($matches){
       
        if($matches[1]=='Locale'){
            return $this->locale;
        }
        $result = $this->translateService->getTranslation($matches[1],$this->locale);
        if(empty($result)){
            throw new \Rubedo\Exceptions\Server('can\'t translate :'.$matches[1]);
        }
        return $result;
    }
    
    /**
     * @todo move to a service
     * @param unknown $maskObj
     * @param unknown $name
     * @param number $numcol
     * @param string $forceCol
     * @return unknown
     */
    protected function createMask($maskObj,$name,$numcol=1,$forceCol = null){
        // Search mask
        $mask = $maskObj;
        
        $searchFirstColumnId = (string) new \MongoId();
        $searchSecondColumnId = (string) new \MongoId();
        
        $mask['rows'][0]['id'] = (string) new \MongoId();
        $mask['rows'][1]['id'] = (string) new \MongoId();
        $mask['rows'][0]['columns'][0]['id'] = $searchFirstColumnId;
        
        $tempCol = $mask['rows'][1]['columns'][0];
        $tempCol['span'] = floor(12/$numcol);
        unset($mask['rows'][1]['columns']);
        for($i = 1; $i <= $numcol; $i++){
            $mask['rows'][1]['columns'][$i-1]=$tempCol;
            $mask['rows'][1]['columns'][$i-1]['id'] = (string) new \MongoId();
            if($forceCol && $i == 1){
                $mask['rows'][1]['columns'][$i-1]['id'] = $forceCol;
            }
            if($i <=2){
                $mask['mainColumnId']=$mask['rows'][1]['columns'][$i-1]['id'];
            }
        }
                
        $mask['blocks'][0]['id'] = (string) new \MongoId();
        $mask['blocks'][0]['parentCol'] = $searchFirstColumnId;
        
        $mask['i18n'][$this->locale]['text'] = $mask['text'] = $this->translateService->getTranslation($name,$this->locale);
        $maskCreation = Manager::getService('Masks')->create($mask);
        if($maskCreation['success']){
            return $maskCreation;
        }
    }
    
    protected function updateMenuForMask($mask,$homePage,$searchPage){
        $mask["blocks"][0]['configBloc'] = array(
            "useSearchEngine" => true,
            "rootPage" => $homePage,
            "searchPage" => $searchPage
        );
        $updateMaskReturn = Manager::getService('Masks')->update($mask);
        
        return $updateMaskReturn;
    }
}