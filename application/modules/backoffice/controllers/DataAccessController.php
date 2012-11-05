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

use Rubedo\Mongo\DataAccess, Rubedo\Mongo, Rubedo\Services;

/**
 * Controller providing CRUD API and dealing with the data access
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_DataAccessController extends Zend_Controller_Action {

	/**
	 * Name of the store which is also to the collection name
	 *
	 * @var string
	 */
	protected $_store;

	/**
	 * Data Access Service
	 *
	 * @var DataAccess
	 */
	protected $_dataReader;

	/**
	 * should json be prettified
	 *
	 * @var bool
	 */
	protected $_prettyJson = true;

	/**
	 * Disable layout & rendering, set content type to json
	 * init the store parameter if transmitted
	 *
	 * @see Zend_Controller_Action::init()
	 */
	public function init() {
		parent::init();
		// refuse write action not send by POST
		if (!$this -> getRequest() -> isPost() && $this -> getRequest() -> getActionName() !== 'index') {
			//throw new \Exception('This action should be called by POST request');
		}

		// set the store value from the request is sent
		if (!isset($this -> _store)) {
			$this -> _store = $this -> getRequest() -> getParam('store');
		}

		if (!isset($this -> _store)) {
			throw new Zend_Exception("No store parameter", 1);

		}

		// init the data access service
		$this -> _dataReader = Rubedo\Services\Manager::getService('MongoDataAccess');
		$this -> _dataReader -> init($this -> _store);
	}

	/**
	 * Set the response body with Json content
	 * Option : json is made human readable
	 * @param mixed $data data to be json encoded
	 */
	protected function _returnJson($data) {
		// disable layout and set content type
		$this -> getHelper('Layout') -> disableLayout();
		$this -> getHelper('ViewRenderer') -> setNoRender();
		$this -> getResponse() -> setHeader('Content-Type', "application/json", true);

		$returnValue = Zend_Json::encode($data);
		if ($this -> _prettyJson) {
			$returnValue = Zend_Json::prettyPrint($returnValue);
		}
		$this -> getResponse() -> setBody($returnValue);
	}

	/**
	 * The default read Action
	 *
	 * Return the content of the collection, get filters from the request
	 * params, get sort from request params
	 *
	 */
	public function indexAction() {
		$filterJson = $this -> getRequest() -> getParam('filter');
		if (isset($filterJson)) {
			$filters = Zend_Json::decode($filterJson);
			foreach ($filters as $value) {
				if ((!(isset($value["operator"])))||($value["operator"]=="eq")) {
					$this -> _dataReader -> addFilter(array($value["property"] => $value["value"]));					
				}
				else if ($value["operator"] == 'like') {
					$this -> _dataReader -> addFilter(array($value["property"] => array('$regex' => new \MongoRegex('/.*' . $value["value"] . '.*/i'))));
				}

			}
		}
		$sortJson = $this -> getRequest() -> getParam('sort');
		if (isset($sortJson)) {
			$sort = Zend_Json::decode($sortJson);
			foreach ($sort as $value) {

					$this -> _dataReader -> addSort(array($value["property"] => strtolower($value["direction"])));				

			}
		}		
		$dataValues = $this -> _dataReader -> read();

		$response = array();
		$response['data'] = array_values($dataValues);
		$response['total'] = count($response['data']);
		$response['success'] = TRUE;
		$response['message'] = 'OK';

		$this -> _returnJson($response);
	}
	
	/**
	 * read child action
	 *
	 * Return the children of a node
	 *
	 */
	public function readChildAction() {
		$filterJson = $this -> getRequest() -> getParam('filter');
		if (isset($filterJson)) {
			$filters = Zend_Json::decode($filterJson);
			foreach ($filters as $value) {
				if ((!(isset($value["operator"])))||($value["operator"]=="eq")) {
					$this -> _dataReader -> addFilter(array($value["property"] => $value["value"]));					
				}
				else if ($value["operator"] == 'like') {
					$this -> _dataReader -> addFilter(array($value["property"] => array('$regex' => new \MongoRegex('/.*' . $value["value"] . '.*/i'))));
				}

			}
		}
		$sortJson = $this -> getRequest() -> getParam('sort');
		if (isset($sortJson)) {
			$sort = Zend_Json::decode($sortJson);
			foreach ($sort as $value) {

					$this -> _dataReader -> addSort(array($value["property"] => strtolower($value["direction"])));				

			}
		}

		$parentId = $this->getRequest()->getParam('node','root');

		$dataValues = $this -> _dataReader -> readChild($parentId);

		$response = array();
		$response['children'] = array_values($dataValues);
		$response['total'] = count($response['children']);
		$response['success'] = TRUE;
		$response['message'] = 'OK';

		$this -> _returnJson($response);
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

		$dataValues = $this -> _dataReader -> readTree();

		$response = array();
		$response["expanded"]	=	true;
		$response['children'] 	= 	$dataValues;
		$response['success'] 	= 	TRUE;
		$response['message'] 	= 	'OK';

		$this -> _returnJson($response);
	}

	/**
	 * The destroy action of the CRUD API
	 */
	public function deleteAction() {
		$data = $this -> getRequest() -> getParam('data');

		if (!is_null($data)) {
			$data = Zend_Json::decode($data);
			if (is_array($data)) {

				$returnArray = $this -> _dataReader -> destroy($data, true);

			} else {
				$returnArray = array('success' => false, "msg" => 'Not an array');
			}

		} else {
			$returnArray = array('success' => false, "msg" => 'Invalid Data');
		}
		if (!$returnArray['success']) {
			$this -> getResponse() -> setHttpResponseCode(500);
		}
		$this -> _returnJson($returnArray);
	}

	/**
	 * The create action of the CRUD API
	 */
	public function createAction() {
		$data = $this -> getRequest() -> getParam('data');

		if (!is_null($data)) {
			$insertData = Zend_Json::decode($data);
			if (is_array($insertData)) {
				$returnArray = $this -> _dataReader -> create($insertData, true);

			} else {
				$returnArray = array('success' => false, "msg" => 'Not an array');
			}
		} else {
			$returnArray = array('success' => false, "msg" => 'No Data');
		}
		if (!$returnArray['success']) {
			$this -> getResponse() -> setHttpResponseCode(500);
		}
		$this -> _returnJson($returnArray);
	}

	/**
	 * The update action of the CRUD API
	 */
	public function updateAction() {

		$data = $this -> getRequest() -> getParam('data');

		if (!is_null($data)) {
			$updateData = Zend_Json::decode($data);
			if (is_array($updateData)) {

				$returnArray = $this -> _dataReader -> update($updateData, true);
				

			} else {
				$returnArray = array('success' => false, "msg" => 'Not an array');
			}
		} else {
			$returnArray = array('success' => false, "msg" => 'No Data');
		}
		if (!$returnArray['success']) {
			$this -> getResponse() -> setHttpResponseCode(500);
		}
		$this -> _returnJson($returnArray);
	}

}
