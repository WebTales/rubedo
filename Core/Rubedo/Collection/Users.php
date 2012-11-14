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
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IUsers;
use Rubedo\Mongo\DataAccess;

/**
 * Service to handle Users
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Users extends AbstractCollection implements IUsers
{
	/**
	 * Change the password of the user given by its id
	 * Check version conflict
	 * 
	 * @param string $$password new password
	 * @param int $version version number
	 * @param string $userId id of the user to be changed
	 */
	public function changePassword($password,$version,$userId){
		$hashService = \Rubedo\Services\Manager::getService('Hash');
		
		$salt = $hashService->generateRandomString();
		
		if (!empty($password) && !empty($userId) && !empty($version)) {
			$password = $hashService->derivatePassword($password, $salt);
			
			$insertData['id'] = $userId;
			$insertData['version'] = (int) $version;
			$insertData['password'] = $password;
			$insertData['salt'] = $salt;
			
			$result = $this->_dataService->update($insertData, true);
			
			if($result['success'] == true){
				return true;
			} else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	/**
	 * Set the collection name
	 */
	public function __construct(){
		$this->_collectionName = 'Users';
		parent::__construct();
	}
	
	/**
	 * ensure that no password field is sent outside of the service layer
	 */
	protected function _init(){
		parent::_init();
		$this->_dataService->addToExcludeFieldList(array('password'));
	}
}
