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
 * Service to handle Content types mapping
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class ContentTypes extends DataAbstract
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
			'analyzer' => 'simple',
			'search_analyzer' => 'simple',
			'payloads' => true,
			'preserve_position_increments' => false
		],
		'text' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'target' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'writeWorkspace' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'availableLanguages' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'version' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'summary' => [
			'type' => 'string',
			'store' => 'yes'
		],
		'contentType' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'online' => [
			'type' => 'boolean',
			'store' => 'yes'
		]
	];

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_indexName = $this->getIndexNameFromConfig('contentIndex');
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

		if (isset ($data ['fields']) && is_array($data ['fields'])) {

			// get vocabularies
			$vocabularies = $this->getVocabularies($data);

			// add mapping for autocomplete in every active language
			foreach ($this->_activeLanguages as $lang) {
				$locale = !in_array($lang ['locale'], $this->_activeAnalysers) ? 'default' : $lang ['locale'];
				$mapping ['autocomplete_' . $lang ['locale']] = [
					'type' => 'completion',
					'analyzer' => $locale . '_analyzer',
					'search_analyzer' => $locale . '_analyzer',
					'payloads' => true,
					'preserve_position_increments' => false
				];
			}

			// Add Taxonomies
			foreach ($vocabularies as $vocabularyName) {
				$mapping ["taxonomy_" . $vocabularyName] = [
					'type' => 'string',
					'index' => 'not_analyzed',
					'store' => 'yes'
				];
			}

			// Add system fields : text and summary
			$fields = $data['fields'];

			$fields [] = [
				'cType' => 'system',
				'config' => [
					'name' => 'text',
					'fieldLabel' => 'text',
					'searchable' => true,
					'localizable' => true
				]
			];
			$fields [] = [
				'cType' => 'system',
				'config' => [
					'name' => 'summary',
					'fieldLabel' => 'summary',
					'searchable' => true,
					'localizable' => true
				]
			];

			// unmapped fields are not allowed in fields, i18n and productProperties
			$mapping ['fields'] = [
				'dynamic' => false,
				'type' => 'object'
			];

			foreach ($this->_activeLanguages as $lang) {
				$mapping ['i18n'] ['properties'] [$lang ['locale']] ['properties'] ['fields'] = [
					'dynamic' => false,
					'type' => 'object'
				];
			}

			// Add properties for product only
			if (isset($data['productType']) && $data['productType'] != 'none') {
				$mapping ['productProperties'] = [
					'dynamic' => true,
					'store' => 'yes',
					'type' => 'object',
					'properties'=>[
						'basePrice' => [
							'type' => 'float'
						],
						'variations'=>[
							'dynamic' => true,
							'type' => 'object',
							'properties'=>[
								'price' => [
									'type' => 'float'
								]
							]
						]
					]
				];
				$mapping['encodedProductProperties']=[
					'store'=>'yes',
					'type'=>'string',
					'index'=>'no'
				];

				$mapping['isProduct'] = [
					'type' => 'boolean',
                    'store' => 'yes'
	            ];
			}

			// add fields mappings
			foreach ($fields as $field) {
				$this->addFieldMapping($field,$mapping);
			}
		}

		return array_merge(self::$_mapping, $mapping);
	}

	/**
	 * Set mapping for new or updated content type
	 *
	 * @param string $typeId
	 * @param array $data
	 *            content type data
	 * @return array
	 */
	public function setMapping($typeId, $data)
	{

		// Delete existing content type
		// $this->deleteMapping($this->_indexName, $typeId);

		// Create mapping
		$this->putMapping($this->_indexName, $typeId, $this->getMapping($data));

	}

	/**
	 * Delete content type mapping
	 *
	 * @param string $typeId
	 *            content type id
	 * @return array
	 */
	public function delete($typeId)
	{
		$this->deleteMapping($this->_indexName, $typeId);
	}

	/**
	 * Index all existing contents from given type
	 *
	 * @param string $typeId
	 *            object type id
	 * @param string $id
	 *            object id
	 */
	public function index($typeId)
	{
		return $this->indexByType('content', $typeId);
	}

}
