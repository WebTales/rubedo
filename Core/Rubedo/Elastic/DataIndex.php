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
namespace Rubedo\Elastic;

use Rubedo\Interfaces\Elastic\IDataIndex;

/**
 * Class implementing the Rubedo API to Elastic Search indexing services using Elastica API
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataIndex extends DataAbstract implements IDataIndex 
{

    /**
     * Get ES type structure
     *     
	 * @param string $id content type id
     * @return array
     */
    public function getContentTypeStructure ($id) {
    	
		$returnArray=array();
		$searchableFields=array('lastUpdateTime','text','summary','type','author');
    	
		// Get content type config by id
		$contentTypeConfig = \Rubedo\Services\Manager::getService('ContentTypes')->findById($id);

		// Get indexable fields
		$fields=$contentTypeConfig["fields"];
		foreach($fields as $field) {
			if ($field['config']['searchable']) {
				$searchableFields[] = $field['config']['name'];
			}	
		}    
		
		$returnArray['searchableFields']=$searchableFields;
		return $returnArray;	
    }

    /**
     * Get ES DAM type structure
     *     
	 * @param string $id DAM type id
     * @return array
     */
    public function getDamTypeStructure ($id) {
    	
		$returnArray=array();
		$searchableFields=array('lastUpdateTime','title','type','author');
    	
		// Get content type config by id
		$DamTypeConfig = \Rubedo\Services\Manager::getService('DamTypes')->findById($id);

		// Search summary field
		$fields=$DamTypeConfig["fields"];
		foreach($fields as $field) {
			if ($field['config']['searchable']) {
				$searchableFields[] = $field['config']['name'];
			}	
		}    
		
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
    public function indexContentType($id, $data, $overwrite=FALSE) {
    	
		// Unicity type id check
		$mapping = $this->_content_index->getMapping();
		if (array_key_exists($id,$mapping[self::$_options['contentIndex']])) {
			if ($overwrite) {
				// delete existing content type
				$this->deleteContentType($id);
			} else {
				// throw exception
				throw new \Exception("$id type already exists");
			}
		}
		
		// Get vocabularies for current content type
		$vocabularies=array();
		foreach($data['vocabularies'] as $vocabularyId) {
			$vocabulary = \Rubedo\Services\Manager::getService('Taxonomy')->findById($vocabularyId);	
			$vocabularies[] = $vocabulary['name'];
		}
		
		// Create mapping
		$indexMapping = array();
		
		// If there is any fields get them mapped
		if (is_array($data["fields"])) {

			foreach($data["fields"] as $key => $field) {
						
				// Only searchable fields get indexed
				if ($field['config']['searchable']) {
					
					$name = $field['config']['fieldLabel'];
					$store = "no";
								
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
		$indexMapping["lastUpdateTime"] = array('type' => 'date', 'store' => 'yes');
		$indexMapping["text"] = array('type' => 'string', 'store' => 'yes');
		$indexMapping["summary"] = array('type' => 'string', 'store' => 'yes');
		$indexMapping["author"] = array('type' => 'string', 'index'=> 'not_analyzed', 'store' => 'yes');
		$indexMapping["contentType"] = array('type' => 'string', 'index'=> 'not_analyzed', 'store' => 'yes');
		foreach($vocabularies as $vocabularyName) {
			$indexMapping["taxonomy.".$vocabularyName] = array('type' => 'string', 'index'=> 'not_analyzed', 'store' => 'no');
		}
		
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
	 * @param string $id content id
	 * @param boolean $live live if true, workspace if live
     * @return array
     */
	public function indexContent ($id, $live = false) {

	     // content data to index
	     if ($live) {
	            $space = "live";
	     } else {
	            $space = "workspace";
	     }
            
        // retrieve type id and content data if null
        $data = \Rubedo\Services\Manager::getService('Contents')->findById($id);
        $typeId = $data['typeId'];
		
		// Retrieve type label

		$contentType = \Rubedo\Services\Manager::getService('ContentTypes')->findById($typeId);
		$type = $contentType['type'];
					
		// Load ES type 
    	$contentType = $this->_content_index
    						->getType($typeId);
		
		// Get content type structure
		$typeStructure = $this->getContentTypeStructure($typeId);
	
		// Add fields to index	
		$contentData = array();
		foreach($data[$space]['fields'] as $field => $var) {

			// only index searchable fields
			if (in_array($field,$typeStructure['searchableFields']))  {	
				$contentData[$field] = (string) $var;
			}

			// Date format fix
			if ($field=="lastUpdateTime") $contentData[$field] = date("Y-m-d", (int) $var);
		}

		// Add default meta's
		$contentData['contentType'] = $type;
		if (isset($data['lastUpdateTime'])) {
			$contentData['lastUpdateTime'] = (string) $data['lastUpdateTime'];
		} else {
			$contentData['lastUpdateTime'] = 0;
		}
		if (isset($data['status'])) {
			$contentData['status'] = (string) $data[$space]['status'];
		} else {
			$contentData['status'] = "unknown";
		}
		if (isset($data['createUser'])) {
			$contentData['author'] = (string) $data['createUser']['fullName'];
		} else {
			$contentData['author'] = "unknown";
		}
        
        // Add taxonomy
         if (isset($data[$space]["taxonomy"])) {
                $tt = \Rubedo\Services\Manager::getService('TaxonomyTerms');
                foreach ($data[$space]["taxonomy"] as $vocabulary => $terms) {
                    if(!is_array($terms)){
                        continue;
                    }
					$collection = \Rubedo\Services\Manager::getService('Taxonomy');
					//$collection->init("Taxonomy");
					$taxonomy = $collection->findById($vocabulary);
					$termsArray = array();
								
                    foreach ($terms as $term) {
                    	$term = $tt->findById($term);
                    	if(!$term){
                    	    continue;
                    	}
						$termsArray = $tt->getAncestors($term);
						$termsArray[] = $term;
						$tmp = array();
						foreach ($termsArray as $tempTerm) {
							$contentData['taxonomy'][$taxonomy['name']][] = $tempTerm['text'];
						}
                    	
					}
                }
         }
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
		@$this->_content_index->delete();
		$this->_content_index->create(self::$_content_index_param,true);
		@$this->_document_index->delete();
		$this->_document_index->create(self::$_document_index_param,true);	
			
		// Retreive all content types
		$contentTypeList = \Rubedo\Services\Manager::getService('ContentTypes')->read();
		
		foreach($contentTypeList["data"] as $contentType) {
			// Create content type with overwrite set to true
			$this->indexContentType($contentType["id"],$contentType,TRUE);
			// Index all contents from type
			$c = \Rubedo\Services\Manager::getService('MongoDataAccess');
			$c->init("Contents");	
			$filter = array("typeId"=>$contentType["id"]);
			$c->addFilter($filter);
			$contentList = $c->read();
			$contentCount = 0;
			foreach($contentList["data"] as $content) {
				$this->indexContent($content["id"]);
				$contentCount++;
			}
			$result[$contentType["type"]]=$contentCount;
		}
		return($result);

    }
	
}
