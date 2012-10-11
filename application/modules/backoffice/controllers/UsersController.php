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
	 * The default read Action
	 *
	 * Return the content of the collection, get filters from the request
	 * params
	 *
	 */
	public function indexAction() {
		$filterJson = $this -> getRequest() -> getParam('filter');
		if (isset($filterJson)) {
			$filters = Zend_Json::decode($filterJson);
			foreach ($filters as $value) {
				if ($value["operator"] == 'like') {
					$this -> _dataReader -> addFilter(array($value["property"] => array('$regex' => new \MongoRegex('/.*' . $value["value"] . '.*/i'))));
				}

			}
		}
		$sortJson = $this -> getRequest() -> getParam('sort');
		if (isset($sortJson)) {
			$sort = Zend_Json::decode($sortJson);
			foreach ($sort as $value) {

					$this -> _dataReader -> addSort(array($value["property"] => $value["direction"]));				

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

}
