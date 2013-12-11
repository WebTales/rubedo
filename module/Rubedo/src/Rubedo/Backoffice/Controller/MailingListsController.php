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

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\View\Model\JsonModel;
use Zend\Debug\Debug;
use Zend\Json\Json;

/**
 * Controller providing CRUD API for the mailing lists JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class MailingListsController extends DataAccessController
{

    public function __construct()
    {
        parent::__construct();
        
        // init the data access service
        $this->_dataService = Manager::getService('MailingList');
    }

    public function subscribeUsersAction(){
        $userEmailArray=$this->params()->fromPost("userEmailArray","[ ]");
        $userEmailArray=Json::decode($userEmailArray, Json::TYPE_ARRAY);
        $mlId=$this->params()->fromPost("mlId",null);
        $result=array();
        $result['success']=true;
        foreach ($userEmailArray as $userEmail){
            $resultInter=$this->_dataService->subscribe($mlId,$userEmail);
            $result['success']==$result['success']&&$resultInter['success'];
        }
        return $this->_returnJson($result);
    }

    public function unsubscribeUsersAction(){
        $userEmailArray=$this->params()->fromPost("userEmailArray","[ ]");
        $userEmailArray=Json::decode($userEmailArray, Json::TYPE_ARRAY);
        $mlId=$this->params()->fromPost("mlId",null);
        $result=array();
        $result['success']=true;
        foreach ($userEmailArray as $userEmail){
            $resultInter=$this->_dataService->unSubscribe($mlId,$userEmail);
            $result['success']==$result['success']&&$resultInter;
        }
        return $this->_returnJson($result);
    }

    public function getUsersAction(){
        $usersService = Manager::getService('Users');
        $params = $this->params()->fromQuery();
        $sortJson =$this->params()->fromQuery("sort",null);
        if (isset($sortJson)) {
            $sort = Json::decode($sortJson, Json::TYPE_ARRAY);
        } else {
            $sort = null;
        }
        $filters =  Filter::factory()->addFilter(Filter::factory('Value')->setName('mailingLists.'.$params['id'].'.status') ->setValue(true));
        $results=$usersService->getList($filters, $sort, (($params['page']-1) * $params['limit']), intval($params['limit']));
        return $this->_returnJson($results);
    }
    
    public function exportUsersAction(){
        $usersService = Manager::getService('Users');
        $fileName = 'export_csv_' . date('Ymd') . '.csv';
        $filePath = sys_get_temp_dir() . '/' . $fileName;
        $csvResource = fopen($filePath, 'w+');
        $params = $this->params()->fromQuery();
        $filters =  Filter::factory()->addFilter(Filter::factory('Value')->setName('mailingLists.'.$params['id'].'.status') ->setValue(true));
        $list=$usersService->getList($filters);
        $fieldsArray = array(
            "email",
            "name",
            "subscription"
        );
        $headerArray = array(
            "email"=>"Email",
            "name"=>"Name",
            "subscription"=>"Date of subscription"
        );
        
        $filters2 = Filter::factory();
        $filters2->addFilter(Filter::factory('Value')->setName('UTType')
            ->setValue("email"));
        $emailUserType = Manager::getService("UserTypes")->findOne($filters2);
        foreach ($emailUserType['fields'] as $typeField){
            if ((!$typeField['config']['allowBlank'])&&(($typeField['cType']=='Ext.form.field.Text')||($typeField['cType']=='Ext.form.field.TextArea'))){
                $fieldsArray[]=$typeField['config']['name'];
                $headerArray[$typeField['config']['name']]=$typeField['config']['fieldLabel'];
            }
        }
        $csvLine = array();
        
        foreach ($fieldsArray as $field) {
            $csvLine[] = $headerArray[$field];
        }
        fputcsv($csvResource, $csvLine, ';');
        
        foreach ($list['data'] as $client) {
            $csvLine = array();
        
            foreach ($fieldsArray as $field) {
                switch ($field) {
                    case 'subscription':
                        $csvLine[] = date('d-m-Y H:i:s', $client["mailingLists"][$params['id']]["date"]);
                        break;
                    case 'email':
                    case 'name':
                        $csvLine[] = isset($client[$field]) ? $client[$field] : 'null';
                        break;
                    default:
                        $csvLine[] = isset($client['fields'][$field]) ? $client['fields'][$field] : 'null';
                        break;
                }
            }
            fputcsv($csvResource, $csvLine, ';');
        }
        
        $content = file_get_contents($filePath);
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'text/csv');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"$fileName\"");
        $headers->addHeaderLine('Accept-Ranges', 'bytes');
        $headers->addHeaderLine('Content-Length', strlen($content));
        
        $response->setContent(utf8_decode($content));
        return $response;
    }
    
    public function importUsersAction(){
        $mlId=$this->params()->fromPost("id",null);
        $returnArray = array();
        $fileInfos = $this->params()->fromFiles('csvFile');
        if (! isset($fileInfos)) {
            $returnArray['success'] = false;
            $returnArray['message'] = "Pas de fichier reçu.";
            $this->getResponse()->setStatusCode(500);
            return new JsonModel($returnArray);
        }
        $mimeType = mime_content_type($fileInfos['tmp_name']);
        $contentType = isset($mimeType) ? $mimeType : $fileInfos['type'];
        if (($contentType != "text/plain") && ($contentType!= "text/csv")) {
            $returnArray['success'] = false;
            $returnArray['message'] = "Le fichier doit doit être au format CSV.";
            $this->getResponse()->setStatusCode(500);
            return new JsonModel($returnArray);
        }
        $recievedFile = fopen($fileInfos['tmp_name'], 'r');
        // Read the first line to start at the second line
        fgetcsv($recievedFile, 1000000, ';', '"', '\\');
        $lineCounter = 0;
        $success=true;
        while (($currentLine = fgetcsv($recievedFile, 1000000, ';', '"', '\\')) !== false) {
            if (isset($currentLine[1])){
                $resultInter=$this->_dataService->subscribe($mlId,$currentLine[0], true, $currentLine[1]);
            } else {
                $resultInter=$this->_dataService->subscribe($mlId,$currentLine[0]);
            }
            $success==$success&&$resultInter['success'];
            $lineCounter=$lineCounter+1;
        }
        fclose($recievedFile);
        $returnArray['importedContentsCount'] = $lineCounter;
        $returnArray['success'] = $success;
        return new JsonModel($returnArray);
    }
    
}