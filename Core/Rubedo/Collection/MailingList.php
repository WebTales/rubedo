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
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IMailingList;
use Rubedo\Services\Manager;

/**
 * Service to handle Mailing list
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
class MailingList extends AbstractCollection implements IMailingList
{

	public function __construct(){
		$this->_collectionName = 'MailingList';
		parent::__construct();
	}
	
	/**
	 * Add a user into a specified mailing list
	 *
	 * @param string $mailingListId
	 * @param string $email
	 *
	 * @return array
	 * 
	 * @see \Rubedo\Interfaces\Collection\IMailingList::subscribe()
	 */
	public function subscribe($mailingListId, $email) {
		//Get mailing list
		$mailingList = $this->findById($mailingListId);
		
		//Test if the mailing list exist in database
		if($mailingList === null){
			throw new \Rubedo\Exceptions\User('Identifiant de newsletter invalide');
		}
			
		//Get the user
		$wasFiltered = AbstractCollection::disableUserFilter();
		$user = Manager::getService("Users")->findByEmail($email);
		AbstractCollection::disableUserFilter($wasFiltered);
		
		//Check if the user exist
		if($user != null) {
			//Check if the user is already registered
			$isRegistered=false;
			
			if(isset($user["mailingLists"]) && isset($user["mailingLists"][$mailingList["id"]]) && $user["mailingLists"][$mailingList["id"]]['status']==true){
				$isRegistered = true;
			}
			
			if($isRegistered === false){
				//Add new mailing list to the user
				$user["mailingLists"][$mailingList["id"]] =array("id" => $mailingList["id"], "status" => true, "date" => time());
				
				//Update user
				$wasFiltered = AbstractCollection::disableUserFilter();
				$updateResult = Manager::getService("Users")->update($user);
				AbstractCollection::disableUserFilter($wasFiltered);
				
				//Check the result of the update
				if($updateResult["success"]){
					$response = array("success" => true, "msg" => "Inscription réussie");
				} else {
					throw new \Rubedo\Exceptions\User("Erreur lors de la mise à jour de l'utilisateur");
				}
			} else {
				$response = array("success" => false, "msg" => "Vous êtes déjà inscrit à cette newsletter");
			}
		} else {
			//Make the default skeleton for the user if it's a new user
			$user = array(
				"login" => $email,
				"email" => $email,
				"workspace" => $mailingList["workspaces"],
				"mailingLists" => array(
					array(
						"id" => $mailingList["id"],
						"status" => true,
						"date" => time(),
					),
				),
			);
			
			//Create the new user
			$createResult = Manager::getService("Users")->create($user);
			
			//Check the result of the creation
			if($createResult["success"]) {
				$response = array("success" => true, "msg" => "Inscription réussie");
			} else {
				throw new \Rubedo\Exceptions\User("Erreur lors de la création de l'utilisateur");
			}
		}
		
		return $response;
	}
	
	/**
	 * Remove a user from a specified mailing list
	 *
	 * @param string $mailingListId
	 * @param string $email
	 *
	 * @return array
	 * 
	 * @see \Rubedo\Interfaces\Collection\IMailingList::unSubscribe()
	 */
	public function unSubscribe($mailingListId, $email) {
		return true;
	}
	
}
