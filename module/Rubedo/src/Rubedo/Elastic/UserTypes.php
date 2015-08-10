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
 * Service to handle User types mapping
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class UserTypes extends DataAbstract
{
		
	/**
	 * Mapping
	 */
	protected static $_mapping = [
		'objectType' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'typeId' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'author' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'createUser' => [
			'type' => 'object',
			'store' => 'yes',
			'properties' => [
				'id' => [
					'type' => 'string',
					'index' => 'not_analyzed',
					'store' => 'yes'
				],
				'fullName' => [
					'type' => 'string',
					'index' => 'not_analyzed',
					'store' => 'yes'
				],
				'login' => [
					'type' => 'string',
					'index' => 'no',
					'store' => 'no'
				]
			]
		],
		'startPublicationDate' => [
			'type' => 'integer',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'endPublicationDate' => [
			'type' => 'integer',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'lastUpdateTime' => [
			'type' => 'date',
			'store' => 'yes'
		],
		'autocomplete_nonlocalized' => [
			'type' => 'completion',
			'index_analyzer' => 'simple',
			'search_analyzer' => 'simple',
			'payloads' => true,
			'preserve_position_increments' => false
		],
		'email' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'photo' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'userType' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'status' => [
			'type' => 'string',
			'index' => 'no',
			'store' => 'yes'
		]
	];

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_indexName = $this->getIndexNameFromConfig('userIndex');
		parent::init();
		
		parent::getAnalyzers();
		parent::getLanguages();
	}
	
	/**
	 * Build mapping for object
	 *
	 * @param array $data
	 * @return array
	 */
	public function getMapping(array $data)
	{
	
	    $mapping = [];

        if (isset($data['fields']) && is_array($data['fields'])) {

            // get vocabularies
            $vocabularies = $this->getVocabularies($data);

            // Add Taxonomies
            foreach ($vocabularies as $vocabularyName) {
                $mapping["taxonomy." . $vocabularyName] = [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'store' => 'yes'
                ];
            }

            // Add Fields
            $fields = $data['fields'];

            // Add system fields : email and name for user
            $fields[] = [
                'cType' => 'system',
                'config' => [
                    'name' => 'email',
                    'fieldLabel' => 'email',
                    'searchable' => false
                ]
            ];
            $fields[] = [
                'cType' => 'system',
                'config' => [
                    'name' => 'name',
                    'fieldLabel' => 'name',
                    'searchable' => true,
                    'notAnalyzed' => false
                ]
            ];

            // unmapped fields are not allowed in fields and i18n
            $mapping['fields'] = [
                'dynamic' => false,
                'type' => 'object'
            ];

            // add fields mappings
            foreach ($fields as $field) {
            	$this->addFieldMapping($field,$mapping);
            }
            
        }
	
		return array_merge(self::$_mapping, $mapping);
	}
	
	/**
	 * Set mapping for new or updated user type
	 *
	 * @param string $typeId
	 * @param array $data
	 *            user type data
	 * @return array
	 */
	public function setMapping($typeId, $data)
	{
			// Delete existing content type
		$this->deleteMapping($this->_indexName, $typeId);
	
		// Create mapping
		return $this->putMapping($this->_indexName, $typeId, $this->getMapping($data));
	}
	
	/**
	 * Delete user type mapping
	 *
	 * @param string $typeId
	 *            user type id
	 * @return array
	 */
	public function delete($typeId)
	{
		return $this->deleteMapping($this->_indexName, $typeId);
	}
	
	/**
	 * Index all existing users from given type
	 *
	 * @param string $typeId
	 *            object type id
	 * @param string $id
	 *            object id
	 */
	public function index($typeId)
	{
		return $this->indexByType('user', $typeId);
	}	
}
