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
class Backoffice_IconsController extends Backoffice_DataAccessController
{
    /**
     * Name of the store which is also to the collection name
     * 
     * @see Backoffice_DataAccessController::$_store
     * @var string
     */
    protected $_store = 'Icons';
	
	/**
	 * Data Access Service
	 *
	 * @var DataAccess
	 */
	protected $_dataReader;
	
	/**
     * Variable for Authentication service
	 * 
	 * @param 	Rubedo\Interfaces\User\IAuthentication
     */
	protected $_auth;
	
	public function init(){
		parent::init();
		
		$this->_auth = \Rubedo\Services\Manager::getService('Authentication');
	}
	
	/**
	 * Get icons preferences of the current user
	 * 
	 * @return array
	 */
	public function indexAction() {
		$response = array();
		
		$result = $this->_auth->getIdentity();
		
		if($result){
			$this -> _dataReader -> addFilter(array('userId' => $result['id']));
			
			$dataValues = $this -> _dataReader -> read();

			$response['data'] = array_values($dataValues);
			$response['total'] = count($response['data']);
			$response['success'] = TRUE;
			$response['message'] = 'OK';
		} else {
			$response['success'] = FALSE;
			$response['message'] = 'No user connected';
		}
		
		$this -> _returnJson($response);
	}
	
	/**
	 * Create a new icon in mongoDB
	 * 
	 * @return array
	 */
	public function createAction() {
		$data = $this -> getRequest() -> getParam('data');

		if (!is_null($data)) {
			$insertData = Zend_Json::decode($data);
			if (is_array($insertData)) {
				$result = $this->_auth->getIdentity();
				if($result){
					$userId = $result['id'];
					$insertData['userId'] = $userId;
					$returnArray = $this -> _dataReader -> create($insertData, true);
				} else {
					$returnArray = array('success' => false, "msg" => 'No user connected');
				}
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