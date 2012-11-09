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

require_once ('DataAccessController.php');

/**
 * Controller providing CRUD API for the users JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_UsersController extends Backoffice_DataAccessController {
	/**
	 * Name of the store which is also to the collection name
	 *
	 * @see Backoffice_DataAccessController::$_store
	 * @var string
	 */
	protected $_store = 'Users';
	
	/**
	 * Data Access Service
	 *
	 * @var DataAccess
	 */
	protected $_dataReader;
	
	public function init(){
		parent::init();
		
		// init the data access service
		$this -> _dataReader = Rubedo\Services\Manager::getService('Users');
	}

	public function changePasswordAction(){
		$hashService = \Rubedo\Services\Manager::getService('Hash');
		
		$password = $this -> getRequest() -> getParam('password');
		$id = $this -> getRequest() -> getParam('id');
		$version = $this -> getRequest() -> getParam('version');
		
		
		if (!empty($password) && !empty($id) && !empty($version)) {
			
			
			$result = $this->_dataReader->changePassword($password,$version,$id);
			
			if($result == true){
				$message['success'] = true;
			} else{
				$message['success'] = false;
			}
			
			return $this->_helper->json($message);
		} else {
			$returnArray = array('success' => false, "msg" => 'No Data');
		}
		
		if (!$returnArray['success']) {
			$this -> getResponse() -> setHttpResponseCode(500);
		}
		
		return $this->_helper->json($returnArray);
	}

	public function indexAction(){
		$filterJson = $this -> getRequest() -> getParam('filter');
		if (isset($filterJson)) {
			$filters = Zend_Json::decode($filterJson);
		}else{
			$filters = null;
		}
		$sortJson = $this -> getRequest() -> getParam('sort');
		if (isset($sortJson)) {
			$sort = Zend_Json::decode($sortJson);
		}else{
			$sort = null;
		}
				
		$dataValues = $this -> _dataReader -> getList($filters,$sort);

		$response = array();
		$response['data'] = array_values($dataValues);
		$response['total'] = count($response['data']);
		$response['success'] = TRUE;
		$response['message'] = 'OK';

		$this -> _returnJson($response);
	}

}
