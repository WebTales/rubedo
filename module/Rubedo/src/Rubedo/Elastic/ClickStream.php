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
 * Service to handle clickstream indexing and searching
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class ClickStream extends DataAbstract
{

	/**
	 * Mapping
	 */

  protected static $_type = 'clickstream';

	protected static $_mapping = [
		'@timestamp' => [
			'type' => 'date',
			'store' => 'yes'
		],
		'date' => [
			'type' => 'date',
			'store' => 'yes'
		],
		'fingerprint' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'sessionId' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'event' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'browser' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'browserVersion' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'city' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'country' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'os' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'referer' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'refereringDomain' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'region' => [
			'type' => 'string',
			'index' => 'not_analyzed',
			'store' => 'yes'
		],
		'screenHeight' => [
			'type' => 'integer',
			'store' => 'yes'
		],
		'screenWidth' => [
			'type' => 'integer',
			'store' => 'yes'
		],
		'geoip' => [
			'type' => 'geo_point',
			'store' => 'yes'
		]
	];

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$today = date("Y.m.d");
		$this->_indexName = $this->getIndexNameFromConfig('clickSteamIndex')."-".$today;
		parent::init();
		// Create type and mapping if necessary
		$params = [
				'index' => $this->_indexName,
				'type' => self::$_type
		];
		if (!$this->_client->indices()->existsType($params)) {
				$this->putMapping(self::$_type, self::$_mapping);
		}
	}

    /**
     * Index
     *
     * @param obj $data content data
     * @param boolean $bulk
     * @return array
     */
	public function index($data, $bulk = false)
	{

		// Initialize data array to push into index
		$body = [
			'@timestamp' => (isset($data['timestamp'])) ? $data['timestamp'] : time(),
			'date' => $data['date'],
			'fingerprint' => $data['fingerprint'],
			'sessionId' => $data['sessionId'],
			'event' => $data['event'],
			'browser' => $data['browser'],
			'browserVersion' => $data['browserVersion'],
			'city' => $data['city'],
			'country' => $data['country'],
			'os' => $data['os'],
			'referer' => $data['referer'],
			'refereringDomain' => $data['refereringDomain'],
			'region' => $data['region'],
			'screenHeight' => $data['screenHeight'],
			'screenWidth' => $data['screenWidth'],
			'geoip' => $data['geoip']
		];

		// Add content to clickstream index
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
