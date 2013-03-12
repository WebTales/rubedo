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
    protected $_indexes = array(
        array('keys'=>array('login'=>1),'options'=>array('unique'=>true)),
        array('keys'=>array('email'=>1),'options'=>array('unique'=>true)),
    );

    /**
     * Change the password of the user given by its id
     * Check version conflict
     *
     * @param string $$password
     *            new password
     * @param int $version
     *            version number
     * @param string $userId
     *            id of the user to be changed
     */
    public function changePassword ($password, $version, $userId)
    {
        $hashService = \Rubedo\Services\Manager::getService('Hash');
        
        $salt = $hashService->generateRandomString();
        
        if (! empty($password) && ! empty($userId) && ! empty($version)) {
            $password = $hashService->derivatePassword($password, $salt);
            
            $insertData['id'] = $userId;
            $insertData['version'] = (int) $version;
            $insertData['password'] = $password;
            $insertData['salt'] = $salt;
            
            $result = $this->_dataService->update($insertData, array(
                'safe' => true
            ));
            
            if ($result['success'] == true) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getAdminUsers ()
    {
        $adminGroup = Manager::getService('Groups')->findByName('admin');
        $userIdList = array();
        if (isset($adminGroup['members'])) {
            foreach ($adminGroup['members'] as $id) {
                $userIdList[] = $id;
            }
        }
        
        $filters = array();
        $filters[] = array(
            'property' => 'id',
            'value' => $userIdList,
            'operator' => '$in'
        );
        return $this->getList($filters);
    }

    /**
     * Set the collection name
     */
    public function __construct ()
    {
        $this->_collectionName = 'Users';
        parent::__construct();
    }

    /**
     * ensure that no password field is sent outside of the service layer
     */
    protected function _init ()
    {
        parent::_init();
        $this->_dataService->addToExcludeFieldList(array(
            'password'
        ));
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
		if(!isset($obj['groups']) || $obj['groups']==""){
			$groups= array();
		} else {
			$group = $obj['groups'];
		}		
		
        $obj['groups'] = null;
        
        $returnValue = parent::create($obj, $options);
        $createUser = $returnValue['data'];
        
        Manager::getService('Groups')->addUserToGroupList($createUser['id'], $groups);
        
        $personalPrefsObj = array(
            'userId' => $createUser['id'],
            'stylesheet' => 'resources/css/red_theme.css',
            'wallpaper' => 'resources/wallpapers/rubedo.jpg',
            'iconSet' => 'red',
            'themeColor' => '#D7251D',
            'lastEdited' => array(),
            'HCMode' => false
        );
        
        $personalPrefsService = Manager::getService('PersonalPrefs');
        
		$personalPrefsService->create($personalPrefsObj);
		
        return $returnValue;
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \Rubedo\Collection\AbstractCollection::findById()
     */
    public function findById ($contentId)
    {
        $result = parent::findById($contentId);
        if($result){
            $result = $this->_addGroupsInfos($result);
        }
        return $result;
    }

    /**
     * Add groups data from group members list.
     *
     * @param array $obj            
     * @return array
     */
    protected function _addGroupsInfos ($obj)
    {
        $groupList = Manager::getService('Groups')->getListByUserId($obj['id']);
        $obj['groups'] = array();
        foreach ($groupList['data'] as $group) {
            $obj['groups'][] = $group['id'];
        }
        
        return $obj;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::getList()
     */
    public function getList ($filters = null, $sort = null, $start = null, $limit = null)
    {
        $list = parent::getList($filters, $sort, $start, $limit);
        
        foreach ($list['data'] as &$value) {
            $value = $this->_addGroupsInfos($value);
        }
        return $list;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array('safe'=>true,))
    {
        Manager::getService('Groups')->clearUserFromGroups($obj['id']);
        $groups = isset($obj['groups']) ? $obj['groups'] : array();
        Manager::getService('Groups')->addUserToGroupList($obj['id'], $groups);
        $obj['groups'] = null;
        $result = parent::update($obj, $options);
        if($result){
            $result['data'] = $this->_addGroupsInfos($result['data']);
        }
        return $result;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy (array $obj, $options = array('safe'=>true,))
    {
        Manager::getService('Groups')->clearUserFromGroups($obj['id']);
        return parent::destroy($obj, $options);
    }
    
    /**
     * Add a readOnly field to contents based on user rights
     *
     * @param array $obj
     * @return array
     */
    protected function _addReadableProperty ($obj)
    {
        if (! self::isUserFilterDisabled()) {
            //$writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
    
            if (!Manager::getService('Acl')->hasAccess("write.ui.users")) {
                $obj['readOnly'] = true;
            }
        }
    
        return $obj;
    }
}
