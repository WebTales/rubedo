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
namespace Rubedo\Elastic;

use Rubedo\Interfaces\Elastic\IDataSearch;

/**
 * Class implementing the Rubedo API to Elastic Search using Elastica API
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataSearch implements IDataSearch
{

    /**
     * Default value of hostname
     *
     * Used by the constructor if no specific params
     *
     * @var string
     */
    private static $_defaultHost;
	
    /**
     * Default transport value
     *
     * Used by the constructor if no specific params
     *
     * @var string
     */
    private static $_defaultTransport;


    /**
     * Default port value
     *
     * Used by the constructor if no specific params
     *
     * @var string
     */
    private static $_defaultPort;

    /**
     * Elastica Client
     *
     * @var \Elastica_Client
     */
    private $_client;
	
    /**
     * Configuration options
     *
     * @var array
     */
    private static $_options;

    /**
     * Object which represent the content ES index
     *
     * @var \Elastica_Index
     */
    private static $_content_index = "content";

    /**
     * Object which represent the default ES index param
     *
     * @var \Elastica_Index
     */
    // TODO : get params into .ini
    private static $_content_index_param = array('index' => array(
		'number_of_shards' => 1, 
		'number_of_replicas' => 0 ));
		
    /**
     * Object which represent the document ES index
     *
     * @var \Elastica_Index
     */
    private static $_document_index = "document";

    /**
     * Object which represent the default document ES index param
     *
     * @var \Elastica_Index
     */
     // TODO : get params into .ini
    private static $_document_index_param = array('index' => array(
		'number_of_shards' => 1, 
		'number_of_replicas' => 0 ));
	
    /**
     * Initialize a search service handler to index or query Elastic Search
     *
	 * @see \Rubedo\Interfaces\IDataSearch::init()
     * @param string $host http host name
     * @param string $port http port 
     */
    public function init($host = null, $port= null)
    {
        if (is_null($host)) {
            $host = self::$_options['host'];
        }

        if (is_null($port)) {
            $port = self::$_options['port'];
        }

        $this->_client = new \Elastica_Client();
		
		$this->_content_index = $this->_client->getIndex(self::$_options['contentIndex']);
		
		// Create content index if not exists
		if (!$this->_content_index->exists()) {
			$this->_content_index->create(self::$_content_index_param,true);
		}
		
		$this->_document_index = $this->_client->getIndex(self::$_options['documentIndex']);
		
		// Create document index if not exists
		if (!$this->_document_index->exists()) {
			$this->_document_index->create(self::$_document_index_param,true);
		}
    }

	 /**
     * Set the main hostname for ES connection
     *
     * @param string $host
     */
    public static function setOptions(array $options) {
        self::$_options = $options;
    }

    /**
     * Create ES type for new content type
     *     
	 * @see \Rubedo\Interfaces\IDataSearch::createContentType()
	 * @param string $id content type id
	 * @param array $data new content type
     * @return array
     */
    public function createContentType ($contentType, $overwrite=false) {
    	
		// Unicity type id check
		$id = $contentType["id"];
		$type = $contentType["type"];

		$mapping = $this->_content_index->getMapping();
		if (array_key_exists($id,$mapping[self::$_content_index])) {
			if (!$overwrite) {
				// throw exception
				throw new \Exception("$type type already exists");
			} else {
				// delete existing content type
				$this->deleteContentType($id);
			}
		}

		// Create mapping
		$indexMapping = array();
		
		foreach($contentType["fields"] as $field) {

			if ($field['config']['searchable']) {
				//print_r($field);
				$name = $field['config']['name'];
				if ($field['config']['resumed']) {
					$store = 'yes';
				} else {
					$store = 'no';
				}				
				switch($field['cType']) {
					case 'checkbox' :
						$indexMapping[$name] = array('type' => 'string', 'store' => $store);
						break;
					case 'combo' :
						$indexMapping[$name] = array('type' => 'string', 'store' => $store);
						break;
					case 'datefield' :
						$indexMapping[$name] = array('type' => 'date', 'format' => 'yyyy-MM-dd', 'store' => $store);
						break;
					case 'field' :
						$indexMapping[$name] = array('type' => 'string', 'store' => $store);
						break;
					case 'htmleditor' :
						$indexMapping[$name] = array('type' => 'string', 'store' => $store);
						break;
					case 'CKEField' :
						$indexMapping[$name] = array('type' => 'string', 'store' => $store);
						break;
					case 'numberfield' :
						$indexMapping[$name] = array('type' => 'string', 'store' => $store);
						break;
					case 'radio' :
						$indexMapping[$name] = array('type' => 'string', 'store' => $store);
						break;
					case 'textareafield' :
						$indexMapping[$name] = array('type' => 'string', 'store' => $store);
						break;
					case 'textfield' :
						$indexMapping[$name] = array('type' => 'string', 'store' => $store);
						break;
					case 'timefield' :
						$indexMapping[$name] = array('type' => 'string', 'store' => $store);
						break;
					case 'ratingField' :
						$indexMapping[$name] = array('type' => 'string', 'store' => $store);
						break;
					case 'slider' :
						$indexMapping[$name] = array('type' => 'string', 'store' => $store);
						break;
					case 'document' :
						$indexMapping[$name] = array('type' => 'attachment', 'store' => 'no');
						break;
					default :
						throw new \Exception("unknown ".$field['cType']." type");
						break;
				}
			}
		}	
		
		// If there is no searchable field, the new type is not created
		if (!empty($indexMapping)) {
			// Create new type
			$type = new \Elastica_Type($this->_content_index, $id);
			
			// Set mapping
			$type->setMapping($indexMapping);
			//print_r($indexMapping);
			return array_flip(array_keys($indexMapping));
		} else {
			return array();
		}
    }
	
    /**
     * Update ES type for existing content type
     *     
	 * @see \Rubedo\Interfaces\IDataSearch::updateContentType()
	 * @param string $id content type id
	 * @param array $data new content type data
     * @return array
     */
    public function updateContentType ($id, $data) {
    	
    }
	
    /**
     * Delete ES type for existing content type
     *     
	 * @see \Rubedo\Interfaces\IDataSearch::deleteContentType()
	 * @param string $id content type id
     * @return array
     */
    public function deleteContentType ($id) {
    	
    	$type = new \Elastica_Type($this->_content_index, $id);
    	$type->delete();
		
    }
	
    /**
     * Index new content
     *    
	 * @see \Rubedo\Interfaces\IDataSearch::createContent()
	 * @param string $id new content id
	 * @param string $type new content type
	 * @param array $data new content data
     * @return array
     */
    public function createContent ($id, $type, $data) {
    		
		// Load content type 
    	$contentType = $this->_content_index
    						->getType($type);
							
		// Build content document to index	
		$contentData = array();
		
		foreach($data as $field => $var) {
			$contentData[$field] = (string) $var;
		}
		$contentData['type'] = (string) $type;
		//$currentDocument = new \Elastica_Document($lang.'_'.$id, $contentData);
		$currentDocument = new \Elastica_Document($id, $contentData);
		
		if (isset($contentData['attachment']) && $contentData['attachment'] != '') {
			$currentDocument->addFile('file', $contentData['attachment']);
		}
		
		// Add content to content type index
		$contentType->addDocument($currentDocument);

		// Refresh index
		$contentType->getIndex()->refresh();    
    	
    }
	
    /**
     * Update index for existing content
     *     
	 * @see \Rubedo\Interfaces\IDataSearch::updateContent()
	 * @param string $id content id
	 * @param array $data new content data
     * @return array
     */
    public function updateContent ($id, $data) {
    	
    }
	
    /**
     * Delete existing content from index
     *     
	 * @see \Rubedo\Interfaces\IDataSearch::deleteContent()
	 * @param string $id content id
     * @return array
     */
    public function deleteContent ($id) {
    	
    }
	
    /**
     * Index new DAM document
     *   
	 * @see \Rubedo\Interfaces\IDataSearch::createDocument()
	 * @param string $id document id  
	 * @param array $data new document data
     * @return array
     */
    public function createDocument ($id,$data) {
    	
    }
	
    /**
     * Update index for existing DAM document
     *     
	 * @see \Rubedo\Interfaces\IDataSearch::updateDocument()
	 * @param string $id document id  
	 * @param array $data new document data
     * @return array
     */
    public function updateDocument ($id, $data) {
    	
    }
	
    /**
     * Delete index type for existing DAM document
     *     
	 * @see \Rubedo\Interfaces\IDataSearch::deleteDocument()
	 * @param string $id document id  
     * @return array
     */
    public function deleteDocument ($id) {
    	
    }

    /**
     * Reindex all content
     *      
     * @return array
     */
    public function indexAllContent () {
			
		$ct = new \Rubedo\Mongo\DataAccess();
		$ct->init("ContentTypes");
			
		$result = array();
				
		// For every content type
		$contentTypeList = $ct->read();
		
		foreach($contentTypeList as $contentType) {
			// Create content type with overwrite set to true
			$fieldsToIndex = $this->createContentType($contentType,TRUE);
			if (!empty($fieldsToIndex)) {
				// If mapping completed, we can index content
				$c = new \Rubedo\Mongo\DataAccess();
				$c->init("Contents");
				// Get content from type
				$filter = array("typeId"=>$contentType["id"]);
				$c->addFilter($filter);
				$contentList = $c->read();
				$contentCount = 0;
				foreach($contentList as $content) {
					// Only searchable fields get indexed
					$data = array_intersect_key($content["fields"], $fieldsToIndex);
					// Push content to index
					$this->createContent($contentType["id"], $contentType["id"], $data);
					$contentCount++;
				}
				$result[$contentType["type"]]=$contentCount;
				
			}
		}
		
		return($result);
    }
	
}
