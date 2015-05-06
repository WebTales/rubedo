<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2015, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2015 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Elastic;

/**
 * Service to handle Contents indexing and searching
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class Users extends DataAbstract
{
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_indexName = $this->getIndexNameFromConfig('damIndex');
		parent::init();
	}
	
    /**
     * Create or update index for existing user
     *
     * @param obj $data user data
     * @param boolean $bulk
     * @return array
     */
	public function index($data, $bulk = false)
	{
	    if (!isset($data['fields'])) {
            return;
        }
        
        $typeId = $data['typeId'];
        
        // Initialize data array to push into index
        
        $data['fields']['name'] = $data['name'];
        
        $photo = isset($data['photo']) ? $data['photo'] : null;
        
        $indexData = [
        	'objectType' => 'user',
        	'userType' => $typeId,
        	'text' => $data['name'],
        	'email' => $data['email'],
        	'createUser' => $data['createUser'],
        	'lastUpdateTime' => (isset($data['lastUpdateTime'])) ? (string)($data['lastUpdateTime'] *1000) : 0,
        	'fields' => $data['fields'],
        	'photo' => $photo
        ];
        
        // Add taxonomy
        if (isset($data["taxonomy"])) {
        
        	$taxonomyService = $this->_getService('Taxonomy');
        	$taxonomyTermsService = $this->_getService('TaxonomyTerms');
        
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
        $userThumbnail = (!empty($photo)) ? $this->_getService('Url')->userAvatar($data['id'], 40, 40, "boxed") : null;
        
        $indexData['autocomplete_nonlocalized'] = [
        'input' => $data['name'],
        'output' => $data['name'],
        'payload' => "{ \"type\" : \"user\",  \"id\" : \"" . $data['id'] . "\", \"thumbnail\" : \"" . $userThumbnail . "\"}"
        		];
        
        // Add document
        if (isset($indexData['attachment']) && $indexData['attachment'] != '') {
        	$indexData['file'] = base64_encode($indexData['attachment']);
        }        

        // Add user to user type index
        $body = [
        	['index' => ['_id' => $data['id']]],
        	$indexData
        ];        
        if (!$bulk) {
        	$params = [
        		'index' => $this->_indexName,
        		'type' => $typeId,
        		'body' => $body
        	];
        	$this->_client->bulk($params);
        	 
        	$this->_client->indices()->refresh(['index' => $this->_indexName]);
        	
        } else {
			return $body;
        }
	}	
	
	/**
	 * Delete existing user from index
	 *
	 * @param string $typeId
	 *            user type id
	 * @param string $id
	 *            user id
	 */
	public function delete($typeId, $id)
	{
		$params = [
			'index' => $this->_indexName,
			'type' => $typeId,
			'id' => $id
		];
		$this->_client->delete($params);
	}
	
	
}
