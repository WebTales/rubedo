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
class Backoffice_ContentsController extends Backoffice_DataAccessController
{
    public function init(){
		parent::init();
		
		// init the data access service
		$this -> _dataService = Rubedo\Services\Manager::getService('Contents');
	}
	
	/**
     * The default read Action
     *
     * Return the content of the collection, get filters from the request
     * params, get sort from request params
     *
     */
    public function indexAction() {
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
		$startJson = $this->getRequest()->getParam('start');
		if (isset($startJson)) {
            $start = Zend_Json::decode($startJson);
        } else {
            $start = null;
        }
		$limitJson = $this->getRequest()->getParam('limit');
		if (isset($limitJson)) {
            $limit = Zend_Json::decode($limitJson);
        } else {
            $limit = null;
        }

        $dataValues = $this->_dataService->getList($filters, $sort, $start, $limit, false);

        $response = array();
        $response['data'] = array_values($dataValues);
        $response['total'] = count($response['data']);
        $response['success'] = TRUE;
        $response['message'] = 'OK';

        $this->_returnJson($response);
    }
	
	/**
     * read child action
     *
     * Return the children of a node
     *
     */
    public function readChildAction() {
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

        $parentId = $this->getRequest()->getParam('node', 'root');

        $dataValues = $this->_dataService->readChild($parentId, $filters, $sort, false);

        $response = array();
        $response['children'] = array_values($dataValues);
        $response['total'] = count($response['children']);
        $response['success'] = TRUE;
        $response['message'] = 'OK';

        $this->_returnJson($response);
    }
	
	/**
     * The create action of the CRUD API
     */
    public function createAction() {
        $data = $this->getRequest()->getParam('data');

        if (!is_null($data)) {
            $insertData = Zend_Json::decode($data);
            if (is_array($insertData)) {
                $returnArray = $this->_dataService->create($insertData, true, false);

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

        $data = $this->getRequest()->getParam('data');

        if (!is_null($data)) {
            $updateData = Zend_Json::decode($data);
            if (is_array($updateData)) {

                $returnArray = $this->_dataService->update($updateData, true, false);

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