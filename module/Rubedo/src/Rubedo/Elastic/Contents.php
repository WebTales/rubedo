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

use Zend\Json\Json;

/**
 * Service to handle Contents indexing and searching
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class Contents extends DataAbstract
{

	protected $typesArray = array();
	protected $service = 'ContentTypes';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_indexName = $this->getIndexNameFromConfig('contentIndex');
		parent::init();
	}

    /**
     * Create or update index for existing content
     *
     * @param obj $data content data
     * @param boolean $bulk
     * @return array
     */
	public function index($data, $bulk = false)
	{
		if (!isset($data['fields']) || !isset($data['i18n'])) {
			return;
		}

		$typeId = $data['typeId'];

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

		// Normalize date fields
		$contentType = $this->_getType($typeId);
		foreach ($contentType['fields'] as $field) {
			if ($field['cType'] == 'datefield' or $field['cType'] == 'Ext.form.field.Date') {
				$fieldName = $field['config']['name'];
				if (isset($indexData['fields'][$fieldName])) {
					$ts = intval($indexData['fields'][$fieldName]);
					$indexData['fields'][$fieldName] = mktime(0, 0, 0, date('m', $ts), date('d', $ts), date('Y', $ts))*1000;
				}
			}
		}

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

			foreach ($data["taxonomy"] as $vocabulary => $terms) {
				if (!is_array($terms)) {
					$terms = [$terms];
				}

				$taxonomy = $this->_getService('Taxonomy')->findById($vocabulary);
				$termsArray = [];

				foreach ($terms as $term) {
					if ($term == 'all' or $term=="") {
						continue;
					}
					$term = $this->_getService('TaxonomyTerms')->findById($term);

					if (!$term) {
						continue;
					}

					if (!isset($termsArray[$term["id"]])) {
						$termsArray[$term["id"]] = $this->_getService('TaxonomyTerms')->getAncestors(
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
	 * Delete existing content from index
	 *
	 * @param string $typeId
	 *            content type id
	 * @param string $id
	 *            content id
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
