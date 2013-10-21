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

use Rubedo\Interfaces\Collection\IUsers, Rubedo\Services\Manager, WebTales\MongoFilters\Filter;

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
        array(
            'keys' => array(
                'login' => 1
            ),
            'options' => array(
                'unique' => true
            )
        ),
        array(
            'keys' => array(
                'email' => 1
            ),
            'options' => array(
                'unique' => true
            )
        )
    );

    /**
     * Only access to content with read access
     *
     * @see \Rubedo\Collection\AbstractCollection::_init()
     */
    protected function _init ()
    {
        parent::_init();
        
        $this->_dataService->addToExcludeFieldList(array(
            'password'
        ));
        
        if (! self::isUserFilterDisabled()) {
            $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
            if (! in_array('all', $readWorkspaceArray)) {
                $filter = Filter::factory();
                
                $filter->addFilter(Filter::factory('In')->setName('workspace')
                    ->setValue($readWorkspaceArray));
                
                $this->_dataService->addFilter($filter);
            }
        }
    }

    protected function _addReadableProperty ($obj)
    {
        if (! self::isUserFilterDisabled()) {
            // Set the workspace for old items in database
            if (! isset($obj['workspace'])) {
                $obj['workspace'] = 'global';
            }
            
            $aclServive = Manager::getService('Acl');
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
            
            if (! $aclServive->hasAccess("write.ui.users") || ! in_array($obj['workspace'], $writeWorkspaces)) {
                $obj['readOnly'] = true;
            }
        }
        
        return $obj;
    }

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
            
            $result = $this->_dataService->update($insertData);
            
            if ($result['success'] == true) {
                return true;
            } else {
                throw new \Rubedo\Exceptions\User('Failed to update password', "Exception58");
            }
        } else {
            throw new \Rubedo\Exceptions\User('All required fields must be specified', "Exception59");
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
        $filters = Filter::factory();
        $filters->addFilter(Filter::factory('InUid')->setValue($userIdList));
        
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
     * Create an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::create
     * @param array $obj
     *            data object
     * @param array $options            
     * @return array
     */
    public function create (array $obj, $options = array())
    {
        if (! isset($obj['groups']) || $obj['groups'] == "") {
            $groups = array();
        } else {
            $groups = $obj['groups'];
        }
        
        $obj['groups'] = null;
        
        // Define default workspace for a user if it's not set
        if (! isset($obj['workspace']) || $obj['workspace'] == "") {
            $obj['workspace'] = array(
                Manager::getService('CurrentUser')->getMainWorkspaceId()
            );
        }
         
        $obj['workingLanguage'] = Manager::getService('Languages')->getDefaultLanguage();
        $obj['language'] = Manager::getService('Languages')->getDefaultLanguage();
        
        $returnValue = parent::create($obj, $options);
        $createUser = $returnValue['data'];

        if ($returnValue["success"]) {
            $this->_indexUser($createUser);
        }        
        Manager::getService('Groups')->addUserToGroupList($createUser['id'], $groups);
        
        $personalPrefsObj = array(
            'userId' => $createUser['id'],
            'stylesheet' => 'resources/css/red_theme.css',
            'wallpaper' => 'resources/wallpapers/rubedo.png',
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
    public function findById ($contentId, $forceReload = false)
    {
    	if($contentId === null){
    		return null;
    	}
        $result = parent::findById($contentId, $forceReload);
        if ($result) {
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
        if($obj['id']){
            $groupList = Manager::getService('Groups')->getListByUserId($obj['id']);
            $obj['groups'] = array();
            foreach ($groupList['data'] as $group) {
                $obj['groups'][] = $group['id'];
            }
        }
        
        
        return $obj;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::getList()
     */
    public function getList (\WebTales\MongoFilters\IFilter $filters = null, $sort = null, $start = null, $limit = null)
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
    public function update (array $obj, $options = array())
    {
        // Define default workspace for a user if it's not set
        if (! isset($obj['workspace']) || $obj['workspace'] == "") {
            $obj['workspace'] = array(
                Manager::getService('CurrentUser')->getMainWorkspaceId()
            );
        }
        
        Manager::getService('Groups')->clearUserFromGroups($obj['id']);
        $groups = isset($obj['groups']) ? $obj['groups'] : array();
        Manager::getService('Groups')->addUserToGroupList($obj['id'], $groups);
        $obj['groups'] = null;
        $result = parent::update($obj, $options);
        
        if ($result['success']) {
            $result['data'] = $this->_addGroupsInfos($result['data']);
        }

        if ($result["success"]) {
            $this->_indexUser($result['data']);
        }

        $this->propagateUserUpdate($obj['id']);
        
        return $result;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy (array $obj, $options = array())
    {
        Manager::getService('Groups')->clearUserFromGroups($obj['id']);
        $returnArray =  parent::destroy($obj, $options);
        if ($returnArray["success"]) {
            $this->_unIndexUser($obj);
        }
        return $returnArray;
    }

    /**
     * Push the user to Elastic Search
     *
     * @param array $obj
     */
    protected function _indexUser ($obj)
    {
        $contentType = Manager::getService('UserTypes')->findById($obj['typeId']);
        if(!$contentType || (isset($contentType['system']) && $contentType['system']==true)){
            return;
        }
    
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->indexUser($obj);
    }
    
    /**
     * Remove the user from Indexed Search
     *
     * @param array $obj
     */
    protected function _unIndexUser ($obj)
    {
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->deleteUser($obj['typeId'], $obj['id']);
    }
    
    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Collection\IUsers::findByEmail()
     */
    public function findByEmail ($email)
    {
        $filter = Filter::factory('Value')->setName('email')->setValue($email);
        $result = $this->_dataService->findOne($filter);
        if ($result) {
            $result = $this->_addGroupsInfos($result);
        }
        
        return $result;
    }

    
    public function getByType ($typeId, $start = null, $limit = null)
    {
        $filter = Filter::factory('Value')->setName('typeId')->SetValue($typeId);
        return $this->getList($filter, null, $start, $limit);
    }
    
    public function findValidatingUsersByWorkspace ($workspace)
    {
        $members = array();
        
        $wasFiltered = AbstractCollection::disableUserFilter();
        $groups = Manager::getService('Groups')->getValidatingGroupsForWorkspace($workspace);
        AbstractCollection::disableUserFilter($wasFiltered);
        foreach ($groups as $group) {
            foreach ($group['members'] as $member) {
                if (! empty($member)) {
                    $members[$member] = $member;
                }
            }
        }
        
        return array_values($members);
    }

    protected function propagateUserUpdate ($userId)
    {
        $servicesArray = \Rubedo\Interfaces\config::getCollectionServices();
        $result = true;
        foreach ($servicesArray as $service) {
            $result = Manager::getService($service)->renameAuthor($userId) && $result;
        }
    }
}
