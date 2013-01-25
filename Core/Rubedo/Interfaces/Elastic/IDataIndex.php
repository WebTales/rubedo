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
     * @param string $host http host name
     * @param string $port http port 
     */
    public function init ($host = null, $port= null);

    /**
     * Get ES content type structure
     *     
	 * @param string $id content type id
     * @return array
     */
    public function getContentTypeStructure ($id);

    /**
     * Get ES DAM type structure
     *     
	 * @param string $id content type id
     * @return array
     */
    public function getDamTypeStructure ($id);


    /**
     * Index ES type for new or updated content type
     *     
	 * @param string $id content type id
	 * @param array $data new content type
     * @param boolean $overwrite overwrite content type if it exists
     * @return array
     */
    public function IndexContentType ($id, $data,$overwrite=false);
	
    /**
     * Delete ES type for new content type
     *     
	 * @param string $id content type id
     * @return array
     */
    public function deleteContentType ($id);
	
    /**
     * Create or update index for existing content
     *    
	 * @param string $id content id
	 * @param boolean $live live if true, workspace if live
     * @return array
     */
	public function indexContent ($id, $live = true);
	
    /**
     * Delete existing content from index
	 * 
	 * @param string $typeId content type id
	 * @param string $id content id
     * @return array
     */
    public function deleteContent ($typeId, $id);
	
    /**
     * Index DAM document
     *   
	 * @param string $id document id  
	 * @param array $data new document data
     * @return array
     */
    public function indexDocument ($id,$data);
	
    /**
     * Delete index type for existing DAM document
     *     
	 * @param string $id document id  
     * @return array
     */
    public function deleteDocument ($id);

    /**
     * Reindex all content
     *      
     * @return array
     */
    public function indexAllContent ();
		
}