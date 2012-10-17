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
namespace Rubedo\Interfaces\Elastic;

/**
 * Interface of data search services
 *
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
interface IDataSearch
{

    /**
     * Initialize a search service handler to index or search data
     *
     * @param string $host http host name
     * @param string $port http port 
     * @param string $index index name
     */
    public function init ($index = null, $host = null, $port= null);

    /**
     * Create ES type for new content type
     *     
	 * @param string $id content type id
	 * @param array $data new content type
     * @return array
     */
    public function createContentType ($id, $data);
	
    /**
     * Update ES type for new content type
     *     
	 * @param string $id content type id
	 * @param array $data new content type data
     * @return array
     */
    public function updateContentType ($id, $data);
	
    /**
     * Delete ES type for new content type
     *     
	 * @param string $id content type id
     * @return array
     */
    public function deleteContentType ($id);
	
    /**
     * Index new content
     *    
	 * @param string $id new content id
	 * @param string $type new content type
	 * @param array $data new content data
     * @return array
     */
    public function createContent ($id, $type, $data);
	
    /**
     * Update index for existing content
     *     
	 * @param string $id content id
	 * @param array $data new content data
     * @return array
     */
    public function updateContent ($id, $data);
	
    /**
     * Delete existing content from index
     *     
	 * @param string $id content id
     * @return array
     */
    public function deleteContent ($id);
	
    /**
     * Index new DAM document
     *   
	 * @param string $id document id  
	 * @param array $data new document data
     * @return array
     */
    public function createDocument ($id,$data);
	
    /**
     * Update index for existing DAM document
     *     
	 * @param string $id document id  
	 * @param array $data new document data
     * @return array
     */
    public function updateDocument ($id, $data);
	
    /**
     * Delete index type for existing DAM document
     *     
	 * @param string $id document id  
     * @return array
     */
    public function deleteDocument ($id);
	
}