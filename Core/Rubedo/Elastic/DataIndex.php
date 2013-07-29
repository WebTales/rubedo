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
namespace Rubedo\Elastic;

use Rubedo\Interfaces\Elastic\IDataIndex, Rubedo\Services\Manager;

/**
 * Class implementing the Rubedo API to Elastic Search indexing services using
 * Elastica API
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataIndex extends DataAbstract implements IDataIndex
{

    /**
     * Contains content types already requested
     */
    protected $_contentTypeCache = array();

    /**
     * Contains dam types already requested
     */
    protected $_damTypeCache = array();

    /**
     * Contains the documents
     */
    protected $_documents;

    /**
     * Get ES type structure
     *
     * @param string $id
     *            content type id
     * @return array
     */
    public function getContentTypeStructure ($id)
    {
        $returnArray = array();
        
        $searchableFields = array(
            array('name'=>'lastUpdateTime','localizable'=>false),
            array('name'=>'text','localizable'=>true),
            array('name'=>'summary','localizable'=>true),
            array('name'=>'type','localizable'=>false),
            array('name'=>'target','localizable'=>false)
        );
        
        if (! isset($this->_contentTypeCache[$id])) {
            // Get content type config by id
            $this->_contentTypeCache[$id] = Manager::getService('ContentTypes')->findById($id);
        }
        
        // System contents are not indexed
        if (isset($this->_contentTypeCache[$id]['system']) and $this->_contentTypeCache[$id]['system'] == TRUE) {
            return array();
        }
        
        // Get indexable fields
        $fields = $this->_contentTypeCache[$id]["fields"];
        foreach ($fields as $field) {
            if ($field['config']['searchable']) {
                $searchableFields[] = $field['config'];
            }
        }
        
        $returnArray['searchableFields'] = $searchableFields;
        return $returnArray;
    }

    /**
     * Get ES DAM type structure
     *
     * @param string $id
     *            DAM type id
     * @return array
     */
    public function getDamTypeStructure ($id)
    {
        $returnArray = array();
        $searchableFields = array(
            array('name'=>'lastUpdateTime','localizable'=>false),
            array('name'=>'text','localizable'=>true),
            array('name'=>'text_not_analysed','localizable'=>true),
            array('name'=>'type','localizable'=>false),
            array('name'=>'author','localizable'=>false),
            array('name'=>'fileSize','localizable'=>false),
            array('name'=>'target','localizable'=>false)
        );
        
        if (! isset($this->_damTypeCache[$id])) {
            // Get content type config by id
            $this->_damTypeCache[$id] = Manager::getService('DamTypes')->findById($id);
        }
        
        // Search summary field
        $fields = $this->_damTypeCache[$id]["fields"];
        foreach ($fields as $field) {
            if ($field['config']['searchable']) {
                $searchableFields[] = $field['config'];
            }
        }
        
        $returnArray['searchableFields'] = $searchableFields;
        return $returnArray;
    }

    /**
     * Returns the indexable fields and their configuration
     *
     * @param array $fields
     *            contain the fields and their configuration
     * @return array
     */
    public function getIndexMapping (array $fields, $objectType)
    {
        $indexMapping = array();
        
        // ad text, summary, nativeText, nativeSummary system fields
        
        $fields[] = array(
        	"cType" => "system",
            "config" => array (
            	"name" => "text",
            	"fieldLabel" => "text",
            	"searchable" => true,
            	"localizable" => true
            )
        );
        $fields[] = array(
        	"cType" => "system",
            "config" => array (
                "name" => "summary",
                "fieldLabel" => "summary",
                "searchable" => true,
            	"localizable" => true
            )
        );
        
        foreach ($fields as $field) {
            
            // Only searchable fields get indexed
            if ($field['config']['searchable']) {
                
                //$name = $field['config']['fieldLabel'];
                $name = $field['config']['name'];
                $store = "yes";
                
                switch ($field['cType']) {
                    case 'datefield':
                        $indexMapping[$name] = array(
                            'type' => 'date',
                            'format' => 'yyyy-MM-dd',
                            'store' => $store
                        );
                        break;
                    case 'document':
                        $indexMapping[$name] = array(
                            'type' => 'attachment',
                            'store' => 'no'
                        );
                        break;
                    case 'localiserField':
                        $indexMapping["position_location"] = array(
                            'type' => 'geo_point',
                            'store' => 'yes'
                        );
                        $indexMapping["position_address"] = array(
                            'type' => 'string',
                            'store' => 'yes'
                        );
                        break;
                    default:
                    	
                        if (isset($field['config']['localizable']) and $field['config']['localizable']==true) {
                        	// get active languages
                        	$languages = Manager::getService("Languages");
                        	$activeLanguages = $languages->getActiveLanguages();
                        	
                        	// get active analysers
                        	$activeAnalysers = array_keys($this::$_content_index_param["index"]["analysis"]["analyzer"]);
    
                        	// create on field per language with proper analyser with all_locale collector
    						foreach($activeLanguages as $lang) {
    							$locale = $lang['locale'];
    							$fieldName = $name.'_'.$locale;
    							$_all = 'all_'.$locale;
                                $_autocomplete = 'autocomplete_'.$locale;
    							if (in_array($locale.'_analyzer',$activeAnalysers))	{					
    								$lg_analyser = $locale.'_analyzer';						
    							} else {
    								$lg_analyser = 'default';
    							}
    							$indexMapping[$fieldName] = array(
    									"type" => "multi_field",
    									"path" => "just_name",
    									"fields" => array(
    											$fieldName => array("type" => "string", "analyzer" => $lg_analyser, 'store' => $store),
    											$_all => array("type" => "string", "analyzer" => $lg_analyser, 'store' => $store),
    									        $_autocomplete => array("type"=> "string", "analyzer" => "autocomplete", 'store' => $store)
    									)
    							);
    						}
                    	} else {
                    		$_autocomplete = 'autocomplete_nonlocalized';
                    		$_all = 'all_nonlocalized';
                    		$indexMapping[$name] = array(
        						"type" => "multi_field",
        						"path" => "just_name",
        						"fields" => array(
        							$name => array("type" => "string", 'store' => $store),
        							$_all => array("type" => "string", 'store' => $store),
        						    $_autocomplete => array("type"=> "string", "analyzer" => "autocomplete", 'store' => $store)
        						)
        					);
                    	} 

                        break;
                }
            }
        }
        
        return $indexMapping;
    }
    
    /**
     * Return all the vocabularies contained in the id list
     * 
     * @param array $data
     *         contain vocabularies id of the current object
     * @return array
     */
    protected function _getVocabularies($data) {
        $vocabularies = array();
        foreach ($data['vocabularies'] as $vocabularyId) {
            $vocabulary = Manager::getService('Taxonomy')->findById($vocabularyId);
            $vocabularies[] = $vocabulary['name'];
        }
        
        return $vocabularies;
    }
   
    /**
     * Index ES type for new or updated content type
     *
     * @see \Rubedo\Interfaces\IDataIndex:indexContentType()
     * @param string $id
     *            content type id
     * @param array $data
     *            new content type
     * @return array
     */
    public function indexContentType ($id, $data, $overwrite = FALSE)
    {
        
        // Unicity type id check
        $mapping = self::$_content_index->getMapping();
        if (array_key_exists($id, $mapping[self::$_options['contentIndex']])) {
            if ($overwrite) {
                // delete existing content type
                $this->deleteContentType($id);
            } else {
                // throw exception
                throw new \Rubedo\Exceptions\Server('%1$s type already exists', "Exception64", $id);
            }
        }
        
        $vocabularies = $this->_getVocabularies($data);
        
        // Create mapping
        if (isset($data["fields"]) && is_array($data["fields"])) {
            $indexMapping = $this->getIndexMapping($data["fields"],"content");
        }
        
        // Add systems metadata
        $indexMapping["lastUpdateTime"] = array(
            'type' => 'date',
            'store' => 'yes'
        );
        $indexMapping["nativeText"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );       
        $indexMapping["text_not_analyzed"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["nativeSummary"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );               
        $indexMapping["objectType"] = array(
            'type' => 'string',
            'store' => 'yes'
        );
        $indexMapping["author"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["authorName"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["contentType"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["target"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["writeWorkspace"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["startPublicationDate"] = array(
            'type' => 'integer',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["endPublicationDate"] = array(
            'type' => 'integer',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["availableLanguages"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        
        // Add Taxonomies
        foreach ($vocabularies as $vocabularyName) {
            $indexMapping["taxonomy." . $vocabularyName] = array(
                'type' => 'string',
                'index' => 'not_analyzed',
                'store' => 'no'
            );
        }
        
        // Create new ES type if not empty
        if (! empty($indexMapping)) {
            // Create new type
            $type = new \Elastica\Type(self::$_content_index, $id);
            
            // Set mapping
            $type->setMapping($indexMapping);
            
            // Return indexed field list
            return array_flip(array_keys($indexMapping));
        } else {
            // If there is no searchable field, the new type is not created
            return array();
        }
    }

    /**
     * Index ES type for new or updated dam type
     *
     * @see \Rubedo\Interfaces\IDataIndex:indexDamType()
     * @param string $id
     *            dam type id
     * @param array $data
     *            new content type
     * @return array
     */
    public function indexDamType ($id, $data, $overwrite = FALSE)
    {
        
        // Unicity type id check
        $mapping = self::$_dam_index->getMapping();
        if (array_key_exists($id, $mapping[self::$_options['damIndex']])) {
            if ($overwrite) {
                // delete existing content type
                $this->deleteDamType($id);
            } else {
                // throw exception
                throw new \Rubedo\Exceptions\Server('%1$s type already exists', "Exception64", $id);
            }
        }
        
        $vocabularies = $this->_getVocabularies($data);
        
        // Create mapping
        if (isset($data["fields"]) && is_array($data["fields"])) {
            $indexMapping = $this->getIndexMapping($data["fields"],"dam");
        }
        
        // Add systems metadata
        $indexMapping["lastUpdateTime"] = array(
            'type' => 'date',
            'store' => 'yes'
        );
        $indexMapping["text"] = array(
            'type' => 'string',
            'store' => 'yes'
        );
        $indexMapping["text_not_analyzed"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["objectType"] = array(
            'type' => 'string',
            'store' => 'yes'
        );
        $indexMapping["summary"] = array(
            'type' => 'string',
            'store' => 'yes'
        );
        $indexMapping["author"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["authorName"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["damType"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["fileSize"] = array(
            'type' => 'integer',
            'store' => 'yes'
        );
        $indexMapping["file"] = array(
            'type' => 'attachment'
        );
        $indexMapping["target"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["writeWorkspace"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["startPublicationDate"] = array(
            'type' => 'integer',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["endPublicationDate"] = array(
            'type' => 'integer',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        $indexMapping["availableLanguages"] = array(
            'type' => 'string',
            'index' => 'not_analyzed',
            'store' => 'yes'
        );
        
        // Add Taxonomies
        foreach ($vocabularies as $vocabularyName) {
            $indexMapping["taxonomy." . $vocabularyName] = array(
                'type' => 'string',
                'index' => 'not_analyzed',
                'store' => 'no'
            );
        }
        
        // If there is no searchable field, the new type is not created
        if (! empty($indexMapping)) {
            // Create new type
            $type = new \Elastica\Type(self::$_dam_index, $id);
            
            // Set mapping
            $indexMappingObject = \Elastica\Type\Mapping::create($indexMapping);
            $indexMappingObject->disableSource();
            $type->setMapping($indexMappingObject);
            
            // Return indexed field list
            return array_flip(array_keys($indexMapping));
        } else {
            return array();
        }
    }

    
    /**
     * Index ES type for new or updated user
     *
     * @see \Rubedo\Interfaces\IDataIndex:indexUserType()
     * @param string $id
     *            user id
     * @param array $data
     *            new user
     * @return array
     */
    public function indexUserType ($id, $data, $overwrite = FALSE)
    {
    
        // Unicity type id check
        $mapping = self::$_user_index->getMapping();
        if (array_key_exists($id, $mapping[self::$_options['userIndex']])) {
            if ($overwrite) {
                // delete existing content type
                $this->deleteUserType($id);
            } else {
                // throw exception
                throw new \Rubedo\Exceptions\Server('%1$s user type already exists', "Exception64", $id);
            }
        }
    
        $vocabularies = $this->_getVocabularies($data);
    
        // Create mapping
        if (isset($data["fields"]) && is_array($data["fields"])) {
            $indexMapping = $this->getIndexMapping($data["fields"],"user");
        }
    
        // Add systems metadata
        $indexMapping["lastUpdateTime"] = array(
                'type' => 'date',
                'store' => 'yes'
        );
        $indexMapping["name"] = array(
                'type' => 'string',
                'store' => 'yes'
        );
        $indexMapping["organisation"] = array(
                'type' => 'string',
                'store' => 'yes'
        );
        $indexMapping["photo"] = array(
                'type' => 'string',
                'index' => 'not_analyzed',
                'store' => 'yes'
        );
       
        // If there is no searchable field, the new type is not created
        if (! empty($indexMapping)) {
            // Create new type
            $type = new \Elastica\Type(self::$_user_index, $id);
    
            // Set mapping
            $indexMappingObject = \Elastica\Type\Mapping::create($indexMapping);
            $indexMappingObject->disableSource();
            $type->setMapping($indexMappingObject);
    
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
     * @param string $id
     *            content type id
     * @return array
     */
    public function deleteContentType ($id)
    {
        $type = new \Elastica\Type(self::$_content_index, $id);
        $type->delete();
    }

    /**
     * Delete existing content from index
     *
     * @see \Rubedo\Interfaces\IDataIndex::deleteContent()
     * @param string $typeId
     *            content type id
     * @param string $id
     *            content id
     * @return array
     */
    public function deleteContent ($typeId, $id)
    {
        $type = new \Elastica\Type(self::$_content_index, $typeId);
        $type->deleteById($id);
    }

    /**
     * Delete ES type for existing dam type
     *
     * @see \Rubedo\Interfaces\IDataIndex::deleteDamType()
     * @param string $id
     *            dam type id
     * @return array
     */
    public function deleteDamType ($id)
    {
        $type = new \Elastica\Type(self::$_dam_index, $id);
        $type->delete();
    }

    /**
     * Delete existing dam from index
     *
     * @see \Rubedo\Interfaces\IDataIndex::deleteDam()
     * @param string $typeId
     *            content type id
     * @param string $id
     *            content id
     * @return array
     */
    public function deleteDam ($typeId, $id)
    {
        $type = new \Elastica\Type(self::$_dam_index, $typeId);
        $type->deleteById($id);
    }

    /**
     * Create or update index for existing content
     *
     * @see \Rubedo\Interfaces\IDataIndex::indexContent()
     * @param obj $data
     *            content data
     * @param boolean $live
     *            live if true, workspace if live
     * @return array
     */
    public function indexContent ($data, $bulk = false)
    {
        $typeId = $data['typeId'];
        
        // Load ES type
        $contentType = self::$_content_index->getType($typeId);
        
        // Get content type structure
        $typeStructure = $this->getContentTypeStructure($typeId);
        
        // System contents are not indexed
        if (empty($typeStructure)) {
            return;
        }
        
        // Add fields to index
        $contentData = array();
        

        // For every searchable field
        foreach ($typeStructure['searchableFields'] as $fieldConfig) {
        	
        	// Get field name
        	$name = $fieldConfig['name'];
        	
        	// Get non localizable fields from fields 
        	if (!$fieldConfig['localizable']) {
        		if (isset($data['fields'][$name])) {
 					
        			$value  = $data['fields'][$name];
	        		if (is_array($value)) {
	        			foreach ($value as $key => $subvalue) {
	        				if ($name != 'position') {
	        					$contentData[$name][$key] = (string) $subvalue;
	        				} else {
	        					if ($key == 'address') {
	        						$contentData['position_address'] = (string) $subvalue;
	        					}
	        					if ($key == 'location') {
	        						if (isset($subvalue['coordinates'][0]) && isset($subvalue['coordinates'][1])) {
	        							$lon = $subvalue['coordinates'][0];
	        							$lat = $subvalue['coordinates'][1];
	        			
	        							$contentData['position_location'] = array(
	        									(float) $lon,
	        									(float) $lat
	        							);
	        						}
	        					}
	        				}
	        			}	        			
	        		} else  {
	        		    if(!empty($value)){
	        		        $contentData[$name] = (string) $value;
	        		    }else{
	        		        $contentData[$name] = null;
	        		    }
	        			
	        		}
        		}
        		
        	} else {
        		
        	// Get localizable fields from i18n
        		if (isset($data['i18n'])) {
        		    $availableLanguages = array();
        			foreach ($data['i18n'] as $key => $locale) {
        			    $availableLanguages[] = $key;
        				if (isset($locale['fields'][$name])) {
        					$value  = $locale['fields'][$name];
        					// Temp fix because local is not set in i18n at the first content creation
        					if (!isset($locale['locale'])) $locale['locale'] = $key ;
        					$indexFieldName = $name.'_'.$locale['locale'];
        					if (is_array($value)) {
        						foreach ($value as $key => $subvalue) {
									$contentData[$indexFieldName][$key] = (string) $subvalue;
        						}
        					} else  {
        						$contentData[$indexFieldName] = (string) $value;
        					}       					
        				}
        			}
        			$contentData['availableLanguages'] = $availableLanguages;
        		}
        		
        	}
        
        }

    
        // Add default meta's
        $contentData['objectType'] = 'content';
        $contentData['contentType'] = $typeId;
        if(isset($data['fields'])){
            $contentData['nativeText']=$data['fields']['text'];
            $contentData['nativeSummary']=$data['fields']['summary'];
        }else{
            $contentData['nativeText']='';
            $contentData['nativeSummary']='';
        }
        
        $contentData['writeWorkspace'] = isset($data['writeWorkspace']) ? $data['writeWorkspace'] : null;
        $contentData['startPublicationDate'] = isset($data['startPublicationDate']) ? intval($data['startPublicationDate']) : null;
        $contentData['endPublicationDate'] = isset($data['endPublicationDate']) ? intval($data['endPublicationDate']) : null;
        
        //in ES 0.9, date are in microseconds
        $contentData['lastUpdateTime'] = (isset($data['lastUpdateTime'])) ? (string) ($data['lastUpdateTime']*1000) : 0;
        $contentData['status'] = (isset($data['status'])) ? (string) $data['status'] : 'unknown';
        $contentData['author'] = (isset($data['createUser'])) ? (string) $data['createUser']['id'] : 'unknown';
        $contentData['authorName'] = (isset($data['createUser'])) ? (string) $data['createUser']['fullName'] : 'unknown';
        
        // Add taxonomy
        if (isset($data["taxonomy"])) {
            
            $taxonomyService = Manager::getService('Taxonomy');
            $taxonomyTermsService = Manager::getService('TaxonomyTerms');
            
            foreach ($data["taxonomy"] as $vocabulary => $terms) {
                if (! is_array($terms)) {
                    continue;
                }
                
                $taxonomy = $taxonomyService->findById($vocabulary);
                $termsArray = array();
                
                foreach ($terms as $term) {
                	if($term == 'all'){
                		continue;
                	}
                    $term = $taxonomyTermsService->findById($term);
                    
                    if (! $term) {
                        continue;
                    }
                    
                    if (! isset($termsArray[$term["id"]])) {
                        $termsArray[$term["id"]] = $taxonomyTermsService->getAncestors($term);
                        $termsArray[$term["id"]][] = $term;
                    }
                    
                    foreach ($termsArray[$term["id"]] as $tempTerm) {
                        $contentData['taxonomy'][$taxonomy['id']][] = $tempTerm['id'];
                    }
                }
            }
        }
        
        // Add read workspace
        $contentData['target'] = array();
        if (isset($data['target'])) {
            if (! is_array($data['target'])) {
                $data['target'] = array(
                    $data['target']
                );
            }
            foreach ($data['target'] as $key => $target) {
                $contentData['target'][] = (string) $target;
            }
        }
        if (empty($contentData['target'])) {
            $contentData['target'][] = 'global';
        }

        // Add document
        $currentDocument = new \Elastica\Document($data['id'], $contentData);
        
        if (isset($contentData['attachment']) && $contentData['attachment'] != '') {
            $currentDocument->addFile('file', $contentData['attachment']);
        }
        
        // Add content to content type index
        if (! $bulk) {
            $contentType->addDocument($currentDocument);
            $contentType->getIndex()->refresh();
        } else {
            $this->_documents[] = $currentDocument;
        }
    }

    /**
     * Create or update index for existing Dam document
     *
     * @param obj $data
     *            dam data
     * @return array
     */
    public function indexDam ($data, $bulk = false)
    {
        $typeId = $data['typeId'];
        
        // Load ES dam type
        $damType = self::$_dam_index->getType($typeId);
        
        // Get dam type structure
        $typeStructure = $this->getDamTypeStructure($typeId);
        
        // Add fields to index
        $damData = array();

        // For every searchable field
        foreach ($typeStructure['searchableFields'] as $fieldConfig) {
             
            // Get field name
            $name = $fieldConfig['name'];
             
            // Get non localizable fields from fields
            if (!$fieldConfig['localizable']) {
                if (isset($data['fields'][$name])) {
        
                    $value  = $data['fields'][$name];
                    if (is_array($value)) {
                        foreach ($value as $key => $subvalue) {
                            $damData[$name][$key] = (string) $subvalue;
                        }
                    } else  {
                        if(!empty($value)){
                            $damData[$name] = (string) $value;
                        }else{
                            $damData[$name] = null;
                        }
        
                    }
                }
        
            } else {
        
                // Get localizable fields from i18n
                if (isset($data['i18n'])) {
                    $availableLanguages = array();
                    foreach ($data['i18n'] as $key => $locale) {
                        $availableLanguages[] = $key;
                        if (isset($locale['fields'][$name])) {
                            $value  = $locale['fields'][$name];
                            // Temp fix because local is not set in i18n at the first dam creation
                            if (!isset($locale['locale'])) $locale['locale'] = $key ;
                            $indexFieldName = $name.'_'.$locale['locale'];
                            if (is_array($value)) {
                                foreach ($value as $key => $subvalue) {
                                    $damData[$indexFieldName][$key] = (string) $subvalue;
                                }
                            } else  {
                                $damData[$indexFieldName] = (string) $value;
                            }
                        }
                    }
                    $damData['availableLanguages'] = $availableLanguages;
                }
        
            }
        
        }
                
        // Add default meta's
        $damData['damType'] = $typeId;
        $damData['objectType'] = 'dam';
        $damData['writeWorkspace'] = isset($data['writeWorkspace']) ? $data['writeWorkspace'] : array();
        $damData['text'] = (string) $data['title'];
        $damData['text_not_analyzed'] = (string) $data['title'];
        $fileSize = isset($data['fileSize']) ? (integer) $data['fileSize'] : 0;
        $damData['fileSize'] = $fileSize;
        
        //in ES 0.9, date are in microseconds
        $damData['lastUpdateTime'] = (isset($data['lastUpdateTime'])) ? (string) ($data['lastUpdateTime']*1000) : 0;
        $damData['author'] = (isset($data['createUser'])) ? (string) $data['createUser']['id'] : 'unknown';
        $damData['authorName'] = (isset($data['createUser'])) ? (string) $data['createUser']['fullName'] : 'unknown';
        
        // Add taxonomy
        if (isset($data["taxonomy"])) {
            $taxonomyTermsService = Manager::getService('TaxonomyTerms');
            foreach ($data["taxonomy"] as $vocabulary => $terms) {
                if (! is_array($terms)) {
                    continue;
                }
                $taxonomy = Manager::getService('Taxonomy')->findById($vocabulary);
                $termsArray = array();
                
                foreach ($terms as $term) {
                	if($term == 'all'){
                		continue;
                	}
                    $term = $taxonomyTermsService->findById($term);
                    
                    if (! $term) {
                        continue;
                    }
                    
                    if (! isset($termsArray[$term["id"]])) {
                        $termsArray[$term["id"]] = $taxonomyTermsService->getAncestors($term);
                        $termsArray[$term["id"]][] = $term;
                    }
                    
                    foreach ($termsArray[$term["id"]] as $tempTerm) {
                        $damData['taxonomy'][$taxonomy['id']][] = $tempTerm['id'];
                    }
                }
            }
        }
        
        // Add target
        $damData['target'] = array();
        if (isset($data['target'])) {
            foreach ($data['target'] as $key => $target) {
                $damData['target'][] = (string) $target;
            }
        }
        
        // Add document
        $currentDam = new \Elastica\Document($data['id'], $damData);
        
        if (isset($data['originalFileId']) && $data['originalFileId'] != '') {
            
            $indexedFiles = array(
                'application/pdf',
                'application/rtf',
                'text/html',
                'text/plain',
                'text/richtext',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.oasis.opendocument.text',
                'application/vnd.oasis.opendocument.spreadsheet',
                'application/vnd.oasis.opendocument.presentation'
            );
            $mime = explode(';', $data['Content-Type']);
            
            if (in_array($mime[0], $indexedFiles)) {
                $mongoFile = Manager::getService('Files')->FindById($data['originalFileId']);
                $currentDam->addFileContent('file', $mongoFile->getBytes());
            }
        }
        
        // Add dam to dam type index
        
        if (! $bulk) {
            $damType->addDocument($currentDam);
            $damType->getIndex()->refresh();
        } else {
            $this->_documents[] = $currentDam;
        }
    }

    /**
     * Reindex all content or dam
     *
     * @param string $option
     *            : dam, content or all
     *            
     * @return array
     */
    public function indexAll ($option = 'all')
    {
        
        // Bulk size
        $bulkSize = 500;
        $bulk = true;
        
        // Initialize result array
        $result = array();
        
        if ($option == 'all' or $option == 'content') {
            // Destroy and re-create content index
            @self::$_content_index->delete();
            self::$_content_index->create(self::$_content_index_param, true);
        }
        
        if ($option == 'all' or $option == 'dam') {
            // Destroy and re-create dam index
            @self::$_dam_index->delete();
            self::$_dam_index->create(self::$_dam_index_param, true);
        }
        
        $contentsService = Manager::getService('Contents');
        
        if ($option == 'all' or $option == 'content') {
            
            // Retreive all content types
            $contentTypeList = Manager::getService('ContentTypes')->getList();
            
            foreach ($contentTypeList["data"] as $contentType) {
                
                // System contents are not indexed
                if (! isset($contentType['system']) or $contentType['system'] == FALSE) {
                    
                    // Create content type with overwrite set to true
                    $this->indexContentType($contentType["id"], $contentType, TRUE);
                    
                    // Get content type ES type
                    $ESType = self::$_content_index->getType($contentType["id"]);
                    
                    // Index all contents from type
                    $itemList = $contentsService->getByType($contentType["id"]);
                    $bulkCount = 0;
                    $this->_documents = array();
                    $itemCount = 0;
                    foreach ($itemList["data"] as $content) {
                        $this->indexContent($content, $bulk);
                        if ($bulkCount == $bulkSize or count($itemList["data"]) == $itemCount + 1) {
                            $ESType->addDocuments($this->_documents);
                            $ESType->getIndex()->refresh();
                            $bulkCount = 0;
                            $this->_documents = array();
                        }
                        $itemCount ++;
                        $bulkCount ++;
                    }
                    $result[$contentType["type"]] = $itemCount;
                }
            }
        }
        
        if ($option == 'all' or $option == 'dam') {
            
            // Retreive all dam types
            $damTypeList = Manager::getService('DamTypes')->getList();
            
            foreach ($damTypeList["data"] as $damType) {
                
                // Create dam type with overwrite set to true
                $this->indexdamType($damType["id"], $damType, TRUE);
                
                // Get dam type ES type
                $ESType = self::$_dam_index->getType($damType["id"]);
                
                // Index all dams from type
                $itemList = Manager::getService('Dam')->getByType($damType["id"]);
                $bulkCount = 0;
                $this->_documents = array();
                $itemCount = 0;
                
                foreach ($itemList["data"] as $dam) {
                    
                    $this->indexDam($dam, $bulk);
                    if ($bulkCount == $bulkSize or count($itemList["data"]) == $itemCount + 1) {
                        $ESType->addDocuments($this->_documents);
                        $ESType->getIndex()->refresh();
                        $bulkCount = 0;
                        $this->_documents = array();
                    }
                    $itemCount ++;
                    $bulkCount ++;
                }
                
                $result[$damType["type"]] = $itemCount;
            }
        }
        
        return ($result);
    }

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
    public function indexByType ($option, $id)
    {
        // bulk size
        $bulkSize = 500;
        $bulk = true;
        
        // Initialize result array
        $result = array();
        
        // Retrieve data and ES index for type
        switch ($option) {
            case 'content':
                $serviceType = 'ContentTypes';
                $serviceData = 'Contents';
                $contentType = self::$_content_index->getType($id);
                break;
            case 'dam':
                $serviceType = 'DamTypes';
                $serviceData = 'Dam';
                $contentType = self::$_dam_index->getType($id);
                break;
            default:
                throw new \Rubedo\Exceptions\Server("Option argument should be set to content or dam", "Exception65");
                break;
        }
        
        // Retrieve data and ES index for type
        
        $type = Manager::getService($serviceType)->findById($id);
        
        $itemCount = 0;
        $start = 0;
        $this->_documents = array();
        
        // Index all dam or contents from given type
        
        $dataService = Manager::getService($serviceData);
        
        do {
            
            $itemList = $dataService->getByType($id, $start, $bulkSize);
            
            foreach ($itemList["data"] as $item) {
                
                if ($option == 'content') {
                    $this->indexContent($item, $bulk);
                }
                
                if ($option == 'dam') {
                    $this->indexDam($item, $bulk);
                }
                
                $itemCount ++;
            }
            
            if (! empty($this->_documents)) {
                
                $contentType->addDocuments($this->_documents);
                $contentType->getIndex()->refresh();
                empty($this->_documents);
            }
            
            $start = $start + $bulkSize;
        } while (count($itemList['data']) == $bulkSize);
        
        $result[$type['type']] = $itemCount;
        
        return ($result);
    }
}
