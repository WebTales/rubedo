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
 * Interface of service handling Contents
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface INestedContents
{

    /**
     * Do a find request on nested contents of a given content
     *
     * @param string $parentContentId parent id of nested contents
     * @return array
     */
    public function getList($parentContentId);

    /**
     * Create an objet in the current collection
     *
     * @param string $parentContentId parent id of nested contents
     * @param array $obj data object
     * @param bool $options should we wait for a server response
     * @return array
     */
    public function create($parentContentId, array $obj, $options = array('safe'=>true));

    /**
     * Update an objet in the current collection
     *
     * @param string $parentContentId parent id of nested contents
     * @param array $obj data object
     * @param bool $options should we wait for a server response
     * @return array
     */
    public function update($parentContentId, array $obj, $options = array('safe'=>true));

    /**
     * Delete objets in the current collection
     *
     * @param string $parentContentId parent id of nested contents
     * @param array $obj data object
     * @param bool $options should we wait for a server response
     * @return array
     */
    public function destroy($parentContentId, array $obj, $options = array('safe'=>true));
	
	/**
	 * Find a nested content by its id and its parentId
	 * 
	 * @param string $parentContentId id of the parent content
	 * @param string $subContentId id of the content we are looking for
	 */
    public function findById($parentContentId, $subContentId);

}
