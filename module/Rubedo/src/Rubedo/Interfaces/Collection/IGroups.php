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
namespace Rubedo\Interfaces\Collection;

/**
 * Interface of service handling Groups
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IGroups extends IAbstractCollection
{

    /**
     * return "can read" workspaces
     *
     * @param string $groupId            
     * @return array
     */
    public function getReadWorkspaces ($groupId);

    /**
     * return main workspace
     *
     * @param string $groupId            
     * @return array
     */
    public function getMainWorkspace ($groupId);

    /**
     * return "can write" workspaces
     *
     * @param string $groupId            
     * @return array
     */
    public function getWriteWorkspaces ($groupId);

    public function getListByUserId ($userId);

    public function getValidatingGroupsId ();

    public function getPublicGroup ();

    public function getValidatingGroupsForWorkspace ($workspace);

    public function clearOrphanGroups ();

    public function countOrphanGroups ();

    public function clearUserFromGroups ($userId);

    public function addUserToGroupList ($userId, $groupIdList);

    public function propagateWorkspace ($parentId, $workspaceId);
}
