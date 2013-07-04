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
namespace Rubedo\Interfaces\Elastic;

/**
 * Interface of data search indexing services
 *
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
interface IDataIndex
{

    /**
     * Initialize a search service handler to index data
     *
     * @param string $host
     *            http host name
     * @param string $port
     *            http port
     */
    public function init ($host = null, $port = null);

    /**
     * Get ES content type structure
     *
     * @param string $id
     *            content type id
     * @return array
     */
    public function getContentTypeStructure ($id);

    /**
     * Get ES DAM type structure
     *
     * @param string $id
     *            content type id
     * @return array
     */
    public function getDamTypeStructure ($id);

    /**
     * Index ES type for new or updated content type
     *
     * @param string $id
     *            content type id
     * @param array $data
     *            new content type
     * @param boolean $overwrite
     *            overwrite content type if it exists
     * @return array
     */
    public function indexContentType ($id, $data, $overwrite = false);

    /**
     * Index ES type for new or updated dam type
     *
     * @param string $id
     *            dam type id
     * @param array $data
     *            new dam type
     * @param boolean $overwrite
     *            overwrite dam type if it exists
     * @return array
     */
    public function indexDamType ($id, $data, $overwrite = false);

    /**
     * Index ES type for new or updated user type
     *
     * @param string $id
     *            user type id
     * @param array $data
     *            new user data
     * @param boolean $overwrite
     *            overwrite user type if it exists
     * @return array
     */
    public function indexUserType ($id, $data, $overwrite = false);
    
    /**
     * Delete ES type for content type
     *
     * @param string $id
     *            content type id
     * @return array
     */
    public function deleteContentType ($id);

    /**
     * Delete existing content from index
     *
     * @param string $typeId
     *            content type id
     * @param string $id
     *            content id
     * @return array
     */
    public function deleteContent ($typeId, $id);

    /**
     * Delete ES type for dam type
     *
     * @param string $id
     *            dam type id
     * @return array
     */
    public function deleteDamType ($id);

    /**
     * Delete existing dam from index
     *
     * @param string $typeId
     *            content type id
     * @param string $id
     *            content id
     * @return array
     */
    public function deleteDam ($typeId, $id);

    /**
     * Create or update index for existing content
     *
     * @param obj $data
     *            content data
     * @param boolean $live
     *            live if true, workspace if live
     * @return array
     */
    public function indexContent ($data);

    /**
     * Create or update index for existing Dam document
     *
     * @param obj $data
     *            dam data
     * @return array
     */
    public function indexDam ($data);

    /**
     * Reindex all content or dam
     * 
     * @param string $option
     *            : dam, content or all
     *            
     * @return array
     */
    public function indexAll ($option);

    /**
     * Reindex all content or dam for one type
     * 
     * @param string $option
     *            : dam or content
     * @param string $id
     *            : dam type or content type id
     *            
     * @return array
     */
    public function indexByType ($option, $id);
}