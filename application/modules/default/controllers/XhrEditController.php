<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    yet to be written
 * @version    $Id:
 */

/**
 * Front End Edition controller
 *
 * @author nduvollet
 * @category Rubedo
 * @package Rubedo
 */
class XhrEditController extends Zend_Controller_Action {
	/**
	 * variable for the Session service
	 *
	 * @param 	Rubedo\Interfaces\User\ISession
	 */
	protected $_session;

	/**
	 * variable for the Data service
	 *
	 * @param 	Rubedo\Interfaces\User\ISession
	 */
	protected $_dataService;

	/**
	 * Init the session service
	 */
	public function init() {
		$this -> _dataService = Rubedo\services\Manager::getService('Contents');
	}

	/**
	 * Allow to define the current theme
	 */
	public function indexAction() {

		$contentId = $this -> getRequest() -> getParam('id');
		$data = $this -> getRequest() -> getParam('data');
		if (!empty($contentId['id'])) {
			$contentId = explode("_", $contentId);
			$id = $contentId[0];
			$field = $contentId[1];

			$baseData = $this -> _dataService -> findById($id, true, false);
			$baseData['fields'][$field] = $data;
			$returnArray = $this -> _dataService -> update($baseData, true, true);

		} else {
			$returnArray['success'] = false;
			$returnArray['msg'] = 'No content id given.';
		}
		if (!$returnArray['success']) {
			$this -> getResponse() -> setHttpResponseCode(500);
		}
		return $this -> _helper -> json($returnArray);

	}

}
