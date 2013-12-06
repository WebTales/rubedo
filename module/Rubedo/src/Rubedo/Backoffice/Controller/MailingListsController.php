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
}