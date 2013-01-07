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

    /**
     * Delete objects in the current collection
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
	
	/**
	 * Allow to find a term by its id
	 * 
	 * @param string $id id of the term
	 * @return array Contain the term
	 */
	public function getTerm($id){
		if(!isset(self::$_termsArray[$id])){
			$term = $this->findById($id);
			self::$_termsArray[$id]=$term['text'];
		}
		return self::$_termsArray[$id];
	}
	
	/**
	 * Clear orphan terms in the collection
	 * 
	 * @return array Result of the request
	 */
	public function clearOrphanTerms() {
		$taxonomy = \Rubedo\Services\Manager::getService('Taxonomy');
		$orphans = array();
		$erreur = false;
		
		$terms = $this->getList();
		$terms = $terms ['data'];
		
		foreach ($terms as $value) {
			if(isset($value['vocabularyId'])){
				$vocabulary = $taxonomy->findById($value['vocabularyId']);
				
				if(!$vocabulary){
					$orphans[] = $value;
				} else {
					if (isset($value['parentId'])) {
						if(!$value['parentId'] == "root") {
							$parent = $this->findById($value['parentId']);
							
							if(!$parent){
								$orphans[] = $value;
							}
						}
					} else {
						$orphans[] = $value;
					}
				}
			} else {
				$orphans[] = $value;
			}
		}
		
		foreach ($orphans as $value) {
			$result = $this->destroy($value);

			if(!$result['success']){
				$erreur = true;
			}
		}
				
		if(!$erreur) {
			$response['success'] = true;
			$response['data'] = $orphans;
		} else {
			$response['success'] = false;
		}
		
		return $response;
	}
	
	/**
	 * Allow to find terms by their vocabulary
	 * 
	 * @param string $vocabularyId Contain the id of the vocabulary
	 * @return array Contain the terms associated to the vocabulary given in parameter
	 */
	public function findByVocabulary($vocabularyId) {
		$filter = array("property" => "vocabularyId", "value" => $vocabularyId);	
		return $this->getList($filter);
	}

	public function deleteByVocabularyId($id){
		$deleteCond = array('vocabularyId'=>$id);
		return $this->customDelete($deleteCond);
	}

}
