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

use Rubedo\Interfaces\Elastic\IDataIndex;

/**
 * Class implementing the Rubedo API to Elastic Search indexing services using Elastica API
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataIndex implements IDataIndex
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
    private static $_content_index;

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
	 * @see \Rubedo\Interfaces\IDataIndex::init()
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

        $this->_client = new \Elastica_Client(array('port'=>$port,'host'=>$host));
		
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
     * Set the options for ES connection
     *
     * @param string $host
     */
    public static function setOptions(array $options) {
        self::$_options = $options;
    }

    /**
     * Get ES type structure
     *     
	 * @param string $id content type id
     * @return array
     */
    public function getTypeStructure ($id) {
    	
		$returnArray=array();
		$searchableFields=array('lastUpdateTime','text','type','author');
    	
		// Get content type config by id
		$c = new \Rubedo\Mongo\DataAccess();
		$c->init("ContentTypes");
		$contentTypeConfig = $c->findById($id);

		// Search abstract field
		$abstract="";
		$fields=$contentTypeConfig["fields"];
		foreach($fields as $field) {
			if ($field['config']['resumed']) {
				$abstract = $field['config']['name'];
			}
			if ($field['config']['searchable']) {
				$searchableFields[] = $field['config']['name'];
			}	
		}    
		
		$returnArray['abstract']=$abstract;
		$returnArray['searchableFields']=$searchableFields;
		return $returnArray;	
    }
		
    /**
     * Index ES type for new or updated content type
     *     
	 * @see \Rubedo\Interfaces\IDataIndex:indexContentType()
	 * @param string $id content type id
	 * @param array $data new content type
     * @return array
     */
    public function indexContentType($id, $data, $overwrite=false) {
    	
		// Unicity type id check

		$mapping = $this->_content_index->getMapping();
		if (array_key_exists($id,$mapping[self::$_options['contentIndex']])) {
			if (!$overwrite) {
				// throw exception
				throw new \Exception("$id type already exists");
			} else {
				// delete existing content type
				$this->deleteContentType($id);
			}
		}

		// Create mapping
		$indexMapping = array();
		
		// If there is any fields get them mapped
		if (is_array($data["fields"])) {

			foreach($data["fields"] as $key => $field) {
				
				// Only searchable fields get indexed
				if ($field['config']['searchable']) {
					
					$name = $field['config']['fieldLabel'];
					
					//print_r($field);
					if ($field['config']['resumed']) {
						$store = 'yes';
						$name = 'abstract';
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
							$indexMapping[$name] = array('type' => 'string', 'store' => '$store');
							break;
					}
				}
			}	
		}
		
		// Add systems metadata : TODO update model text to title	
		$indexMapping["lastUpdateTime"] = array('type' => 'date', 'format' => 'YYYY-mm-dd', 'store' => 'yes');
		$indexMapping["text"] = array('type' => 'string', 'store' => 'yes');
		$indexMapping["author"] = array('type' => 'string', 'index'=> 'not_analyzed', 'store' => 'yes');
		$indexMapping["type"] = array('type' => 'string', 'index'=> 'not_analyzed', 'store' => 'yes');
		
		// If there is no searchable field, the new type is not created
		if (!empty($indexMapping)) {
			// Create new type
			$type = new \Elastica_Type($this->_content_index, $id);
			
			// Set mapping
			$type->setMapping($indexMapping);
			
			// Return indexed field list
			return array_flip(array_keys($indexMapping));
		} else {
			return array();
		}
    }
	
    /**
     * Delete ES type for existing content type
     *     
	 * @see \Rubedo\Interfaces\IDataIndex::deleteContentType()
	 * @param string $id content type id
     * @return array
     */
    public function deleteContentType ($id) {
    	
    	$type = new \Elastica_Type($this->_content_index, $id);
    	$type->delete();
		
    }
	
    /**
     * Create or update index for existing content
     *    
	 * @see \Rubedo\Interfaces\IDataIndex::indexContent()
	 * @param string $id new content id
	 * @param string $typeId new content type id
	 * @param array $data new content data
     * @return array
     */
    public function indexContent ($id, $typeId = null, $data = null) {

		// retrieve type id and content data if null
		if (is_null($typeId)) {
			$c = \Rubedo\Services\Manager::getService('MongoDataAccess');
			$c->init("Contents");	
			$data = $c->findById($id);
			$typeId = $data['typeId'];
		}
		
		// Retrieve type label
		$ct = \Rubedo\Services\Manager::getService('MongoDataAccess');
		$ct->init("ContentTypes");	
		//$filter = array("id"=>$typeId);		
		//$ct->addFilter($filter);
		//$contentType = $ct->read();
		$contentType = $ct->findById($typeId);
		$type = $contentType['type'];
					
		// Load ES type 
    	$contentType = $this->_content_index
    						->getType($typeId);
		
		// Get content type structure
		$typeStructure = $this->getTypeStructure($typeId);
	
		// Add fields to index	
		$contentData = array();
		//print_r($data);
		foreach($data['workspace']['fields'] as $field => $var) {

			// Add abstract if exists
			if ($field==$typeStructure['abstract']) {
				$contentData["abstract"] = (string) $var;
			} else {
				// only index searchable fields
				if (in_array($field,$typeStructure['searchableFields']))  {	
					$contentData[$field] = (string) $var;
				}
			}
			// Date format fix
			if ($field=="lastUpdateTime") $contentData[$field] = date("Y-m-d", (int) $var);
		}

		// Add default meta's
		$contentData['type'] = $type;
		$contentData['lastUpdateTime'] = (string) $data['lastUpdateTime'];
		$contentData['status'] = (string) $data['workspace']['status'];
		$contentData['author'] = (string) $data['lastUpdateUser']['fullName'];

		//print_r($contentData);
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
     * Delete existing content from index
     *     
	 * @see \Rubedo\Interfaces\IDataIndex::deleteContent()
	 * @param string $typeId content type id
	 * @param string $id content id
     * @return array
     */
    public function deleteContent ($typeId, $id) {
     	$type = new \Elastica_Type($this->_content_index, $typeId);
    	$type->deleteById($id);   	
    }
	
    /**
     * Index DAM document
     *   
	 * @see \Rubedo\Interfaces\IDataIndex::indexDocument()
	 * @param string $id document id  
	 * @param array $data new document data
     * @return array
     */
    public function indexDocument ($id,$data) {
    	
    }
	
    /**
     * Delete index type for existing DAM document
     *     
	 * @see \Rubedo\Interfaces\IDataIndex::deleteDocument()
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
    	
		// Initialize result array
		$result = array();
		
		// Destroy and re-create content and document index
		$this->_content_index->delete();
		$this->_content_index->create(self::$_content_index_param,true);
		$this->_document_index->delete();
		$this->_document_index->create(self::$_document_index_param,true);	
			
		// Retreive all content types
		$ct = \Rubedo\Services\Manager::getService('MongoDataAccess');
		$ct->init("ContentTypes");
		$contentTypeList = $ct->read();
		
		foreach($contentTypeList as $contentType) {
			// Create content type with overwrite set to true
			$this->indexContentType($contentType["id"],$contentType,TRUE);
			// Index all contents from type
			$c = \Rubedo\Services\Manager::getService('MongoDataAccess');
			$c->init("Contents");	
			$filter = array("typeId"=>$contentType["id"]);
			$c->addFilter($filter);
			$contentList = $c->read();
			$contentCount = 0;
			foreach($contentList as $content) {
				$this->indexContent($content["id"]);
				$contentCount++;
			}
			$result[$contentType["type"]]=$contentCount;
			
		}
		return($result);

    }
	
}
