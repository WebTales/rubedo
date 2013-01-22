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
namespace Rubedo\Security;

use Rubedo\Interfaces\Security\IAcl, Rubedo\Services\Manager;

/**
 * Interface of Access Control List Implementation
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Acl implements IAcl
{

    /**
     * Check if the current user has access to a given resource for a given
     * access mode
     *
     * @param string $resource
     *            resource name
     * @return boolean
     */
    public function hasAccess ($resource)
    {
        $currentUserService = Manager::getService('CurrentUser');
        $groups = $currentUserService->getGroups();
        $roleArray = array();
        foreach ($groups as $group) {
            $roleArray = $this->_addGroupToRoleArray($roleArray, $group);
        }
        
        foreach ($roleArray as $role) {
            if ($this->_roleHasAccess($resource, $role)) {
                return true;
            }
        }
        
        return false;
    }

    protected function _addGroupToRoleArray (array $roleArray, array $group)
    {
        if (! isset($group['roles'])) {
            $group['roles'] = $this->_getRole($group);
        }
        if (isset($group['roles']) && is_array($group['roles'])) {
            $groupRoleArray = array_values($group['roles']);
            $roleArray = array_merge($roleArray, $groupRoleArray);
        }
        return $roleArray;
    }

    protected function _roleHasAccess ($resource, $role)
    {
        if (is_null($role)) {
            return false;
        }
        
        if (strpos($resource, 'execute') !== false) {
            if (strpos($resource, 'backoffice') !== false && $role == 'public' && (strpos($resource, 'index') === false && strpos($resource, 'login') === false)) {
                return false;
            }
            return true;
        }
        
        $aclArray = array();
        
        $aclArray['public'] = array();
        $aclArray['redacteur'] = array(
            'read.ui.contents',
            'write.ui.contents',
            'read.ui.contents.draft',
            'read.ui.contents.pending',
            'read.ui.contents.published',
            'write.ui.contents.draft',
            'write.ui.contents.draftToPending'
        );
        $aclArray['valideur'] = array(
            'read.ui.contents',
            'write.ui.contents',
            'read.ui.contents.draft',
            'read.ui.contents.pending',
            'read.ui.contents.published',
            'write.ui.contents.draft',
            'write.ui.contents.pending',
            'write.ui.contents.published',
            'write.ui.contents.draftToPending',
            'write.ui.contents.pendingToDraft',
            'write.ui.contents.pendingToPublished',
            'write.ui.contents.putOnline',
            'write.ui.contents.putOffline',
            'read.ui.masks',
            'read.ui.users',
            'read.ui.contentTypes'
        );
        $aclArray['admin'] = array(
            'read.ui.taxonomy',
            'write.ui.taxonomy',
            'read.ui.contentTypes',
            'write.ui.contentTypes',
            'read.ui.contents',
            'write.ui.contents',
            'read.ui.contents.draft',
            'read.ui.contents.pending',
            'read.ui.contents.published',
            'write.ui.contents.draft',
            'write.ui.contents.pending',
            'write.ui.contents.published',
            'write.ui.contents.draftToPending',
            'write.ui.contents.pendingToDraft',
            'write.ui.contents.pendingToPublished',
            'write.ui.contents.putOnline',
            'write.ui.contents.putOffline',
            'read.ui.masks',
            'write.ui.masks',
            'read.ui.users',
            'write.ui.users',
            'read.ui.sites',
            'write.ui.sites',
            'exe.ui.elasticSearch',
            'read.ui.pages',
            'write.ui.pages',
            'read.ui.medias',
            'write.ui.medias',
            'read.ui.groups',
            'write.ui.groups'
        );
        // 'read.ui.workflows',
        // 'write.ui.workflows' ) ;
        
        if (! isset($aclArray[$role])) {
            return false;
        }
        if (in_array($resource, $aclArray[$role])) {
            return true;
        } else {
            return false;
        }
    }

    protected function _getRole ($group)
    {
        switch ($group['name']) {
            case 'admin':
                $roles = array(
                    'admin'
                );
                break;
            default:
                $roles = array(
                    'public'
                );
                break;
        }
        return $roles;
    }

    /**
     * For a given list of ressource, build an array of authorized ressources
     *
     * @param array $ressourceArray
     *            array of ressources
     * @return array the array of boolean with ressource as key name
     */
    public function accessList (array $ressourceArray)
    {
        $aclArray = array();
        if (isset($this->_service)) {
            $object = $this->_service;
        } else {
            $object = $this;
        }
        foreach ($ressourceArray as $value) {
            $aclArray[$value] = $object->hasAccess($value);
        }
        return $aclArray;
    }
}
