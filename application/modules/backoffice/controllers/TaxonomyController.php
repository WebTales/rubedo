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
class Backoffice_TaxonomyController extends Backoffice_DataAccessController
{
    /**
     * Name of the store which is also to the collection name
     * 
     * @see Backoffice_DataAccessController::$_store
     * @var string
     */
    protected $_store = 'Taxonomy';
	
	public function init(){
		parent::init();
		$this->_termsService =  Rubedo\Services\Manager::getService('MongoDataAccess');
		$this->_termsService -> init('TaxonomyTerms');
	}
	
	/**
	 * The destroy action of the CRUD API
	 */
	public function deleteVocabularyAction() {
		$data = $this -> getRequest() -> getParam('data');
		$error = false;
		
		if (!is_null($data)) {
			$data = Zend_Json::decode($data);
			if (is_array($data)) {
				
				$returnArray = $this->_dataService->deleteVocabulary($data);
				
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

}
