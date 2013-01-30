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
            foreach ($terms as $key => $value) {
                $returnArray = array_merge($returnArray, $this->_getChildToDelete($value['id']));
            }
        }
        
        return $returnArray;
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
    public function destroy (array $obj, $options = array('safe'=>true))
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
                    "msg" => 'no record had been deleted'
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
	
	public function clearOrphanGroups() {
		$pagesArray = array();	
		$pagesIdArray = array('root');
		$orphansArray = array();
		$orphansIdArray = array();
		
		$pagesArray = $this->getList();
		
		//recovers the list of contentTypes id
		foreach ($pagesArray['data'] as $value) {
			$pagesIdArray[] = $value['id'];
		}
		
		$orphansArray = $this->getList(array(array('property' => 'parentId', 'operator' => '$nin', 'value' => $pagesIdArray)));

		foreach ($orphansArray['data'] as $value) {
			$orphansIdArray[] = $value['id'];
		}

		$result = $this->_deleteByArrayOfId($orphansIdArray);

		if($result['ok'] == 1){
			return array('success' => 'true');
		} else {
			return array('success' => 'false');
		}
	}
	
	protected function _deleteByArrayOfId($arrayId){
		$deleteArray = array();
		foreach ($arrayId as $stringId) {
			$deleteArray[]=$this->_dataService->getId($stringId);
		}
		return $this->_dataService->customDelete(array('_id' => array('$in' => $deleteArray)));
		
	}
	
	public function countOrphanGroups() {
		$pagesArray = array();	
		$pagesIdArray = array('root');
		$orphansArray = array();
		$orphansIdArray = array();
		
		$pagesArray = $this->getList();
		
		//recovers the list of contentTypes id
		foreach ($pagesArray['data'] as $value) {
			$pagesIdArray[] = $value['id'];
		}
		
		$orphansArray = $this->getList(array(array('property' => 'parentId', 'operator' => '$nin', 'value' => $pagesIdArray)));

		foreach ($orphansArray['data'] as $value) {
			$orphansIdArray[] = $value['id'];
		}
		
		return count($orphansIdArray);
	}
}
