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
     * Object which represent the mongoDB Collection
     *
     * @var \MongoCollection
     */
    private $_collection;

	public function changePasswordAction(){
		$data = $this -> getRequest() -> getParam('data');
		
		$hashService = \Rubedo\Services\Manager::getService('Hash');
		
		if (!is_null($data)) {
			$insertData = Zend_Json::decode($data);
			if (is_array($insertData)) {
				$password = $insertData['password'];
				$salt = rand();
				unset($insertData['leaf']);
				
				$password = $hashService->derivatePassword($password, $salt);
				
				$insertData['password'] = $password;

		        if(!isset($insertData['version'])){
		        	$insertData['version'] = 1;
		        } else {
		        	$insertData['version'] += 1;
		        }
		
		        $resultArray = $this->_collection->insert($insertData, array("safe" => $safe));
		        if ($resultArray['ok'] == 1) {
		            $insertData['id'] = (string)$insertData['_id'];
		            unset($insertData['_id']);
		            $returnArray = array('success' => true);
		        } else {
		            $returnArray = array('success' => false);
		        }
		
		        return $returnArray;

			} else {
				$returnArray = array('success' => false, "msg" => 'Not an array');
			}
		} else {
			$returnArray = array('success' => false, "msg" => 'No Data');
		}
		if (!$returnArray['success']) {
			$this -> getResponse() -> setHttpResponseCode(500);
		}
		return $this->_helper->json($returnArray);
	}

}
