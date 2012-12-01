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
     * Get ES type structure
     *     
	 * @param string $id content type id
     * @return array
     */
    public function getTypeStructure ($id);

    /**
     * Index ES type for new or updated content type
     *     
	 * @param string $id content type id
	 * @param array $data new content type
     * @param boolean $overwrite overwrite content type if it exists
     * @return array
     */
    public function IndexContentType ($id, $data,$overwrite);
	
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
	 * @param string $typeId new content type id
	 * @param array $data new content data
     * @return array
     */
    public function indexContent ($id, $typeId, $data);
	
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