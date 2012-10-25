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

	public function changePasswordAction(){
		$hashService = \Rubedo\Services\Manager::getService('Hash');
		
		$password = $_POST['password'];
		$id = $_POST['id'];
		$version = $_POST['version'];
		
		// Create a random string for the salt
		$caracters = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmonopqrstuvwxyz123456789');
	    shuffle($caracters);
	    $caracters = array_slice($caracters, 0, 10);
	    $salt = implode('', $caracters);
		
		if (!empty($password) && !empty($id)) {
			$password = $hashService->derivatePassword($password, $salt);
			
			$insertData = array('id' => $id, 'password' => $password, 'salt' => $salt, 'version' => $version);
			
			$result = $this->_dataReader->update($insertData, true);
			
			if($result['success'] == true){
				$message['success'] = true;
			} else if($result['success'] == false){
				$message['success'] = false;
			}
			
			return $message;
		} else {
			$returnArray = array('success' => false, "msg" => 'No Data');
		}
		
		if (!$returnArray['success']) {
			$this -> getResponse() -> setHttpResponseCode(500);
		}
		
		return $this->_helper->json($returnArray);
	}

}
