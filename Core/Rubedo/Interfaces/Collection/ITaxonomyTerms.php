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
namespace Rubedo\Interfaces\Collection;

/**
 * Interface of service handling TaxonomyTerms
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface ITaxonomyTerms extends IAbstractCollection{
	
	/**
     * Delete objects in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::destroy
     * @param array $obj data object
     * @param bool $options should we wait for a server response
     * @return array
     */
    public function destroy(array $obj, $options = array('safe'=>true));
	
	/**
     *
     * @param string $id id whose children should be deleted
     * @return array array list of items to delete
     */
    protected function _getChildToDelete($id);
	
	/**
	 * Allow to find a term by its id
	 * 
	 * @param string $id id of the term
	 * @return array Contain the term
	 */
	public function getTerm($id);
	
	/**
	 * Clear orphan terms in the collection
	 * 
	 * @return array Result of the request
	 */
	public function clearOrphanTerms();
	
	/**
	 * Allow to find terms by their vocabulary
	 * 
	 * @param string $vocabularyId Contain the id of the vocabulary
	 * @return array Contain the terms associated to the vocabulary given in parameter
	 */
	public function findByVocabulary($vocabularyId);
	
}
