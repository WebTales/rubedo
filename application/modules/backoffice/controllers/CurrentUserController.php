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
 * Controller providing CRUD API for the PersonalPrefs JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_CurrentUserController extends Backoffice_DataAccessController
{
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
	
	/**
     * Variable for Authentication service
	 * 
	 * @param 	Rubedo\Interfaces\User\IAuthentication
     */
	protected $_auth;
	
	/**
	 * Initialise the controller
	 */
	public function init(){
		parent::init();
		
		$this->_auth = \Rubedo\Services\Manager::getService('Authentication');
	}

	/**
	 * Get informations of the user
	 * 
	 * @return array
	 */
	public function indexAction() {
		$result = $this->_auth->getIdentity();
		$userId = $result['id'];
		
		$this->_dataReader->addToExcludeFieldList(array('password', 'salt'));
		$this->_dataReader->addFilter(array('id' => $userId));
		$readArray = $this->_dataReader->read();
		
		$this->_returnJson($readArray);
	}
	
	/**
	 * Unable the creation of users
	 * 
	 * @return array
	 */
	public function createAction() {
		$returnArray = array('success' => false, 'message' => 'Not authorized to create a user');
		$this -> _returnJson($returnArray);
	}

	/**
	 * Update the current values for the user
	 */
	public function updateAction() {
	 	
	}
	
	/**
	 * Unable the suppression of a user
	 * 
	 * @return array
	 */
	public function deleteAction(){
		$returnArray = array('success' => false, 'message' => 'Not authorized to delete a user');
		$this -> _returnJson($returnArray);
	}
}
