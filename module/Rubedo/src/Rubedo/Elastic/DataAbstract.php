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
use Zend\Json\Json;


/**
 * Class implementing the Rubedo API to Elastic Search indexing services using Elasticsearch API
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataAbstract
{

    /**
     * Default value of hostname
     *
     * Used by the constructor if no specific params
     *
     * @var string
     */
    protected static $_defaultHost;

    /**
     * Default transport value
     *
     * Used by the constructor if no specific params
     *
     * @var string
     */
    protected static $_defaultTransport;

    /**
     * Default port value
     *
     * Used by the constructor if no specific params
     *
     * @var string
     */
    protected static $_defaultPort;

    /**
     * Elastic search client
     *
     * @var \Elasticsearch_Client
     */
    protected $_client;

    /**
     * Configuration options
     *
     * @var array
     */
    protected static $_options;

    /**
     * Content Index object
     *
     * @var array
     */
    protected static $_content_index;

    /**
     * Content Index param
     *
     * @var array
     */
    protected static $_content_index_param;
    
    /**
     * Dam index object
     *
     * @var string
     */
    protected static $_dam_index;

    /**
     * User index object
     *
     * @var array
     */
    protected static $_user_index;


    public function __construct()
    {
        if (!isset(self::$_options)) {
            self::lazyLoadConfig();
        }
    }

    /**
     * Initialize a search service handler to index or query Elastic Search
     *
     * @see \Rubedo\Interfaces\IDataIndex::init()
     * @param string $host
     *            http host name
     * @param string $port
     *            http port
     */
    public function init($host = null, $port = null)
    {
        if (is_null($host)) {
            $host = self::$_options['host'];
        }

        if (is_null($port)) {
            $port = self::$_options['port'];
        }

        $params = array();
        $params['hosts'] = array ("$host:$port");

        $this->_client = new \Elasticsearch\Client($params);

        //$this->_client->setLogger(Manager::getService('SearchLogger')->getLogger());

        $dataAccess = Manager::getService('MongoDataAccess');
        $defaultDB = $dataAccess::getDefaultDb();
        $defaultDB = mb_convert_case($defaultDB, MB_CASE_LOWER, "UTF-8");

        // Create content index if not exists 
        self::$_content_index['name'] = $defaultDB . "-" . self::$_options['contentIndex'];
		if (!$this->_client->indices()->exists(['index' => array(self::$_content_index['name'])])) {
			self::$_content_index_param = [
				'index' => self::$_content_index['name'], 
				'body' => [
					'settings' => self::$_content_index['settings']
				]
			];
			$this->_client->indices()->create(self::$_content_index_param);
		}

		// Create dam index if not exists
		self::$_dam_index['name'] = $defaultDB . "-" . self::$_options['damIndex'];
		if (!$this->_client->indices()->exists(['index' => array(self::$_dam_index['name'])])) {
			$damIndexParams = [
				'index' => self::$_dam_index['name'],
				'body' => [
					'settings' => self::$_dam_index['settings']
				]
			];
			$this->_client->indices()->create($damIndexParams);
		}

		// Create user index if not exists
		self::$_user_index['name'] = $defaultDB . "-" . self::$_options['userIndex'];
		if (!$this->_client->indices()->exists(['index' => array(self::$_user_index['name'])])) {
			$userIndexParams = [
				'index' => self::$_user_index['name'],
				'body' => [
					'settings' => self::$_user_index['settings']
				]
			];
			$this->_client->indices()->create($userIndexParams);
		}

    }

    /**
     * Set the options for ES connection
     *
     * @param array $options
     */
    public static function setOptions(array $options)
    {
        self::$_options = $options;
    }

    /**
     *
     * @return the $_options
     */
    public static function getOptions()
    {
        if (!isset(self::$_options)) {
            self::lazyLoadConfig();
        }
        return self::$_options;
    }

    /**
     * Return the ElasticSearch Server Version
     *
     * @return string
     */
    public function getVersion()
    {
        $data = $this->_client->info();
        if (isset($data['version']) && isset($data['version']['number'])) {
            return $data['version']['number'];
        }
        return null;
    }

    /**
     * Read configuration from global application config and load it for the current class
     */
    public static function lazyLoadConfig()
    {
        $options = Manager::getService('config');
        if (isset($options)) {
            self::setOptions($options['elastic']);
        }
        $indexOptionsJson = file_get_contents($options['elastic']['configFilePath']);
        $indexOptions = Json::decode($indexOptionsJson, Json::TYPE_ARRAY);
        self::$_content_index['settings'] = $indexOptions;
        self::$_dam_index['settings'] = $indexOptions;
        self::$_user_index['settings'] = $indexOptions;
    }
}
