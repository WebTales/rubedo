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

use Rubedo\Interfaces\Collection\IGroups;

/**
 * Service to handle Groups
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Groups extends AbstractCollection implements IGroups
{

    public function __construct ()
    {
        $this->_collectionName = 'Groups';
        parent::__construct();
    }

    public function getListByUserId ($userId)
    {
        $filters = array();
        $filters[] = array(
            'property' => "members",
            'value' => $userId
        );
        $groupList = $this->getListWithAncestors($filters);
        
        return $groupList;
    }

    public function getPublicGroup ()
    {
        return $this->findByName('public');
    }

    public function findByName ($name)
    {
        return $this->_dataService->findOne(array(
            'name' => $name
        ));
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
        $groupArray = $this->getWriteWorkspaces($groupId);
        
        return array_shift($groupArray);
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \Rubedo\Interfaces\Collection\IGroups::getWriteWorkspaces()
     */
    public function getWriteWorkspaces ($groupId)
    {
        $group = $this->findById($groupId);
        if (! isset($group['writeWorkspaces'])) {
            $group['writeWorkspaces'] = array();
        }
        return $group['writeWorkspaces'];
    }
}
