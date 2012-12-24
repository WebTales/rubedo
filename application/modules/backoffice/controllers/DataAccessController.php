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

use Rubedo\Mongo\DataAccess, Rubedo\Mongo, Rubedo\Services;

/**
 * Abstract Controller providing CRUD API and dealing with the data access
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
abstract class Backoffice_DataAccessController extends Zend_Controller_Action
{

    /**
     * Name of the store which is also to the collection name
     *
     * @var string
     */
    protected $_store;

    /**
     * Data Access Service
     *
     * @var IAbstractCollection
     */
    protected $_dataService;

    /**
     * should json be prettified
     *
     * @var bool
     */
    protected $_prettyJson = true;

	/**
	 * Array with the read only actions
	 */
	protected $_readOnlyAction = array('index', 'find-one', 'read-child', 'tree');
	
    /**
     * Disable layout & rendering, set content type to json
     * init the store parameter if transmitted
     *
     * @see Zend_Controller_Action::init()
     */
    public function init() {
        parent::init();
		
		$sessionService = \Rubedo\Services\Manager::getService('Session');
		
        // refuse write action not send by POST
        if (!$this->getRequest()->isPost() && !in_array($this->getRequest()->getActionName(), $this->_readOnlyAction)) {
            throw new \Exception("You can't call a write action with a GET request");
        } else {
        	if(!in_array($this->getRequest()->getActionName(), $this->_readOnlyAction)){
        		$user = $sessionService->get('user');
        		$token = $this->getRequest()->getParam('token');
				
				if($token !== $user['token']){
					throw new \Exception("The token given in the request doesn't match with the token in session");
				}
        	}
        }

    }

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

        $dataValues = $this->_dataService->getList($filters, $sort, $start, $limit);

        $response = array();
        $response['total'] = $dataValues['count'];
        $response['data'] = $dataValues['data'];
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

        $dataValues = $this->_dataService->readChild($parentId, $filters, $sort);

        $response = array();
        $response['children'] = array_values($dataValues);
        $response['total'] = count($response['children']);
        $response['success'] = TRUE;
        $response['message'] = 'OK';

        $this->_returnJson($response);
    }

    /**
     * Delete all the childrens of the parent given in paremeter
     *
     * @return array
     */
    public function deleteChildAction() {
        $data = $this->getRequest()->getParam('data');
        $result = array('success' => true);

        if (!is_null($data)) {
            $data = Zend_Json::decode($data);

            if (is_array($data)) {

                $returnArray = $this->_dataService->deleteChild($data);

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
     * The read as tree Action
     *
     * Return the content of the collection, get filters from the request
     * params
     *
     * @todo remove the temp hack when database starter is ready
     */
    public function treeAction() {

        $dataValues = $this->_dataService->readTree();

        $response = array();
        $response["expanded"] = true;
        $response['children'] = $dataValues;
        $response['success'] = TRUE;
        $response['message'] = 'OK';

        $this->_returnJson($response);
    }

    /**
     * The destroy action of the CRUD API
     */
    public function deleteAction() {
        $data = $this->getRequest()->getParam('data');

        if (!is_null($data)) {
            $data = Zend_Json::decode($data);
            if (is_array($data)) {

                $returnArray = $this->_dataService->destroy($data, true);

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
        $data = $this->getRequest()->getParam('data');

        if (!is_null($data)) {
            $insertData = Zend_Json::decode($data);
            if (is_array($insertData)) {
                $returnArray = $this->_dataService->create($insertData, true);

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

                $returnArray = $this->_dataService->update($updateData, true);

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
     * Find an item given by its id
     *
     * @return Json_object
     */
    public function findOneAction() {
        $contentId = $this->getRequest()->getParam('id');

        if (!is_null($contentId)) {

            $return = $this->_dataService->findById($contentId);

            if (empty($return['id'])) {

                $returnArray = array('success' => false, "msg" => 'Object not found');

            } else {

                $returnArray = array('succes' => true, 'data' => $return);
            }

        } else {

            $returnArray = array('success' => false, "msg" => 'Missing param');
        }

        $this->_returnJson($returnArray);
    }

}
