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
use Rubedo\Interfaces\Elastic\IDataIndex, Rubedo\Services\Manager, WebTales\MongoFilters\Filter;
use Elastica\SearchableInterface;

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
     * Returns mapping from content or dam type data
     *
     * @param array $data
     *            string $type = 'content' or 'dam'
     *            
     * @return array
     */
    public function getIndexMapping (array $data, $type)
    {
        $mapping = array();
        
        if (isset($data['fields']) && is_array($data['fields'])) {
            
            // get active languages
            $languages = Manager::getService("Languages");
            $activeLanguages = $languages->getActiveLanguages();
            
            // get active analysers
            $activeAnalysers = array_keys(
                    $this::$_content_index_param["index"]["analysis"]["analyzer"]);
            
            // get vocabularies
            $vocabularies = $this->_getVocabularies($data);
            
            // Set generic mapping for contents & dam
            
            $generic_mapping = array(
                    'objectType' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                            'store' => 'yes'
                    ),
                    'lastUpdateTime' => array(
                            'type' => 'date',
                            'store' => 'yes'
                    ),
                    'createUser' => array(
                            'type' => 'object',
                            'store' => 'yes',
                            'properties' => array(
                                    'id' => array(
                                            'type' => 'string',
                                            'index' => 'not_analyzed',
                                            'store' => 'yes'
                                    ),
                                    'fullName' => array(
                                            'type' => 'string',
                                            'index' => 'not_analyzed',
                                            'store' => 'yes'
                                    ),
                                    'login' => array(
                                            'type' => 'string',
                                            'index' => 'no',
                                            'store' => 'no'
                                    )
                            )
                    ),
                    'text' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                            'store' => 'yes'
                    ),
                    'target' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                            'store' => 'yes'
                    ),
                    'writeWorkspace' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                            'store' => 'yes'
                    ),
                    'availableLanguages' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                            'store' => 'yes'
                    ),
                    'version' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                            'store' => 'yes'
                    )
            );
            
            // Set specific mapping for contents
            
            if ($type == 'content') {
                $specific_mapping = array(
                        'summary' => array(
                                'type' => 'string',
                                'store' => 'yes'
                        ),
                        'contentType' => array(
                                'type' => 'string',
                                'index' => 'not_analyzed',
                                'store' => 'yes'
                        ),
                        'startPublicationDate' => array(
                                'type' => 'integer',
                                'index' => 'not_analyzed',
                                'store' => 'yes'
                        ),
                        'endPublicationDate' => array(
                                'type' => 'integer',
                                'index' => 'not_analyzed',
                                'store' => 'yes'
                        )
                );
            }
            
            // Set specific mapping for dam
            
            if ($type == 'dam') {
                $specific_mapping = array(
                        'damType' => array(
                                'type' => 'string',
                                'index' => 'not_analyzed',
                                'store' => 'yes'
                        ),
                        'fileSize' => array(
                                'type' => 'integer',
                                'store' => 'yes'
                        ),
                        'file' => array(
                                'type' => 'attachment'
                        )
                );
            }
            
            // Merge generic and specific mappings
            
            $mapping = array_merge($generic_mapping, $specific_mapping);
            
            // Add Taxonomies
            foreach ($vocabularies as $vocabularyName) {
                $mapping["taxonomy." . $vocabularyName] = array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'store' => 'yes'
                );
            }
            
            // Add Fields
            
            $fields = $data['fields'];
            
            // Add system fields : text and summary for contents, title for dam
            
            if ($type == 'content') {
                $fields[] = array(
                        "cType" => "system",
                        "config" => array(
                                "name" => "text",
                                "fieldLabel" => "text",
                                "searchable" => true,
                                "localizable" => true
                        )
                );
                $fields[] = array(
                        "cType" => "system",
                        "config" => array(
                                "name" => "summary",
                                "fieldLabel" => "summary",
                                "searchable" => true,
                                "localizable" => true
                        )
                );
            }
            
            if ($type == 'dam') {
                $fields[] = array(
                        "cType" => "system",
                        "config" => array(
                                "name" => "title",
                                "fieldLabel" => "text",
                                "searchable" => true,
                                "localizable" => true
                        )
                );
            }
            SearchableInterface
            // unmapped fields are not allowed in fields and i18n
            $mapping['fields'] = array(
                    'dynamic' => false,
                    'type' => 'object'
            );
            foreach ($activeLanguages as $lang) {
                $mapping['i18n']['properties'][$lang['locale']]['properties']['fields'] = array(
                        'dynamic' => false,
                        'type' => 'object'
                );
            }
            
            foreach ($fields as $field) {
                
                // Only searchable fields get indexed
                if ($field['config']['searchable']) {
                    
                    $name = $field['config']['name'];
                    $store = (isset($field['config']['returnInSearch']) && $field['config']['returnInSearch']==FALSE) ? "no" : "yes";
                    
                    switch ($field['cType']) {
                        case 'datefield':
                            $config = array(
                                    'type' => 'string',
                                    'store' => $store
                            );
                            if (! $field['config']['localizable']) {
                                $mapping['fields']['properties'][$name] = $config;
                            } else {
                                foreach ($activeLanguages as $lang) {
                                    $mapping['i18n']['properties'][$lang['locale']]['properties']['fields'][$name] = $config;
                                }
                            }
                            break;
                        case 'document':
                            $config = array(
                                    'type' => 'attachment',
                                    'store' => 'no'
                            );
                            if (! $field['config']['localizable']) {
                                $mapping['fields']['properties'][$name] = $config;
                            } else {
                                foreach ($activeLanguages as $lang) {
                                    $mapping['i18n']['properties'][$lang['locale']]['properties']['fields'][$name] = $config;
                                }
                            }
                            break;
                        case 'localiserField':
                            $config = array(
                                    'properties' => array(
                                            'location' => array(
                                                    'properties' => array(
                                                            'coordinates' => array(
                                                                    'type' => 'geo_point',
                                                                    'store' => 'yes'
                                                            )
                                                    )
                                            ),
                                            'address' => array(
                                                    'type' => 'string',
                                                    'store' => 'yes'
                                            )
                                    )
                            );
                            if (! $field['config']['localizable']) {
                                $mapping['fields']['properties'][$name] = $config;
                            } else {
                                foreach ($activeLanguages as $lang) {
                                    $mapping['i18n']['properties'][$lang['locale']]['properties']['fields'][$name] = $config;
                                }
                            }
                            break;
                        default:
                            if (! $field['config']['localizable']) {
                                $_autocomplete = 'autocomplete_nonlocalized';
                                $_all = 'all_nonlocalized';
                                $config = array(
                                        "type" => "multi_field",
                                        "path" => "just_name",
                                        "fields" => array(
                                                $name => array(
                                                        "type" => "string",
                                                        "store" => $store
                                                ),
                                                $_all => array(
                                                        "type" => "string",
                                                        "analyzer" => "default_analyzer",
                                                        "store" => $store
                                                ),
                                                $_autocomplete => array(
                                                        "type" => "string",
                                                        "analyzer" => "autocomplete",
                                                        'store' => $store
                                                )
                                        )
                                );
                                $mapping['fields']['properties'][$name] = $config;
                            } else {
                                foreach ($activeLanguages as $lang) {
                                    $locale = $lang['locale'];
                                    $fieldName = $name . '_' . $locale;
                                    $_all = 'all_' . $locale;
                                    $_autocomplete = 'autocomplete_' . $locale;
                                    if (in_array($locale . '_analyzer', 
                                            $activeAnalysers)) {
                                        $lg_analyser = $locale . '_analyzer';
                                    } else {
                                        $lg_analyser = 'default';
                                    }
                                    $config = array(
                                            "type" => "multi_field",
                                            "path" => "just_name",
                                            "fields" => array(
                                                    $fieldName => array(
                                                            "type" => "string",
                                                            "analyzer" => $lg_analyser,
                                                            'store' => $store
                                                    ),
                                                    $_all => array(
                                                            "type" => "string",
                                                            "analyzer" => $lg_analyser,
                                                            'store' => $store
                                                    ),
                                                    $_autocomplete => array(
                                                            "type" => "string",
                                                            "analyzer" => "autocomplete",
                                                            'store' => $store
                                                    )
                                            )
                                    );
                                    $mapping['i18n']['properties'][$locale]['properties']['fields']['properties'][$name] = $config;
                                }
                            }
                    }
                }
            }
        }
        
        return $mapping;
    }

    /**
     * Returns mapping from user type data
     *
     * @param array $data            
     *
     * @return array
     */
    public function getUserIndexMapping (array $data)
    {
        $mapping = array();
        
        if (isset($data['fields']) && is_array($data['fields'])) {
            
            // get active analysers
            $activeAnalysers = array_keys(
                    $this::$_content_index_param["index"]["analysis"]["analyzer"]);
            
            // get vocabularies
            $vocabularies = $this->_getVocabularies($data);
            
            // Set mapping for user
            
            $mapping = array(
                    'objectType' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                            'store' => 'yes'
                    ),
                    'email' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                            'store' => 'yes'
                    ),
                    'photo' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                            'store' => 'yes'
                    ),
                    'userType' => array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                            'store' => 'yes'
                    ),
                    'lastUpdateTime' => array(
                            'type' => 'date',
                            'store' => 'yes'
                    ),
                    'createUser' => array(
                            'type' => 'object',
                            'store' => 'yes',
                            'properties' => array(
                                    'id' => array(
                                            'type' => 'string',
                                            'index' => 'not_analyzed',
                                            'store' => 'yes'
                                    ),
                                    'fullName' => array(
                                            'type' => 'string',
                                            'index' => 'not_analyzed',
                                            'store' => 'yes'
                                    ),
                                    'login' => array(
                                            'type' => 'string',
                                            'index' => 'no',
                                            'store' => 'no'
                                    )
                            )
                    )
            );
            
            // Add Taxonomies
            foreach ($vocabularies as $vocabularyName) {
                $mapping["taxonomy." . $vocabularyName] = array(
                        'type' => 'string',
                        'index' => 'not_analyzed',
                        'store' => 'yes'
                );
            }
            
            // Add Fields
            
            $fields = $data['fields'];
            
            // Add system fields : email and name for user
            
            $fields[] = array(
                    "cType" => "system",
                    "config" => array(
                            "name" => "email",
                            "fieldLabel" => "email",
                            "searchable" => false
                    )
            );
            $fields[] = array(
                    "cType" => "system",
                    "config" => array(
                            "name" => "name",
                            "fieldLabel" => "name",
                            "searchable" => true
                    )
            );
            
            // unmapped fields are not allowed in fields and i18n
            $mapping['fields'] = array(
                    'dynamic' => false,
                    'type' => 'object'
            );
            
            foreach ($fields as $field) {
                
                // Only searchable fields get indexed
                if ($field['config']['searchable']) {
                    
                    $name = $field['config']['name'];
                    $store = (isset($field['config']['returnInSearch']) && $field['config']['returnInSearch']==FALSE) ? "no" : "yes";
                    
                    switch ($field['cType']) {
                        case 'datefield':
                            $config = array(
                                    'type' => 'string',
                                    'store' => $store
                            );
                            $mapping['fields']['properties'][$name] = $config;
                            break;
                        case 'document':
                            $config = array(
                                    'type' => 'attachment',
                                    'store' => 'no'
                            );
                            $mapping['fields']['properties'][$name] = $config;
                            break;
                        case 'localiserField':
                            $config = array(
                                    'properties' => array(
                                            'location' => array(
                                                    'properties' => array(
                                                            'coordinates' => array(
                                                                    'type' => 'geo_point',
                                                                    'store' => 'yes'
                                                            )
                                                    )
                                            ),
                                            'address' => array(
                                                    'type' => 'string',
                                                    'store' => 'yes'
                                            )
                                    )
                            );
                            $mapping['fields']['properties'][$name] = $config;
                            break;
                        default:
                            $_autocomplete = 'autocomplete_nonlocalized';
                            $_all = 'all_nonlocalized';
                            $config = array(
                                    "type" => "multi_field",
                                    "path" => "just_name",
                                    "fields" => array(
                                            $name => array(
                                                    "type" => "string",
                                                    "store" => $store
                                            ),
                                            $_all => array(
                                                    "type" => "string",
                                                    "analyzer" => "default_analyzer",
                                                    "store" => $store
                                            ),
                                            $_autocomplete => array(
                                                    "type" => "string",
                                                    "analyzer" => "autocomplete",
                                                    'store' => $store
                                            )
                                    )
                            );
                            // Add first letter indexing for user name
                            if ($field["config"]["name"] == "name") {
                                $config["fields"]["first_letter"] = array(
                                        "type" => "string",
                                        "analyzer" => "first_letter"
                                );
                            }
                            $mapping["fields"]["properties"][$name] = $config;
                    }
                }
            }
        }
        
        return $mapping;
    }

    /**
     * Return all the vocabularies contained in the id list
     *
     * @param array $data
     *            contain vocabularies id of the current object
     * @return array
     */
    protected function _getVocabularies ($data)
    {
        $vocabularies = array();
        foreach ($data['vocabularies'] as $vocabularyId) {
            $vocabulary = Manager::getService('Taxonomy')->findById(
                    $vocabularyId);
            $vocabularies[] = $vocabulary['id'];
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
    public function indexContentType ($id, $data)
    {
        
        // Delete existing content type
        $this->deleteContentType($id);
        
        // Create mapping
        $indexMapping = $this->getIndexMapping($data, 'content');
        
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
    public function indexDamType ($id, $data)
    {
        
        // Delete existing dam type
        $this->deleteDamType($id);
        
        // Create mapping
        $indexMapping = $this->getIndexMapping($data, 'dam');
        
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
     * Index ES type for new or updated duser type
     *
     * @see \Rubedo\Interfaces\IDataIndex:indexUserType()
     * @param string $id
     *            user type id
     * @param array $data
     *            user type data
     * @return array
     */
    public function indexUserType ($id, $data)
    {
        
        // Delete existing user type
        $this->deleteUserType($id);
        
        // Create mapping
        $indexMapping = $this->getUserIndexMapping($data);
        
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
        $mapping = self::$_content_index->getMapping();
        if (array_key_exists($id, $mapping[self::$_content_index->getName()])) {
            $type = new \Elastica\Type(self::$_content_index, $id);
            $type->delete();
        }
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
        $mapping = self::$_dam_index->getMapping();
        if (array_key_exists($id, $mapping[self::$_dam_index->getName()])) {
            $type = new \Elastica\Type(self::$_dam_index, $id);
            $type->delete();
        }
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
     * Delete ES type for existing user type
     *
     * @see \Rubedo\Interfaces\IDataIndex::deleteUserType()
     * @param string $id
     *            user type id
     * @return array
     */
    public function deleteUserType ($id)
    {
        $mapping = self::$_user_index->getMapping();
        if (array_key_exists($id, $mapping[self::$_user_index->getName()])) {
            $type = new \Elastica\Type(self::$_user_index, $id);
            $type->delete();  
        }    
    }

    /**
     * Delete existing user from index
     *
     * @see \Rubedo\Interfaces\IDataIndex::deleteUser()
     * @param string $typeId
     *            user type id
     * @param string $id
     *            user id
     * @return array
     */
    public function deleteUser ($typeId, $id)
    {
        $type = new \Elastica\Type(self::$_user_index, $typeId);
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
        if (! isset($data['fields']) || ! isset($data['i18n'])) {
            return;
        }
        
        $typeId = $data['typeId'];
        
        // Load ES type
        $contentType = self::$_content_index->getType($typeId);
        
        // Initialize data array to push into index
        
        $indexData = array(
                'objectType' => 'content',
                'contentType' => $typeId,
                'text' => $data['text'],
                'fields' => $data['fields'],
                'i18n' => $data['i18n'],
                'writeWorkspace' => $data['writeWorkspace'],
                'startPublicationDate' => $data['startPublicationDate'],
                'endPublicationDate' => $data['endPublicationDate'],
                'lastUpdateTime' => (isset($data['lastUpdateTime'])) ? (string) ($data['lastUpdateTime'] *
                         1000) : 0,
                        'status' => $data['status'],
                        'createUser' => $data['createUser'],
                        'availableLanguages' => array_keys($data['i18n']),
                        'version' => $data['version']
        );
        
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
                    if ($term == 'all') {
                        continue;
                    }
                    $term = $taxonomyTermsService->findById($term);
                    
                    if (! $term) {
                        continue;
                    }
                    
                    if (! isset($termsArray[$term["id"]])) {
                        $termsArray[$term["id"]] = $taxonomyTermsService->getAncestors(
                                $term);
                        $termsArray[$term["id"]][] = $term;
                    }
                    
                    foreach ($termsArray[$term["id"]] as $tempTerm) {
                        $indexData['taxonomy.' . $taxonomy['id']][] = $tempTerm['id'];
                    }
                }
            }
        }
        
        // Add read workspace
        $indexData['target'] = array();
        if (isset($data['target'])) {
            if (! is_array($data['target'])) {
                $data['target'] = array(
                        $data['target']
                );
            }
            foreach ($data['target'] as $key => $target) {
                $indexData['target'][] = (string) $target;
            }
        }
        if (empty($indexData['target'])) {
            $indexData['target'][] = 'global';
        }
        
        // Add document
        $currentDocument = new \Elastica\Document($data['id'], $indexData);
        
        if (isset($indexData['attachment']) && $indexData['attachment'] != '') {
            $currentDocument->addFile('file', $indexData['attachment']);
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
        
        // Initialize data array to push into index
        
        $indexData = array(
                'objectType' => 'dam',
                'damType' => $typeId,
                'text' => $data['title'],
                'fields' => $data['fields'],
                'i18n' => $data['i18n'],
                'writeWorkspace' => $data['writeWorkspace'],
                'lastUpdateTime' => (isset($data['lastUpdateTime'])) ? (string) ($data['lastUpdateTime'] *
                         1000) : 0,
                        'createUser' => $data['createUser'],
                        'availableLanguages' => array_keys($data['i18n']),
                        'fileSize' => isset($data['fileSize']) ? (integer) $data['fileSize'] : 0,
                        'version' => $data['version']
        );
        
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
                    if ($term == 'all') {
                        continue;
                    }
                    $term = $taxonomyTermsService->findById($term);
                    
                    if (! $term) {
                        continue;
                    }
                    
                    if (! isset($termsArray[$term["id"]])) {
                        $termsArray[$term["id"]] = $taxonomyTermsService->getAncestors(
                                $term);
                        $termsArray[$term["id"]][] = $term;
                    }
                    
                    foreach ($termsArray[$term["id"]] as $tempTerm) {
                        $indexData['taxonomy'][$taxonomy['id']][] = $tempTerm['id'];
                    }
                }
            }
        }
        
        // Add target
        $indexData['target'] = array();
        if (isset($data['target'])) {
            foreach ($data['target'] as $key => $target) {
                $indexData['target'][] = (string) $target;
            }
        }
        
        // Add document
        $currentDam = new \Elastica\Document($data['id'], $indexData);
        
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
                $mongoFile = Manager::getService('Files')->FindById(
                        $data['originalFileId']);
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
     * Create or update index for existing user
     *
     * @see \Rubedo\Interfaces\IDataIndex::indexUser()
     * @param obj $data
     *            user data
     * @return array
     */
    public function indexUser ($data, $bulk = false)
    {
        if (! isset($data['fields'])) {
            return;
        }
        
        $typeId = $data['typeId'];
        
        // Load ES type
        $userType = self::$_user_index->getType($typeId);
        
        // Initialize data array to push into index
        
        $data['fields']['name'] = $data['name'];
        
        $indexData = array(
                'objectType' => 'user',
                'userType' => $typeId,
                'email' => $data['email'],
                'userType' => $typeId,
                'lastUpdateTime' => (isset($data['lastUpdateTime'])) ? (string) ($data['lastUpdateTime'] *
                         1000) : 0,
                        'fields' => $data['fields'],
                        'photo' => isset($data['photo']) ? $data['photo'] : null
        );
        
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
                    if ($term == 'all') {
                        continue;
                    }
                    $term = $taxonomyTermsService->findById($term);
                    
                    if (! $term) {
                        continue;
                    }
                    
                    if (! isset($termsArray[$term["id"]])) {
                        $termsArray[$term["id"]] = $taxonomyTermsService->getAncestors(
                                $term);
                        $termsArray[$term["id"]][] = $term;
                    }
                    
                    foreach ($termsArray[$term["id"]] as $tempTerm) {
                        $indexData['taxonomy.' . $taxonomy['id']][] = $tempTerm['id'];
                    }
                }
            }
        }
        
        // Add document
        $currentDocument = new \Elastica\Document($data['id'], $indexData);
        
        if (isset($indexData['attachment']) && $indexData['attachment'] != '') {
            $currentDocument->addFile('file', $indexData['attachment']);
        }
        
        // Add content to content type index
        if (! $bulk) {
            $userType->addDocument($currentDocument);
            $userType->getIndex()->refresh();
        } else {
            $this->_documents[] = $currentDocument;
        }
    }

    /**
     * Reindex all content or dam
     *
     * @param string $option
     *            : dam, content, user or all
     *            
     * @return array
     */
    public function indexAll ($option = 'all')
    {
        // for big data set
        set_time_limit(240);
        
        // Initialize result array
        $result = array();
        
        if ($option == 'all' or $option == 'content') {
            // Destroy and re-create content index
            self::$_content_index->delete();
            self::$_content_index->create(self::$_content_index_param, true);
        }
        
        if ($option == 'all' or $option == 'dam') {
            // Destroy and re-create dam index
            self::$_dam_index->delete();
            self::$_dam_index->create(self::$_dam_index_param, true);
        }
        
        $contentsService = Manager::getService('Contents');
        $damService = Manager::getService('Dam');
        
        if ($option == 'all' or $option == 'content') {
            
            // Retreive all content types
            $contentTypeList = Manager::getService('ContentTypes')->getList();
            
            foreach ($contentTypeList["data"] as $contentType) {
                
                // System contents are not indexed
                if (! isset($contentType['system']) or
                         $contentType['system'] == FALSE) {
                    
                    // Create content type with overwrite set to true
                    $this->indexContentType($contentType["id"], $contentType);
                    
                    // Reindex all contents from given type
                    $result = array_merge($result, 
                            $this->indexByType("content", $contentType["id"]));
                }
            }
        }
        
        if ($option == 'all' or $option == 'dam') {
            
            // Retreive all dam types
            $damTypeList = Manager::getService('DamTypes')->getList();
            
            foreach ($damTypeList["data"] as $damType) {
                
                // Create dam type with overwrite set to true
                $this->indexdamType($damType["id"], $damType);
                
                // Reindex all assets from given type
                $result = array_merge($result, 
                        $this->indexByType("dam", $damType["id"]));
            }
        }
        
        if ($option == 'all' or $option == 'user') {
            
            // Retreive all user types
            $userTypeList = Manager::getService('UserTypes')->getList();
            
            foreach ($userTypeList["data"] as $userType) {
                
                // Create user type with overwrite set to true
                $this->indexUserType($userType["id"], $userType);
                
                // Reindex all assets from given type
                $result = array_merge($result, 
                        $this->indexByType("user", $userType["id"]));
            }
        }
        
        return ($result);
    }

    /**
     * Reindex all content or dam or user for one type
     *
     * @param string $option
     *            : dam or content or user
     * @param string $id
     *            : dam type or content type or user type id
     *            
     * @return array
     */
    public function indexByType ($option, $id)
    {
        // for big data set
        set_time_limit(240);
        
        // Initialize result array and variables
        $result = array();
        $itemCount = 0;
        $this->_documents = array();
        
        // Retrieve data and ES index for type
        switch ($option) {
            case 'content':
                $bulkSize = 500;
                $serviceData = 'Contents';
                $serviceType = 'ContentTypes';
                break;
            case 'dam':
                $bulkSize = 100;
                $serviceData = 'Dam';
                $serviceType = 'DamTypes';
                break;
            case 'user':
                $bulkSize = 500;
                $serviceData = 'Users';
                $serviceType = 'UserTypes';
                break;
            default:
                throw new \Rubedo\Exceptions\Server(
                        "Option argument should be set to content, dam or user", 
                        "Exception65");
                break;
        }
        
        // Retrieve data and ES index for type
        
        $type = Manager::getService($serviceType)->findById($id);
        
        // Index all dam or contents from given type
        $useQueue = class_exists("ZendJobQueue");
        
        if ($useQueue) {
            try {
                $queue = new \ZendJobQueue();
            } catch (\Exception $e) {
                $useQueue = false;
            }
        }
        
        if (! $useQueue) {
            
            do {
                
                $nbIndexedItems = $this->bulkIndex($option, $id, $itemCount, 
                        $bulkSize);
                
                $itemCount += $nbIndexedItems;
            } while ($nbIndexedItems == $bulkSize);
        } else {
            
            // Get total items to be indexed
            
            $dataService = Manager::getService($serviceData);
            
            $filter = Filter::factory('Value')->setName('typeId')->SetValue($id);
            
            $totalToBeIndexed = $dataService->count($filter);
            
            $start = 0;
            
            // Push jobs in queue
            
            if ($totalToBeIndexed > 0) {
                do {
                    
                    // $protocol = isset($_SERVER["HTTPS"]) ? "https://" :
                    // "http://";
                    $protocol = 'http://';
                    $jobID = $queue->createHttpJob(
                            $protocol . $_SERVER['HTTP_HOST'] .
                                     "/queue?service=ElasticDataIndex&class=bulkIndex&Option=$option&id=$id&start=$start&bulkSize=$bulkSize");
                    $start += $bulkSize;
                } while ($start < $totalToBeIndexed);
            }
            
            $itemCount = $totalToBeIndexed;
        }
        
        $result[$type['type']] = $itemCount;
        
        return ($result);
    }

    public function bulkIndex ($option, $id, $start, $bulkSize)
    {
        switch ($option) {
            case 'content':
                $serviceData = 'Contents';
                $serviceType = self::$_content_index->getType($id);
                break;
            case 'dam':
                $serviceData = 'Dam';
                $serviceType = self::$_dam_index->getType($id);
                break;
            case 'user':
                $serviceData = 'Users';
                $serviceType = self::$_user_index->getType($id);
                break;
            default:
                throw new \Rubedo\Exceptions\Server(
                        "Option argument should be set to content or dam", 
                        "Exception65");
                break;
        }
        
        $this->_documents = array();
        
        $dataService = Manager::getService($serviceData);
        $wasFiltered = $dataService::disableUserFilter();
        $itemList = $dataService->getByType($id, (int) $start, (int) $bulkSize);
        
        $dataService::disableUserFilter($wasFiltered);
        foreach ($itemList["data"] as $item) {
            switch ($option) {
                case 'content':
                    $this->indexContent($item, TRUE);
                    break;
                case 'dam':
                    $this->indexDam($item, TRUE);
                    break;
                case 'user':
                    $this->indexUser($item, TRUE);
                    break;
            }
        }
        
        if (! empty($this->_documents)) {
            
            $serviceType->addDocuments($this->_documents);
            $serviceType->getIndex()->refresh();
            empty($this->_documents);
            $return = count($itemList['data']);
        } else {
            
            $return = 0;
        }
        
        return $return;
    }
}
