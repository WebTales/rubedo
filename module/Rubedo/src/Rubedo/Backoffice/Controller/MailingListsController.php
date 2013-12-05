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

    public function subscribeUserAction(){
        $userId=$this->params()->fromPost("userId",null);
        $mlId=$this->params()->fromPost("mlId",null);
        $result=$this->_dataService->subscribe($mlId,$userId);
        return $this->_returnJson($result);
    }

    public function unsubscribeUserAction(){
        $userId=$this->params()->fromPost("userId",null);
        $mlId=$this->params()->fromPost("mlId",null);
        $result=array();
        $result['success']=$this->_dataService->unSubscribe($mlId,$userId);
        return $this->_returnJson($result);
    }
}