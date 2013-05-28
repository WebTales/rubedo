<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

 
/**
 * Controller providing CRUD API for the MailingList JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 *
 */
class Blocks_MailingListController extends Zend_Controller_Action
{
	
	/**
	 * Allow to add an email into a mailing list
	 * 
	 * @return json
	 */
	public function xhrAddEmailAction(){
		//Default mailing list
		$mailingListId = $this->getParam("mailing-list-id");
		if(!$mailingListId){
			throw new \Rubedo\Exceptions\User("No newsletter associeted to this form.", "Exception18");
		}
		
		//Declare email validator
		$emailValidator = new Zend_Validate_EmailAddress();
		
		//MailingList service
		$mailingListService = \Rubedo\Services\Manager::getService("MailingList");
		
		//Get email
		$email = $this->getParam("email");
		
		//Validate email
		if($emailValidator->isValid($email)) {
			//Register user
			$suscribeResult = $mailingListService->subscribe($mailingListId, $email);
			
			$this->_helper->json($suscribeResult);
		} else {
			$this->_helper->json(array("success" => false, "msg" => "Adresse e-mail invalide"));
		}
	}

}