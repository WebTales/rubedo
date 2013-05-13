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

require_once ('DataAccessController.php');

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
    protected $_readOnlyAction = array('index', 'find-one', 'read-child', 'tree', 'model', 'get-readable-content-types', 'is-used', 'is-changeable');

    public function init() {
        parent::init();

        // init the data access service
        $this->_dataService = Rubedo\Services\Manager::getService('ContentTypes');
    }

    public function getReadableContentTypesAction() {
        return $this->_returnJson($this->_dataService->getReadableContentTypes());
    }

    public function isUsedAction() {
        $id = $this->getRequest()->getParam('id');
		$wasFiltered = Rubedo\Collection\AbstractCollection::disableUserFilter();
        $result = Rubedo\Services\Manager::getService('Contents')->isTypeUsed($id);
        Rubedo\Collection\AbstractCollection::disableUserFilter($wasFiltered);
        $this->_returnJson($result);
    }

    public function isChangeableAction() {
        $data = $this->getRequest()->getParams();
        $newType = Zend_Json::decode($data['fields']);
        $id = $data['id'];
        $originalType = $this->_dataService->findById($id);
        $originalType = $originalType['fields'];
        
       
        /*
        $listResult = Rubedo\Services\Manager::getService('Contents')->getListByTypeId($id);
        if (is_array($listResult) && $listResult['count'] == 0) {
            $resultArray = array("modify" => "ok");
        } */
        $wasFiltered = Rubedo\Collection\AbstractCollection::disableUserFilter();
        $isUsedResult = Rubedo\Services\Manager::getService('Contents')->isTypeUsed($id);
        Rubedo\Collection\AbstractCollection::disableUserFilter($wasFiltered);
        if (!$isUsedResult['used']){
        	$resultArray = array("modify" => "ok");
        }
        else {
        	
        	$result=$this->_dataService->isChangeableContentType($originalType,$newType);
        	$resultArray=($result==true)?array("modify" => "ok"):array("modify" => "not");
        	
        $this->_returnJson($resultArray);
    }
    }
    
 
    

}
