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

use Rubedo\Interfaces\Collection\ITaxonomyTerms;

/**
 * Service to handle TaxonomyTerms
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class TaxonomyTerms extends AbstractCollection implements ITaxonomyTerms {

    public function __construct() {
        $this->_collectionName = 'TaxonomyTerms';
        parent::__construct();
    }
	
	protected static $_termsArray = array();

    public function getChildrens($parentId) {

        $terms = $this->readChild($vocabularyId);

        foreach ($terms as $key => $value) {
            $this->deleteChild($terms);
        }

        $result = $this->_dataService->destroy($data);

        if ($result['success'] == true) {

        }
    }

    /**
     * Delete objets in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::destroy
     * @param array $obj data object
     * @param bool $options should we wait for a server response
     * @return array
     */
    public function destroy(array $obj, $options = array('safe'=>true)) {
        $deleteCond = array('_id' => array('$in' => $this->_getChildToDelete($obj['id'])));
		
        $resultArray = $this->_dataService->customDelete($deleteCond);

        if ($resultArray['ok'] == 1) {
            if ($resultArray['n'] > 0) {
                $returnArray = array('success' => true);
            } else {
                $returnArray = array('success' => false, "msg" => 'no record had been deleted');
            }

        } else {
            $returnArray = array('success' => false, "msg" => $resultArray["err"]);
        }
        return $returnArray;
    }

    /**
     *
     * @param string $id id whose children should be deleted
     * @return array array list of items to delete
     */
    protected function _getChildToDelete($id) {
        //delete at least the node
        $returnArray = array($this->_dataService->getId($id));

        //read children list
        $terms = $this->readChild($id);

        //for each child, get sublist of children
        if (is_array($terms)) {
            foreach ($terms as $key => $value) {
                $returnArray = array_merge($returnArray, $this->_getChildToDelete($value['id']));
            }
        }

        return $returnArray;

    }
	
	public function getTerm($id){
		if(!isset(self::$_termsArray[$id])){
			$term = $this->findById($id);
			self::$_termsArray[$id]=$term['text'];
		}
		return self::$_termsArray[$id];
	}

	public function deleteByVocabularyId($id){
		$deleteCond = array('vocabularyId'=>$id);
		return $this->customDelete($deleteCond);
	}

}
