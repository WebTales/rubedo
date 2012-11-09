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
	 * @todo implements that
	 */
	public function changePassword($password,$version,$id){
		$hashService = \Rubedo\Services\Manager::getService('Hash');
		
		$salt = $hashService->generateRandomString();
		
		if (!empty($password) && !empty($id) && !empty($version)) {
			$password = $hashService->derivatePassword($password, $salt);
			
			$insertData['id'] = $id;
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
	
	public function __construct(){
		$this->_collectionName = 'Users';
		parent::__construct();
	}
	
	protected function _init(){
		parent::_init();
		$this->_dataService->addToExcludeFieldList(array('password'));
	}
}
