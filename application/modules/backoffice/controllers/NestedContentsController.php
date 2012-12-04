<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */

/**
 * Controller providing CRUD API for the nested contents
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_NestedContentsController extends Zend_Controller_Action
{
	/**
	 * Parent content Id
	 * 
	 * @var string
	 */
	protected $_parentId;
	
    /**
     * should json be prettified
     *
     * @var bool
     */
    protected $_prettyJson = true;

    /**
     * Set the response body with Json content
     * Option : json is made human readable
     * @param mixed $data data to be json encoded
     */
    protected function _returnJson($data) {
        // disable layout and set content type
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-Type', "application/json", true);

        $returnValue = Zend_Json::encode($data);
        if ($this->_prettyJson) {
            $returnValue = Zend_Json::prettyPrint($returnValue);
        }
        $this->getResponse()->setBody($returnValue);
    }

    public function init() {
        // init the data access service
        $this->_dataService = Rubedo\Services\Manager::getService('NestedContents');
		
		$this->_parentId = $this->getRequest()->getParam('parentId');

        if (!isset($this->_parentId)) {
            $response = array();
            $response['success'] = false;
            $response['message'] = 'no parentId Given';
			$this->getResponse()->setHttpResponseCode(500);
            $this->_returnJson($response);
        }
    }

    /**
     * The default read Action
     *
     * Return the content of the collection, get filters from the request
     * params, get sort from request params
     *
     */
    public function indexAction() {
    	
    	if (!isset($this->_parentId)){
    		return;
    	}
		
        $filterJson = $this->getRequest()->getParam('filter');
        if (isset($filterJson)) {
            $filters = Zend_Json::decode($filterJson);
        } else {
            $filters = null;
        }
        $sortJson = $this->getRequest()->getParam('sort');
        if (isset($sortJson)) {
            $sort = Zend_Json::decode($sortJson);
        } else {
            $sort = null;
        }


        $dataValues = $this->_dataService->getList($this->_parentId, $filters, $sort);

        $response = array();
        $response['total'] = $dataValues['count'];
        $response['data'] = $dataValues['data'];
        $response['success'] = TRUE;
        $response['message'] = 'OK';

        $this->_returnJson($response);
    }

    /**
     * The destroy action of the CRUD API
     */
    public function deleteAction() {
    	
		if (!isset($this->_parentId)){
    		return;
    	}
		
        $data = $this->getRequest()->getParam('data');

        if (!is_null($data)) {
            $data = Zend_Json::decode($data);
            if (is_array($data)) {

                $returnArray = $this->_dataService->destroy($this->_parentId,$data, true);

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

    /**
     * The create action of the CRUD API
     */
    public function createAction() {
    		
    	if (!isset($this->_parentId)){
    		return;
    	}
		
        $data = $this->getRequest()->getParam('data');

        if (!is_null($data)) {
            $insertData = Zend_Json::decode($data);
            if (is_array($insertData)) {
                $returnArray = $this->_dataService->create($this->_parentId,$insertData, true);

            } else {
                $returnArray = array('success' => false, "msg" => 'Not an array');
            }
        } else {
            $returnArray = array('success' => false, "msg" => 'No Data');
        }
        if (!$returnArray['success']) {
            $this->getResponse()->setHttpResponseCode(500);
        }
        $this->_returnJson($returnArray);
    }

    /**
     * The update action of the CRUD API
     */
    public function updateAction() {
			
		if (!isset($this->_parentId)){
    		return;
    	}
		
        $data = $this->getRequest()->getParam('data');

        if (!is_null($data)) {
            $updateData = Zend_Json::decode($data);
            if (is_array($updateData)) {

                $returnArray = $this->_dataService->update($this->_parentId,$updateData, true);

            } else {
                $returnArray = array('success' => false, "msg" => 'Not an array');
            }
        } else {
            $returnArray = array('success' => false, "msg" => 'No Data');
        }
        if (!$returnArray['success']) {
            $this->getResponse()->setHttpResponseCode(500);
        }
        $this->_returnJson($returnArray);
    }

}
