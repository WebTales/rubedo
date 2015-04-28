<?php

/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Elastic;

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\Json\Json;
use Rubedo\Elastic\DataMapping;

/**
 * Class implementing the Rubedo API to Elastic Search indexing services using
 * PHP elasticsearch API
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataIndex extends DataAbstract
{

    /**
     * Contains content types already requested
     */
    protected $_contentTypeCache = [];

    /**
     * Contains dam types already requested
     */
    protected $_damTypeCache = [];

    /**
     * Contains the documents
     */
    protected $_documents;


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
    public function deleteContent($typeId, $id)
    {
    	$params = [
    		'index' => self::$_content_index['name'],
    		'type' => $typeId,
    		'id' => $id
    	];
    	$this->_client->delete($params);
    }

    /**
     * Delete existing dam from index
     *
     * @see \Rubedo\Interfaces\IDataIndex::deleteDam()
     * @param string $typeId
     *            dam type id
     * @param string $id
     *            content id
     * @return array
     */
    public function deleteDam($typeId, $id)
    {
    	$params = [
    		'index' => self::$_dam_index['name'],
    		'type' => $typeId,
    		'id' => $id
    	];
    	$this->_client->delete($params);
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
    public function deleteUser($typeId, $id)
    {
    	$params = [
    		'index' => self::$_user_index['name'],
    		'type' => $typeId,
    		'id' => $id
    	];
    	$this->_client->delete($params);
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
    public function indexContent($data, $bulk = false)
    {
        if (!isset($data['fields']) || !isset($data['i18n'])) {
            return;
        }

        $typeId = $data['typeId'];
        
        $indexName = self::$_content_index['name'];

        // get available languages
        $availableLanguages = array_keys($data['i18n']);

        // Initialize data array to push into index
        $indexData = [
            'objectType' => 'content',
            'contentType' => $typeId,
            'text' => $data['text'],
            'fields' => $data['fields'],
            'i18n' => $data['i18n'],
            'writeWorkspace' => $data['writeWorkspace'],
            'startPublicationDate' => $data['startPublicationDate'],
            'endPublicationDate' => $data['endPublicationDate'],
            'lastUpdateTime' => (isset($data['lastUpdateTime'])) ? (string)($data['lastUpdateTime'] * 1000) : 0,
            'status' => $data['status'],
            'createUser' => $data['createUser'],
            'availableLanguages' => $availableLanguages,
            'version' => $data['version'],
            'online' => $data['online']
        ];

        // Index product properties if exists
        if (isset($data['productProperties'])) {
            $indexData['productProperties'] = $data['productProperties'];
            $indexData['encodedProductProperties'] = Json::encode($data['productProperties']);
            if (isset($data['isProduct'])) {
                $indexData['isProduct'] = $data['isProduct'];
            }
        }

        // Add taxonomy
        if (isset($data["taxonomy"])) {

            $taxonomyService = Manager::getService('Taxonomy');
            $taxonomyTermsService = Manager::getService('TaxonomyTerms');

            foreach ($data["taxonomy"] as $vocabulary => $terms) {
                if (!is_array($terms)) {
                    continue;
                }

                $taxonomy = $taxonomyService->findById($vocabulary);
                $termsArray = [];

                foreach ($terms as $term) {
                    if ($term == 'all' or $term=="") {
                        continue;
                    }
                    $term = $taxonomyTermsService->findById($term);

                    if (!$term) {
                        continue;
                    }

                    if (!isset($termsArray[$term["id"]])) {
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
        $indexData['target'] = [];
        if (isset($data['target'])) {
            if (!is_array($data['target'])) {
                $data['target'] = [
                    $data['target']
                ];
            }
            foreach ($data['target'] as $target) {
                $indexData['target'][] = (string)$target;
            }
        }
        if (empty($indexData['target'])) {
            $indexData['target'][] = 'global';
        }

        // Add autocompletion fields and title
        foreach ($availableLanguages as $lang) {
            $title = isset($data['i18n'][$lang]['fields']['text']) ? $data['i18n'][$lang]['fields']['text'] : $data['text'];
            $indexData['autocomplete_' . $lang] = [
                'input' => $title,
                'output' => $title,
                'payload' => "{ \"type\" : \"content\",  \"id\" : \"" . $data['id'] . "\"}"
            ];
        }

        if (isset($indexData['attachment']) && $indexData['attachment'] != '') {
        	$indexData['file'] = base64_encode($indexData['attachment']);
        }

        // Add content to content type index
        if (!$bulk) {
        	           
        	$params = [
        		'index' => $indexName,
        		'type' => $typeId,
        		'body' => [
        			['index' => ['_id' => $data['id']]],
        			$indexData
        		]
        	];
        	$this->_client->bulk($params);
        	 
        	$this->_client->indices()->refresh(['index' => $indexName]);
        	
        } else {
            $this->_documents[] = ['index' => ['_id' => $data['id']]];
            $this->_documents[] = $indexData;
        }
    }

    /**
     * Create or update index for existing Dam document
     *
     * @param obj $data
     *            dam data
     * @return array
     */
    public function indexDam($data, $bulk = false)
    {
        if (!isset($data['typeId']) || isset($data['mainFileType']) && $data['mainFileType'] === 'Resource') {
            return;
        }
        $typeId = $data['typeId'];

        $indexName = self::$_dam_index['name'];
        
        // get available languages
        $availableLanguages = array_keys($data['i18n']);
        
        // Initialize data array to push into index     
        $indexData = [
            'objectType' => 'dam',
            'damType' => $typeId,
            'text' => $data['title'],
            'fields' => $data['fields'],
            'i18n' => $data['i18n'],
            'writeWorkspace' => $data['writeWorkspace'],
            'lastUpdateTime' => (isset($data['lastUpdateTime'])) ? (string)($data['lastUpdateTime'] *
                1000) : 0,
            'createUser' => $data['createUser'],
            'availableLanguages' => array_keys($data['i18n']),
            'fileSize' => isset($data['fileSize']) ? (integer)$data['fileSize'] : 0,
            'version' => $data['version']
        ];
               	
        // Add taxonomy
        if (isset($data["taxonomy"])) {

            $taxonomyService = Manager::getService('Taxonomy');
            $taxonomyTermsService = Manager::getService('TaxonomyTerms');

            foreach ($data["taxonomy"] as $vocabulary => $terms) {

                if (!is_array($terms)) {
                    continue;
                }
                $taxonomy = $taxonomyService->findById($vocabulary);
                $termsArray = [];

                foreach ($terms as $term) {
                    if ($term == 'all') {
                        continue;
                    }
                    $term = $taxonomyTermsService->findById($term);

                    if (!$term) {
                        continue;
                    }

                    if (!isset($termsArray[$term["id"]])) {
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

        // Add read workspace
        $indexData['target'] = [];
        if (isset($data['target'])) {
            foreach ($data['target'] as $target) {
                $indexData['target'][] = (string)$target;
            }
        }

        // Add autocompletion
        $mediaThumbnail = Manager::getService('Url')->mediaThumbnailUrl($data['id']);
        foreach ($availableLanguages as $lang) {
            $title = isset($data['i18n'][$lang]['fields']['title']) ? $data['i18n'][$lang]['fields']['title'] : $data['title'];
            $indexData['autocomplete_' . $lang] = [
                'input' => $title,
                'output' => $title,
                'payload' => "{ \"type\" : \"dam\",  \"id\" : \"" . $data['id'] . "\",  \"thumbnail\" : \"" . $mediaThumbnail . "\"}"
            ];
        }

        // Add document
        if (isset($data['originalFileId']) && $data['originalFileId'] != '') {

            $indexedFiles = [
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
            ];
            $mime = explode(';', $data['Content-Type']);

            if (in_array($mime[0], $indexedFiles)) {
                $mongoFile = Manager::getService('Files')->FindById($data['originalFileId']);
                $indexData['file'] = base64_encode($mongoFile->getBytes());
            }
        }

        // Add dam to dam type index
        if (!$bulk) {
        	
        	$params = [
        		'index' => $indexName,
        		'type' => $typeId,
        		'body' => [
        			['index' => ['_id' => $data['id']]],
        			$indexData
        		]
        	];
        	$this->_client->bulk($params);
        	 
        	$this->_client->indices()->refresh(['index' => $indexName]);
        	
        } else {
            $this->_documents[] = ['index' => ['_id' => $data['id']]];
            $this->_documents[] = $indexData;
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
    public function indexUser($data, $bulk = false)
    {
        if (!isset($data['fields'])) {
            return;
        }

        $typeId = $data['typeId'];

        $indexName = self::$_user_index['name'];

        // Initialize data array to push into index

        $data['fields']['name'] = $data['name'];

        $photo = isset($data['photo']) ? $data['photo'] : null;

        $indexData = [
            'objectType' => 'user',
            'userType' => $typeId,
            'text' => $data['name'],
            'email' => $data['email'],
            'createUser' => $data['createUser'],
            'lastUpdateTime' => (isset($data['lastUpdateTime'])) ? (string)($data['lastUpdateTime'] *
                1000) : 0,
            'fields' => $data['fields'],
            'photo' => $photo
        ];

        // Add taxonomy
        if (isset($data["taxonomy"])) {

            $taxonomyService = Manager::getService('Taxonomy');
            $taxonomyTermsService = Manager::getService('TaxonomyTerms');

            foreach ($data["taxonomy"] as $vocabulary => $terms) {
                if (!is_array($terms)) {
                    continue;
                }

                $taxonomy = $taxonomyService->findById($vocabulary);
                $termsArray = [];

                foreach ($terms as $term) {
                    if ($term == 'all') {
                        continue;
                    }
                    $term = $taxonomyTermsService->findById($term);

                    if (!$term) {
                        continue;
                    }

                    if (!isset($termsArray[$term["id"]])) {
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

        // Add autocompletion fields and title
        $userThumbnail = (!empty($photo)) ? Manager::getService('Url')->userAvatar($data['id'], 40, 40, "boxed") : null;

        $indexData['autocomplete_nonlocalized'] = [
            'input' => $data['name'],
            'output' => $data['name'],
            'payload' => "{ \"type\" : \"user\",  \"id\" : \"" . $data['id'] . "\", \"thumbnail\" : \"" . $userThumbnail . "\"}"
        ];

        // Add document
        if (isset($indexData['attachment']) && $indexData['attachment'] != '') {
            $indexData['file'] = base64_encode($indexData['attachment']);
        }

        // Add content to content type index
        if (!$bulk) {
        	
        	$params = [
        		'index' => $indexName,
        		'type' => $typeId,
        		'body' => [
        			['index' => ['_id' => $data['id']]],
        			$indexData
        		]
        	];
        	$this->_client->bulk($params);
        	 
        	$this->_client->indices()->refresh(['index' => $indexName]);
        	
        } else {
            $this->_documents[] = ['index' => ['_id' => $data['id']]];
            $this->_documents[] = $indexData;
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
    public function indexAll($option = 'all')
    {
        // for big data set
        set_time_limit(240);

        // Initialize result array
        $result = [];

        // Destroy and re-create content, dam and user indexes        
        if ($option == 'all' or $option == 'content') {
            $this->_client->indices()->delete(['index' =>  self::$_content_index['name']]);
        }

        if ($option == 'all' or $option == 'dam') {
            $this->_client->indices()->delete(['index' =>  self::$_dam_index['name']]);
        }

        if ($option == 'all' or $option == 'users') {
        	$this->_client->indices()->delete(['index' =>  self::$_user_index['name']]);
        }

        if ($option == 'all' or $option == 'content') {

            // Retreive all content types
            $contentTypeList = Manager::getService('ContentTypes')->getList();

            foreach ($contentTypeList["data"] as $contentType) {

                // System contents are not indexed
                if (!isset($contentType['system']) or
                    $contentType['system'] == FALSE
                ) {

                    // Create content type mapping with overwrite set to true
                    $mapping = new DataMapping();
                    $mapping->indexContentType($contentType["id"], $contentType);

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

                // Create dam type mapping with overwrite set to true
            	$mapping = new DataMapping();
            	$mapping->indexdamType($damType["id"], $damType);

                // Reindex all assets from given type
                $result = array_merge($result,
                    $this->indexByType("dam", $damType["id"]));
            }
        }

        if ($option == 'all' or $option == 'user') {

            // Retreive all user types
            $userTypeList = Manager::getService('UserTypes')->getList();

            foreach ($userTypeList["data"] as $userType) {

                // Create user type mapping with overwrite set to true
            	$mapping = new DataMapping();
            	$mapping->indexUserType($userType["id"], $userType);

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
    public function indexByType($option, $id)
    {
        // for big data set
        set_time_limit(240);

        // Initialize result array and variables
        $result = [];
        $itemCount = 0;
        $this->_documents = [];

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

        //if (!$useQueue) {
        if (true) {

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
                    $queue->createHttpJob(
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

    public function bulkIndex($option, $typeId, $start, $bulkSize)
    {
        switch ($option) {
            case 'content':
                $serviceData = 'Contents';
                $indexName = self::$_content_index['name'];
                break;
            case 'dam':
                $serviceData = 'Dam';
                $indexName = self::$_dam_index['name'];
                break;
            case 'user':
                $serviceData = 'Users';
                $indexName = self::$_user_index['name'];
                break;
            default:
                throw new \Rubedo\Exceptions\Server(
                    "Option argument should be set to content or dam",
                    "Exception65");
                break;
        }

        $this->_documents = [];

        $dataService = Manager::getService($serviceData);
        $wasFiltered = $dataService::disableUserFilter();
        $itemList = $dataService->getByType($typeId, (int)$start, (int)$bulkSize);

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

        if (!empty($this->_documents)) {
			
        	$params = array();
        	$params = [
        		'index' => $indexName,
        		'type' => $typeId,
        	];
        	
        	$params['body'] = $this->_documents;

        	$this->_client->bulk($params);        	

        	$this->_client->indices()->refresh(['index' => $indexName]);
        	
            empty($this->_documents);
            
            $return = count($itemList['data']);
            
        } else {

            $return = 0;
        }

        return $return;
    }
}
