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
	 * Get icons preferences of the current user
	 * 
	 * @return array
	 */
	public function indexAction() {
		$response = array();	
			
		if($_SESSION['id']){
			$userId = $_SESSION['id'];
			
			if(!empty($userId)){
				$this -> _dataReader -> addFilter(array('userId' => $userId));
				
				$dataValues = $this -> _dataReader -> read();

				$response['data'] = array_values($dataValues);
				$response['total'] = count($response['data']);
				$response['success'] = TRUE;
				$response['message'] = 'OK';
			} else {
				$response['success'] = FALSE;
				$response['message'] = '$userId should not be empty';
			}
		} else {
			$response['success'] = FALSE;
			$response['message'] = 'No index id set in the session.';
		}
		
		$this -> _returnJson($response);
	}

}