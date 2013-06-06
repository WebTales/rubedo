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

use Rubedo\Interfaces\Collection\IGroups, Rubedo\Services\Manager, WebTales\MongoFilters\Filter;

/**
 * Service to handle Groups
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Groups extends AbstractCollection implements IGroups
{

    protected $_indexes = array(
        array(
            'keys' => array(
                'name' => 1,
                'parentId' => 1
            ),
            'options' => array(
                'unique' => true
            )
        ),
        array(
            'keys' => array(
                'parentId' => 1
            )
        ),
        array(
            'keys' => array(
                'members' => 1
            )
        )
    );

    public function __construct ()
    {
        $this->_collectionName = 'Groups';
        parent::__construct();
    }

    /**
     * Only access to groups with read access
     *
     * @see \Rubedo\Collection\AbstractCollection::_init()
     */
    protected function _init ()
    {
        parent::_init();
        
        if (! self::isUserFilterDisabled()) {
            $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
            if (! in_array('all', $readWorkspaceArray)) {
                $filter = Filter::Factory('In')->setName('workspace')->setValue($readWorkspaceArray);
                $this->_dataService->addFilter($filter);
            }
        }
    }

    /**
     *
     * @param string $id
     *            id whose children should be deleted
     * @return array array list of items to delete
     */
    protected function _getChildToDelete ($id)
    {
        // delete at least the node
        $returnArray = array(
            $this->_dataService->getId($id)
        );
        
        // read children list
        $terms = $this->readChild($id);
        
        // for each child, get sublist of children
        if (is_array($terms)) {
            foreach ($terms as $value) {
                $returnArray = array_merge($returnArray, $this->_getChildToDelete($value['id']));
            }
        }
        
        return $returnArray;
    }

    public function create (array $obj, $options = array())
    {
        // Define default read workspace for groups if it's not set
        if (! isset($obj['readWorkspaces']) || $obj['readWorkspaces'] == "" || $obj['readWorkspaces'] == array()) {
            $obj['readWorkspaces'] = array(
                Manager::getService('CurrentUser')->getMainWorkspaceId()
            );
        }
        $obj = $this->_initObject($obj);
        
        return parent::create($obj, $options);
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array())
    {
        $obj = $this->_initObject($obj);
        return parent::update($obj, $options);
    }

    /**
     * Delete objects in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::destroy
     * @param array $obj
     *            data object
     * @param bool $options
     *            should we wait for a server response
     * @return array
     */
    public function destroy (array $obj, $options = array())
    {
        $deleteCond = array(
            '_id' => array(
                '$in' => $this->_getChildToDelete($obj['id'])
            )
        );
        
        $resultArray = $this->_dataService->customDelete($deleteCond);
        
        if ($resultArray['ok'] == 1) {
            if ($resultArray['n'] > 0) {
                $returnArray = array(
                    'success' => true
                );
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'La suppression du groupe a échoué'
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => $resultArray["err"]
            );
        }
        return $returnArray;
    }

    public function getListByUserId ($userId)
    {
        $filters = Filter::Factory('Value')->setName('members')->setValue($userId);
        $groupList = $this->getListWithAncestors($filters);
        
        return $groupList;
    }

    public function getValidatingGroupsId ()
    {
        // contentReviewer
        $filters = Filter::Factory();
        $filters->addFilter(Filter::Factory('Value')->setName('roles')
            ->setValue('contentReviewer'));
        
        $groupList = $this->getList($filters);
        
        // fetchAllChildren
        $groupsArray = array();
        $list = $groupList['data'];
        foreach ($list as &$obj) {
            $groupsArray[] = $obj['id'];
            $childrenArray = Manager::getService('Groups')->fetchAllChildren($obj['id']);
            foreach ($childrenArray as $child) {
                $groupsArray[] = $child['id'];
            }
        }
        
        return array_unique($groupsArray);
    }

    public function getValidatingGroupsForWorkspace ($workspace)
    {
        $validatingGroups = Manager::getService('Groups')->getValidatingGroupsId();
        
        $filters = Filter::Factory();
        $filters->addFilter(Filter::Factory('In')->setName('writeWorkspaces')
            ->setValue(array(
            $workspace,
            'all'
        )));
        
        $groupList = $this->getList($filters);
        
        // fetchAllChildren
        $groupsArray = array();
        $list = $groupList['data'];
        
        foreach ($list as &$obj) {
            if (in_array($obj['id'], $validatingGroups)) {
                $groupsArray[$obj['id']] = $obj;
            }
            
            $childrenArray = Manager::getService('Groups')->fetchAllChildren($obj['id']);
            
            foreach ($childrenArray as $child) {
                if (in_array($child['id'], $validatingGroups)) {
                    $groupsArray[$child['id']] = $child;
                }
            }
        }
        
        return array_values($groupsArray);
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
            
            if (! in_array($obj['workspace'], $writeWorkspaces) || ! $aclServive->hasAccess("write.ui.groups")) {
                $obj['readOnly'] = true;
            }
        }
        
        return $obj;
    }

    public function getPublicGroup ()
    {
        return $this->findByName('public');
    }

    public function findByName ($name)
    {
        $filter = Filter::Factory('Value')->setValue($name)->setName('name');
        return $this->_dataService->findOne($filter);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Collection\IGroups::getReadWorkspaces()
     */
    public function getReadWorkspaces ($groupId)
    {
        $group = $this->findById($groupId);
        if (! isset($group['readWorkspaces'])) {
            $group['readWorkspaces'] = array(
                'global'
            );
        } else {
            $group['readWorkspaces'][] = 'global';
        }
        return $group['readWorkspaces'];
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Collection\IGroups::getMainWorkspace()
     */
    public function getMainWorkspace ($groupId)
    {
        $group = $this->findById($groupId);
        if (! isset($group['defaultWorkspace']) || $group['defaultWorkspace'] == "") {
            $group['defaultWorkspace'] = 'global';
        }
        return Manager::getService('Workspaces')->findById($group['defaultWorkspace']);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Collection\IGroups::getWriteWorkspaces()
     */
    public function getWriteWorkspaces ($groupId)
    {
        $group = $this->findById($groupId);
        if (! isset($group['writeWorkspaces']) || $group['writeWorkspaces'] == "") {
            $group['writeWorkspaces'] = array();
        }
        return $group['writeWorkspaces'];
    }

    public function clearOrphanGroups ()
    {
        $groupsIdArray = array(
            'root'
        );
        $orphansArray = array();
        $orphansIdArray = array();
        
        $groupsArray = $this->getList();
        
        // recovers the list of contentTypes id
        foreach ($groupsArray['data'] as $value) {
            $groupsIdArray[] = $value['id'];
        }
        $filters = Filter::Factory();
        $filters->addFilter(Filter::Factory('NotIn')->setName('parentId')
            ->setValue($groupsIdArray));
        $orphansArray = $this->getList($filters);
        
        foreach ($orphansArray['data'] as $value) {
            $orphansIdArray[] = $value['id'];
        }
        
        $result = $this->_deleteByArrayOfId($orphansIdArray);
        
        if ($result['ok'] == 1) {
            return array(
                'success' => 'true'
            );
        } else {
            return array(
                'success' => 'false'
            );
        }
    }

    protected function _deleteByArrayOfId ($arrayId)
    {
        $deleteArray = array();
        foreach ($arrayId as $stringId) {
            $deleteArray[] = $this->_dataService->getId($stringId);
        }
        return $this->_dataService->customDelete(array(
            '_id' => array(
                '$in' => $deleteArray
            )
        ));
    }

    public function countOrphanGroups ()
    {
        $groupsArray = array();
        $groupsIdArray = array(
            'root'
        );
        $orphansArray = array();
        $orphansIdArray = array();
        
        $groupsArray = $this->getList();
        
        // recovers the list of contentTypes id
        foreach ($groupsArray['data'] as $value) {
            $groupsIdArray[] = $value['id'];
        }
        
        $filters = Filter::Factory();
        $filters->addFilter(Filter::Factory('NotIn')->setName('parentId')
            ->setValue($groupsIdArray));
        $orphansArray = $this->getList($filters);
        
        foreach ($orphansArray['data'] as $value) {
            $orphansIdArray[] = $value['id'];
        }
        
        return count($orphansIdArray);
    }

    public function clearUserFromGroups ($userId)
    {
        $options = array(
            'multiple' => true
        );
        $data = array(
            '$unset' => array(
                'members.$' => ''
            )
        );
        $updateCond = Filter::Factory('Value')->setName('members')->setValue($userId);
        $this->_dataService->customUpdate($data, $updateCond, $options);
    }

    public function addUserToGroupList ($userId, $groupIdList)
    {
        $inArray = array();
        foreach ($groupIdList as $groupId) {
            $inArray[] = $this->_dataService->getId($groupId);
        }
        
        $options = array(
            'multiple' => true
        );
        $data = array(
            '$push' => array(
                'members' => $userId
            )
        );
        $updateCond = Filter::Factory('InUid')->setValue($inArray);
        $this->_dataService->customUpdate($data, $updateCond, $options);
    }

    protected function _initObject ($obj)
    {
        // set inheritance for workspace
        if (! isset($obj['inheritWorkspace']) || $obj['inheritWorkspace'] !== false) {
            $obj['inheritWorkspace'] = true;
        }
        // resolve inheritance if not forced
        if ($obj['inheritWorkspace']) {
            unset($obj['workspace']);
            $ancestorsLine = array_reverse($this->getAncestors($obj));
            foreach ($ancestorsLine as $ancestor) {
                if (isset($ancestor['inheritWorkspace']) && $ancestor['inheritWorkspace'] == false) {
                    $obj['workspace'] = $ancestor['workspace'];
                    break;
                }
            }
            if (! isset($obj['workspace'])) {
                $obj['workspace'] = 'global';
            }
        }
        // verify workspace can be attributed
        if (! self::isUserFilterDisabled()) {
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
            
            if (! in_array($obj['workspace'], $writeWorkspaces)) {
                throw new \Rubedo\Exceptions\Access('You can not assign group to this workspace', "Exception42");
            }
        }
        return $obj;
    }

    public function propagateWorkspace ($parentId, $workspaceId)
    {
        $filters = array();
        
        $pageList = $this->readChild($parentId, $filters);
        foreach ($pageList as $group) {
            if (! self::isUserFilterDisabled()) {
                if (! $group['readOnly']) {
                    if ($group['workspace'] != $workspaceId) {
                        $this->update($group);
                    }
                }
            } else {
                if ($group['workspace'] != $workspaceId) {
                    $this->update($group);
                }
            }
        }
    }
}
