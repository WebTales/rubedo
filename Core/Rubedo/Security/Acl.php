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
namespace Rubedo\Security;

use Rubedo\Interfaces\Security\IAcl;
/**
 * Interface of Access Control List Implementation
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Acl implements  IAcl
{

    /**
     * Check if the current user has access to a given resource for a given access mode
     *
     * @param string $resource resource name
     * @return boolean
     */
    public function hasAccess($resource) {
		
		$currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
		$groups = $currentUserService->getGroups();
		
		foreach($groups as $group){
			if($this->groupHasAccess($resource, $group)){
				return true;
			}
		}

        return false;
    }
	
	/**
	 * 
	 * @todo real access implementation
	 */
	protected function groupHasAccess($resource, $groupId){
		if(strpos($resource,'execute')!==false){
			return true;
		}
		
		$aclArray = array();
		
		$aclArray['public']=array();
		$aclArray['redacteur']=array('read.ui.contents',
										'write.ui.contents',
										'read.ui.contents.draft',
										'read.ui.contents.pending',
										'read.ui.contents.published',
										'write.ui.contents.draft',
										'write.ui.contents.draftToPending');
		$aclArray['valideur']=array(	'read.ui.contents',
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
										'write.ui.contents.putOffline');
		$aclArray['admin']=array(		'read.ui.taxonomy',
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
										//'read.ui.pages',
										//'write.ui.pages',
										//'read.ui.medias',
										//'write.ui.medias',
										'read.ui.groups',
										'write.ui.groups',
										//'read.ui.workflows',
										//'write.ui.workflows'
										);


		if(in_array($resource, $aclArray[$groupId])){
			return true;
		}else{
			return false;
		}
				
	}

    /**
     * For a given list of ressource, build an array of authorized ressources
     * @param array $ressourceArray array of ressources
     * @return array the array of boolean with ressource as key name
     */
    public function accessList(array $ressourceArray) {
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
