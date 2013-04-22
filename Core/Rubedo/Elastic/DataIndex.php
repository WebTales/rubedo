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

use Rubedo\Interfaces\Elastic\IDataIndex;

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
            'lastUpdateTime',
            'text',
            'text_not_analysed',
            'summary',
            'type',
            'author',
            'target'
        );
        
        // Get content type config by id
        $contentTypeConfig = \Rubedo\Services\Manager::getService('ContentTypes')->findById($id);
        
        // System contents are not indexed
        if (isset($contentTypeConfig['system']) and $contentTypeConfig['system']==TRUE) {
            
            return array();
            
        } else {
        
            // Get indexable fields
            $fields = $contentTypeConfig["fields"];
            foreach ($fields as $field) {
                if ($field['config']['searchable']) {
                    $searchableFields[] = $field['config']['name'];
                }
            }
            
            $returnArray['searchableFields'] = $searchableFields;
            return $returnArray;
        }
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
            'lastUpdateTime',
            'text',
            'text_not_analysed',
            'type',
            'author',
            'fileSize',
            'target'
        );
        
        // Get content type config by id
        $damTypeConfig = \Rubedo\Services\Manager::getService('DamTypes')->findById($id);
        
        // Search summary field
        $fields = $damTypeConfig["fields"];
        foreach ($fields as $field) {
            if ($field['config']['searchable']) {
                $searchableFields[] = $field['config']['name'];
            }
        }
        
        $returnArray['searchableFields'] = $searchableFields;
        return $returnArray;
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
                throw new \Exception("$id type already exists");
            }
        }
        
        // Get vocabularies for current content type
        $vocabularies = array();
        foreach ($data['vocabularies'] as $vocabularyId) {
            $vocabulary = \Rubedo\Services\Manager::getService('Taxonomy')->findById($vocabularyId);
            $vocabularies[] = $vocabulary['name'];
        }
        
        // Create mapping
        $indexMapping = array();
        
        // If there are any fields get them mapped
        if (isset($data["fields"]) && is_array($data["fields"])) {
            
            foreach ($data["fields"] as $key => $field) {
                
                // Only searchable fields get indexed
                if ($field['config']['searchable']) {
                    
                    $name = $field['config']['fieldLabel'];
                    $store = "no";
                    
                    switch ($field['cType']) {
                        case 'checkbox':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'combo':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'datefield':
                            $indexMapping[$name] = array(
                                'type' => 'date',
                                'format' => 'yyyy-MM-dd',
                                'store' => $store
                            );
                            break;
                        case 'field':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'htmleditor':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'CKEField':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'numberfield':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'radio':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'textareafield':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'textfield':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'timefield':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'ratingField':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'slider':
                            $indexMapping[$name] = array(
                                'type' => 'string',
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
                            $indexMapping["position_adress"] = array(
                                'type' => 'string',
                                'store' => 'yes'
                            );
                            break;
                        default:
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => '$store'
                            );
                            break;
                    }
                }
            }
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
            $type = new \Elastica_Type(self::$_content_index, $id);
            
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
                throw new \Exception("$id type already exists");
            }
        }
        
        // Get vocabularies for current dam type
        $vocabularies = array();
        foreach ($data['vocabularies'] as $vocabularyId) {
            $vocabulary = \Rubedo\Services\Manager::getService('Taxonomy')->findById($vocabularyId);
            $vocabularies[] = $vocabulary['name'];
        }
        
        // Create mapping
        $indexMapping = array();
        
        // If there are any fields get them mapped
        if (is_array($data["fields"])) {
            
            foreach ($data["fields"] as $key => $field) {
                
                // Only searchable fields get indexed
                if ($field['config']['searchable']) {
                    
                    $name = $field['config']['fieldLabel'];
                    $store = "no";
                    
                    switch ($field['cType']) {
                        case 'checkbox':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'combo':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'datefield':
                            $indexMapping[$name] = array(
                                'type' => 'date',
                                'format' => 'yyyy-MM-dd',
                                'store' => $store
                            );
                            break;
                        case 'field':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'htmleditor':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'CKEField':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'numberfield':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'radio':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'textareafield':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'textfield':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'timefield':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'ratingField':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'slider':
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => $store
                            );
                            break;
                        case 'document':
                            $indexMapping[$name] = array(
                                'type' => 'attachment',
                                'store' => 'no'
                            );
                            break;
                        default:
                            $indexMapping[$name] = array(
                                'type' => 'string',
                                'store' => '$store'
                            );
                            break;
                    }
                }
            }
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
            'type' => 'attachment',
            'store' => 'no'
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
            $type = new \Elastica_Type(self::$_dam_index, $id);
            
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
     * @param string $id
     *            content type id
     * @return array
     */
    public function deleteContentType ($id)
    {
        $type = new \Elastica_Type(self::$_content_index, $id);
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
        $type = new \Elastica_Type(self::$_content_index, $typeId);
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
        $type = new \Elastica_Type(self::$_dam_index, $id);
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
        $type = new \Elastica_Type(self::$_dam_index, $typeId);
        $type->deleteById($id);
    }

    /**
     * Create or update index for existing content
     *
     * @see \Rubedo\Interfaces\IDataIndex::indexContentById()
     * @param string $id
     *            content id
     * @param boolean $live
     *            live if true, workspace if live
     * @return array
     */
    public function indexContentById ($id, $live = true)
    {
        
        // retrieve type id and content data if null
        $data = \Rubedo\Services\Manager::getService('Contents')->findById($id, $live, false);
        
        $typeId = $data['typeId'];
        
        // Load ES type
        $contentType = self::$_content_index->getType($typeId);
        
        // Get content type structure
        $typeStructure = $this->getContentTypeStructure($typeId);
        
        // Add fields to index
        $contentData = array();
        
        foreach ($data['fields'] as $field => $var) {
            
            // only index searchable fields
            if (in_array($field, $typeStructure['searchableFields'])) {
                if (is_array($var)) {
                    foreach ($var as $key => $subvalue) {
                        if ($field != 'position') {
                            $contentData[$field][$key] = (string) $subvalue;
                        } else {
                            if ($key == 'address') {
                                $contentData['position_address'] = (string) $subvalue;
                            }
                            if ($key == 'location') {
                                list ($lon, $lat) = $subvalue['coordinates'];
                                $contentData['position_location'] = array(
                                    (float) $lon,
                                    (float) $lat
                                );
                            }
                        }
                    }
                } else {
                    $contentData[$field] = (string) $var;
                }
            }
        }
        
        // Add default meta's
        $contentData['objectType'] = 'content';
        $contentData['contentType'] = $typeId;
        $contentData['writeWorkspace'] = isset($data['writeWorkspace']) ? $data['writeWorkspace'] : null;
        $contentData['startPublicationDate'] = isset($data['startPublicationDate']) ? intval($data['startPublicationDate']) : null;
        $contentData['endPublicationDate'] = isset($data['endPublicationDate']) ? intval($data['endPublicationDate']) : null;
        $contentData['text'] = (string) $data['text'];
        $contentData['text_not_analyzed'] = (string) $data['text'];
        $contentData['lastUpdateTime'] = (isset($data['lastUpdateTime'])) ? (string) $data['lastUpdateTime'] : 0;
        $contentData['status'] = (isset($data['status'])) ? (string) $data['status'] : 'unknown';
        $contentData['author'] = (isset($data['createUser'])) ? (string) $data['createUser']['id'] : 'unknown';
        $contentData['authorName'] = (isset($data['createUser'])) ? (string) $data['createUser']['fullName'] : 'unknown';
        
        // Add taxonomy
        if (isset($data["taxonomy"])) {
            $tt = \Rubedo\Services\Manager::getService('TaxonomyTerms');
            foreach ($data["taxonomy"] as $vocabulary => $terms) {
                if (! is_array($terms)) {
                    continue;
                }
                $collection = \Rubedo\Services\Manager::getService('Taxonomy');
                $taxonomy = $collection->findById($vocabulary);
                $termsArray = array();
                
                foreach ($terms as $term) {
                    $term = $tt->findById($term);
                    if (! $term) {
                        continue;
                    }
                    $termsArray = $tt->getAncestors($term);
                    $termsArray[] = $term;
                    $tmp = array();
                    foreach ($termsArray as $tempTerm) {
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
     * Create or update index for existing content
     *
     * @see \Rubedo\Interfaces\IDataIndex::indexContent()
     * @param obj $data
     *            content data
     * @param boolean $live
     *            live if true, workspace if live
     * @return array
     */
    public function indexContent ($data)
    {
        
        $typeId = $data['typeId'];
        
        // Load ES type
        $contentType = self::$_content_index->getType($typeId);
        
        // Get content type structure
        $typeStructure = $this->getContentTypeStructure($typeId);
        
        // System contents are not indexed
        if (empty($typeStructure)){
            return;
        }
        
        // Add fields to index
        $contentData = array();
        
        if (isset($data['fields'])) {
            foreach ($data['fields'] as $field => $var) {
                
                // only index searchable fields
                if (in_array($field, $typeStructure['searchableFields'])) {
                    if (is_array($var)) {
                        foreach ($var as $key => $subvalue) {
                            if ($field != 'position') {
                                $contentData[$field][$key] = (string) $subvalue;
                            } else {
                                if ($key == 'address') {
                                    $contentData['position_address'] = (string) $subvalue;
                                }
                                if ($key == 'location') {
                                    list ($lon, $lat) = $subvalue['coordinates'];
                                    $contentData['position_location'] = array(
                                        (float) $lon,
                                        (float) $lat
                                    );
                                }
                            }
                        }
                    } else {
                        $contentData[$field] = (string) $var;
                    }
                }
            }
        }
        
        // Add default meta's
        $contentData['objectType'] = 'content';
        $contentData['contentType'] = $typeId;
        $contentData['writeWorkspace'] = isset($data['writeWorkspace']) ? $data['writeWorkspace'] : null;
        $contentData['startPublicationDate'] = isset($data['startPublicationDate']) ? intval($data['startPublicationDate']) : null;
        $contentData['endPublicationDate'] = isset($data['endPublicationDate']) ? intval($data['endPublicationDate']) : null;
        $contentData['text'] = (string) $data['text'];
        $contentData['text_not_analyzed'] = (string) $data['text'];
        $contentData['lastUpdateTime'] = (isset($data['lastUpdateTime'])) ? (string) $data['lastUpdateTime'] : 0;
        $contentData['status'] = (isset($data['status'])) ? (string) $data['status'] : 'unknown';
        $contentData['author'] = (isset($data['createUser'])) ? (string) $data['createUser']['id'] : 'unknown';
        $contentData['authorName'] = (isset($data['createUser'])) ? (string) $data['createUser']['fullName'] : 'unknown';
        
        // Add taxonomy
        if (isset($data["taxonomy"])) {
            $tt = \Rubedo\Services\Manager::getService('TaxonomyTerms');
            foreach ($data["taxonomy"] as $vocabulary => $terms) {
                if (! is_array($terms)) {
                    continue;
                }
                $collection = \Rubedo\Services\Manager::getService('Taxonomy');
                $taxonomy = $collection->findById($vocabulary);
                $termsArray = array();
                
                foreach ($terms as $term) {
                    $term = $tt->findById($term);
                    if (! $term) {
                        continue;
                    }
                    $termsArray = $tt->getAncestors($term);
                    $termsArray[] = $term;
                    $tmp = array();
                    foreach ($termsArray as $tempTerm) {
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
        $currentDocument = new \Elastica_Document($data['id'], $contentData);
        
        if (isset($contentData['attachment']) && $contentData['attachment'] != '') {
            $currentDocument->addFile('file', $contentData['attachment']);
        }
        
        // Add content to content type index
        $contentType->addDocument($currentDocument);
        
        // Refresh index
        $contentType->getIndex()->refresh();
    }

    /**
     * Update Content Taxonomy
     *
     * @see \Rubedo\Interfaces\IDataIndex::updateContentTaxonomy()
     * @param string $id
     *            content id
     * @param boolean $live
     *            live if true, workspace if live
     * @return array
     */
    public function updateContentTaxonomy ($id, $live = false)
    {
        
        // content data to index
        if ($live) {
            $space = "live";
        } else {
            $space = "workspace";
        }
        
        // retrieve type id and content data if null
        $data = \Rubedo\Services\Manager::getService('Contents')->findById($id, true, false);
        $typeId = $data['typeId'];
        
        // Retrieve type label
        $contentType = \Rubedo\Services\Manager::getService('ContentTypes')->findById($typeId);
        
        // Add taxonomy
        if (isset($data["taxonomy"])) {
            $tt = \Rubedo\Services\Manager::getService('TaxonomyTerms');
            foreach ($data["taxonomy"] as $vocabulary => $terms) {
                if (! is_array($terms)) {
                    continue;
                }
                $collection = \Rubedo\Services\Manager::getService('Taxonomy');
                $taxonomy = $collection->findById($vocabulary);
                $termsArray = array();
                
                foreach ($terms as $term) {
                    $term = $tt->findById($term);
                    if (! $term) {
                        continue;
                    }
                    $termsArray = $tt->getAncestors($term);
                    $termsArray[] = $term;
                    $tmp = array();
                    foreach ($termsArray as $tempTerm) {
                        $contentData['taxonomy'][$taxonomy['id']][] = $tempTerm['id'];
                    }
                }
            }
        }
        $currentDocument = new \Elastica_Document($id, $contentData);
        
        // Add content to content type index
        $contentType->addDocument($currentDocument);
        
        // Refresh index
        $contentType->getIndex()->refresh();
    }

    /**
     * Create or update index for existing Dam document
     *
     * @param string $id
     *            dam id
     * @return array
     */
    public function indexDamById ($id)
    {
        
        // retrieve Dam Type id and dam data if null
        $data = \Rubedo\Services\Manager::getService('Dam')->findById($id);
        $typeId = $data['typeId'];
        
        // Load ES dam type
        $damType = self::$_dam_index->getType($typeId);
        
        // Get dam type structure
        $typeStructure = $this->getDamTypeStructure($typeId);
        
        // Add fields to index
        $damData = array();
        
        if (array_key_exists('fields', $data) && is_array($data['fields'])) {
            foreach ($data['fields'] as $field => $var) {
                
                // only index searchable fields
                if (in_array($field, $typeStructure['searchableFields'])) {
                    if (is_array($var)) {
                        foreach ($var as $key => $subvalue) {
                            $damData[$field][] = (string) $subvalue;
                        }
                    } else {
                        $damData[$field] = (string) $var;
                    }
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
        $damData['lastUpdateTime'] = (isset($data['lastUpdateTime'])) ? (string) $data['lastUpdateTime'] : 0;
        $damData['author'] = (isset($data['createUser'])) ? (string) $data['createUser']['id'] : 'unknown';
        $damData['authorName'] = (isset($data['createUser'])) ? (string) $data['createUser']['fullName'] : 'unknown';
        
        // Add taxonomy
        if (isset($data["taxonomy"])) {
            $tt = \Rubedo\Services\Manager::getService('TaxonomyTerms');
            foreach ($data["taxonomy"] as $vocabulary => $terms) {
                if (! is_array($terms)) {
                    continue;
                }
                $taxonomy = \Rubedo\Services\Manager::getService('Taxonomy')->findById($vocabulary);
                $termsArray = array();
                
                foreach ($terms as $term) {
                    $term = $tt->findById($term);
                    if (! $term) {
                        continue;
                    }
                    $termsArray = $tt->getAncestors($term);
                    $termsArray[] = $term;
                    $tmp = array();
                    foreach ($termsArray as $tempTerm) {
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
        $currentDam = new \Elastica_Document($id, $damData);
        
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
                $mongoFile = \Rubedo\Services\Manager::getService('Files')->FindById($data['originalFileId']);
                $currentDam->addFileContent('file', $mongoFile->getBytes());
            }
        }
        
        // Add dam to dam type index
        $damType->addDocument($currentDam);
        
        // Refresh index
        $damType->getIndex()->refresh();
    }

    /**
     * Create or update index for existing Dam document
     *
     * @param obj $data
     *            dam data
     * @return array
     */
    public function indexDam ($data)
    {
        $typeId = $data['typeId'];
        
        // Load ES dam type
        $damType = self::$_dam_index->getType($typeId);
        
        // Get dam type structure
        $typeStructure = $this->getDamTypeStructure($typeId);
        
        // Add fields to index
        $damData = array();
        
        if (array_key_exists('fields', $data) && is_array($data['fields'])) {
            foreach ($data['fields'] as $field => $var) {
                
                // only index searchable fields
                if (in_array($field, $typeStructure['searchableFields'])) {
                    if (is_array($var)) {
                        foreach ($var as $key => $subvalue) {
                            $damData[$field][] = (string) $subvalue;
                        }
                    } else {
                        $damData[$field] = (string) $var;
                    }
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
        $damData['lastUpdateTime'] = (isset($data['lastUpdateTime'])) ? (string) $data['lastUpdateTime'] : 0;
        $damData['author'] = (isset($data['createUser'])) ? (string) $data['createUser']['id'] : 'unknown';
        $damData['authorName'] = (isset($data['createUser'])) ? (string) $data['createUser']['fullName'] : 'unknown';
        
        // Add taxonomy
        if (isset($data["taxonomy"])) {
            $tt = \Rubedo\Services\Manager::getService('TaxonomyTerms');
            foreach ($data["taxonomy"] as $vocabulary => $terms) {
                if (! is_array($terms)) {
                    continue;
                }
                $taxonomy = \Rubedo\Services\Manager::getService('Taxonomy')->findById($vocabulary);
                $termsArray = array();
                
                foreach ($terms as $term) {
                    $term = $tt->findById($term);
                    if (! $term) {
                        continue;
                    }
                    $termsArray = $tt->getAncestors($term);
                    $termsArray[] = $term;
                    $tmp = array();
                    foreach ($termsArray as $tempTerm) {
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
        $currentDam = new \Elastica_Document($data['id'], $damData);
        
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
                $mongoFile = \Rubedo\Services\Manager::getService('Files')->FindById($data['originalFileId']);
                $currentDam->addFileContent('file', $mongoFile->getBytes());
            }
        }
        
        // Add dam to dam type index
        $damType->addDocument($currentDam);
        
        // Refresh index
        $damType->getIndex()->refresh();
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
        
        if ($option == 'all' or $option == 'content') {
            
            // Retreive all content types
            $contentTypeList = \Rubedo\Services\Manager::getService('ContentTypes')->getList();
            
            foreach ($contentTypeList["data"] as $contentType) {
                
                // System contents are not indexed
                if (!isset($contentType['system']) or $contentType['system']==FALSE) {
                                
                    // Create content type with overwrite set to true
                    $this->indexContentType($contentType["id"], $contentType, TRUE);
                    // Index all contents from type
                    $contentList = \Rubedo\Services\Manager::getService('Contents')->getByType($contentType["id"]);
                    $contentCount = 0;
                    foreach ($contentList["data"] as $content) {
                        $this->indexContent($content);
                        $contentCount ++;
                    }
                    $result[$contentType["type"]] = $contentCount;
                }
            }
        }
        
        if ($option == 'all' or $option == 'dam') {
            
            // Retreive all dam types
            $damTypeList = \Rubedo\Services\Manager::getService('DamTypes')->getList();
            
            foreach ($damTypeList["data"] as $damType) {
                // Create dam type with overwrite set to true
                $this->indexdamType($damType["id"], $damType, TRUE);
                // Index all dams from type
                $damList = \Rubedo\Services\Manager::getService('Dam')->getByType($damType["id"]);
                $damCount = 0;
                foreach ($damList["data"] as $dam) {
                    $this->indexDam($dam);
                    $damCount ++;
                }
                $result[$damType["type"]] = $damCount;
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
        
        // Initialize result array
        $result = array();
        
        switch ($option) {
            case 'content':
                $serviceType = 'ContentTypes';
                $serviceData = 'Contents';
                break;
            case 'dam':
                $serviceType = 'DamTypes';
                $serviceData = 'Dam';
                break;
            default:
                throw new \Exception("option should be set to content or dam");
                break;
        }
        
        // Retrieve data from type
        
        $type = \Rubedo\Services\Manager::getService($serviceType)->findById($id);
        
        // Index all dam or contents from given type
        $itemList = \Rubedo\Services\Manager::getService($serviceData)->getByType($id);
        $itemCount = 0;
        foreach ($itemList["data"] as $item) {
            if ($option == 'content') {
                $this->indexContent($item);
            }
            if ($option == 'dam') {
                $this->indexDam($item);
            }
            $itemCount ++;
        }
        $result[$type['type']] = $itemCount;
        
        return ($result);
    }
}
