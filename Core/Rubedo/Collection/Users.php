<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IUsers, Rubedo\Services\Manager;

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
			
			$result = $this->_dataService->update($insertData, array('safe' => true));
						
			if($result['success'] == true){
				return true;
			} else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	public function getAdminUsers(){
	    $adminGroup = Manager::getService('Groups')->findByName('admin');
	    $userIdList = array();
	    if(isset($adminGroup['members'])){
	        foreach($adminGroup['members'] as $id){
	            $userIdList[]= $id;
	        } 
	    }
	    
	    $filters = array();
	    $filters[]= array('property'=>'id','value'=>$userIdList,'operator'=>'$in');
	    return $this->getList($filters);
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

    /**
     * Create an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::create
     * @param array $obj
     *            data object
     * @param array $options            
     * @return array
     */
    public function create (array $obj, $options = array('safe'=>true))
    {
        $returnValue = parent::create($obj, $options);
        $obj = $returnValue['data'];
        
        $personalPrefsObj = array(
            'userId' => $obj['id'],
            'stylesheet' => 'resources/css/blue_theme.css',
            'wallpaper' => 'resources/wallpapers/rubedo.jpg',
            'iconSet' => 'blue',
            'themeColor' => '#D7251D',
            'HCMode' => 'false'
        );
        
        $personalPrefsService = Manager::getService('PersonalPrefs');
        $personalPrefsService->create($personalPrefsObj);
        return $returnValue;
    }
    
    public function findById($contentId){
        $result = parent::findById($contentId);
        $result = $this->_addGroupsInfos($result);
        return $result;
    }
    
    protected function _addGroupsInfos($obj){
        $groupList = Manager::getService('Groups')->getListByUserId($obj['id']);
        $obj['groups'] = array();
        foreach ($groupList['data'] as $group){
            $obj['groups'][] = $group['id'];
        }
        
        return $obj;
    }
    
	/* (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractCollection::getList()
     */
    public function getList ($filters = null, $sort = null, $start = null, $limit = null)
    {
        $list = parent::getList($filters, $sort , $start , $limit);
        
        foreach ($list['data'] as &$value){
           $value = $this->_addGroupsInfos($value);
        }
        return $list;
        
    }

    
    
    
}
