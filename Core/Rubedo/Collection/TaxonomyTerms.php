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
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function destroy(array $obj, $safe = true) {
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

}
