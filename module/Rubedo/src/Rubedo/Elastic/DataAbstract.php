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
use WebTales\MongoFilters\Filter;
use Elasticsearch\ClientBuilder;

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
     * Index settings
     *
     * @var array
     */
    protected static $_indexSettings;

    /**
     * Index Name
     *
     * @var array
     */
    protected $_indexName;

    /**
     * Active languages
     *
     * @var array
     */
    protected $_activeLanguages;

    /**
     * Active analyzers
     *
     * @var array
     */
    protected $_activeAnalysers;

    /**
     * Documents queue for indexing
     *
     * @var array
     */
    protected $_documents;

    /**
     * Load ES configuration from file
     */
    public function __construct()
    {
        if (!isset(self::$_options)) {
            self::lazyLoadConfig();
        }
    }

    /**
     * Initialize a search service handler to index or query Elastic Search
     *
     * @param string $host
     *            http host name
     * @param string $port
     *            http port
     */
    public function init($host = null)
    {
        if (is_null($host)) {
            $host = self::$_options['host'];
        }

        $hosts = explode(",",$host);
        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setHosts($hosts);
        $this->_client = $clientBuilder->build();

        //$this->_client->setLogger(Manager::getService('SearchLogger')->getLogger());

        // Create index if not exists
        if (isset($this->_indexName)) {

            if (!$this->_client->indices()->exists(['index' => array($this->_indexName)])) {
                $params = [
                    'index' => $this->_indexName,
                    'body' => [
                        'settings' => self::$_indexSettings
                    ]
                ];
                $this->_client->indices()->create($params);
            }
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
     * Getter for cached service
     *
     * @param string $serviceName
     *
     * @return object
     */
    public function _getService($serviceName) {
        if (!isset($this->_services[$serviceName])) {
            $this->_services[$serviceName] = Manager::getService($serviceName);
        }
        return $this->_services[$serviceName];
    }

    /**
     * Return the index name from configuration file
     *
     * @return string
     */
    public function getIndexNameFromConfig($optionName)
    {
        $dataAccess = $this->_getService('MongoDataAccess');
        $defaultDB = $dataAccess::getDefaultDb();
        $defaultDB = mb_convert_case($defaultDB, MB_CASE_LOWER, "UTF-8");

        return $defaultDB . "-" . self::$_options[$optionName];

    }

    /**
     * Read the active analyzers from config
     */
    public function getAnalyzers()
    {
        $this->_activeAnalysers = array_keys(self::$_indexSettings['analysis'] ['analyzer']);
    }

    /**
     * Read the active languages
     */
    public function getLanguages()
    {
        $this->_activeLanguages = $this->_getService('Languages')->getActiveLanguages();
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
    public function lazyLoadConfig()
    {
        $options = $this->_getService('config');
        if (isset($options)) {
            self::setOptions($options['elastic']);
        }
        $indexOptionsJson = file_get_contents($options['elastic']['configFilePath']);
        $indexOptions = Json::decode($indexOptionsJson, Json::TYPE_ARRAY);
        self::$_indexSettings = $indexOptions;
    }

    /**
     * Return all the vocabularies contained in the id list
     *
     * @param array $data
     *            contain vocabularies id of the current object
     * @return array
     */
    protected function getVocabularies($data)
    {
        $vocabularies = [];
        foreach ($data['vocabularies'] as $vocabularyId) {
            $vocabulary = $this->_getService('Taxonomy')->findById(
                    $vocabularyId);
            $vocabularies[] = $vocabulary['id'];
        }

        return $vocabularies;
    }

    /**
     * Add field mapping to global mapping
     *
     * @param array $field
     *            contains field configuration
     * @param array $mapping
     *            contains global mapping to update
     */
    protected function addFieldMapping($field,&$mapping) {

        // Only searchable fields get indexed
        if ($field ['config'] ['searchable']) {

            $name = $field ['config'] ['name'];
            $store = (isset ($field ['config'] ['returnInSearch']) && $field ['config'] ['returnInSearch'] == FALSE) ? 'no' : 'yes';
            $notAnalyzed = (isset ($field ['config'] ['notAnalyzed']) && $field ['config'] ['notAnalyzed']) ? TRUE : FALSE;

            // For classical fields
            if (!isset($field ['config'] ['useAsVariation']) or ($field ['config'] ['useAsVariation'] == false)) {

                switch ($field ['cType']) {
                    case 'datefield' :
                        $config = [
                            'type' => 'string',
                            'store' => $store
                        ];
                        if ($notAnalyzed) {
                            $config ['index'] = 'not_analyzed';
                        }
                        if (!$field ['config'] ['localizable']) {
                            $mapping ['fields'] ['properties'] [$name] = $config;
                        } else {
                            foreach ($this->_activeLanguages as $lang) {
                                $mapping ['i18n'] ['properties'] [$lang ['locale']] ['properties'] ['fields'] [$name] = $config;
                            }
                        }
                        break;
                    case 'RECField' :
                    case 'Rubedo.view.RECField' :
                        $config = [
                            'type' => 'object',
                            'store' => $store,
                            'properties'=>[ ]
                        ];
                        if (isset($field['config']['usedCT'])&&$field['config']['usedCT']!=""){
                            $subCT=Manager::getService("ContentTypes")->findById($field['config']['usedCT']);
                            if($subCT){
                                foreach ($subCT["fields"] as $subfield) {
                                    $this->addFieldMapping($subfield,$config['properties']);
                                }
                            }

                        }
                        if ($notAnalyzed) {
                            $config ['index'] = 'not_analyzed';
                        }
                        if (!$field ['config'] ['localizable']) {
                            $mapping ['fields'] ['properties'] [$name] = $config;
                        } else {
                            foreach ($this->_activeLanguages as $lang) {
                                $mapping ['i18n'] ['properties'] [$lang ['locale']] ['properties'] ['fields'] [$name] = $config;
                            }
                        }
                        break;
                    case 'urlField' :
                    case 'Rubedo.view.urlField' :
                        $config = [
                            'type' => 'object',
                            'store' => $store,
                            'properties'=>[
                                "url" => ["type" => "string",'store' => $store],
                                "title" => ["type" => "string",'store' => $store],
                                "openInNewWindow" => ["type" => "boolean",'store' => $store],
                            ]
                        ];
                        if ($notAnalyzed) {
                            $config ['index'] = 'not_analyzed';
                        }
                        if (!$field ['config'] ['localizable']) {
                            $mapping ['fields'] ['properties'] [$name] = $config;
                        } else {
                            foreach ($this->_activeLanguages as $lang) {
                                $mapping ['i18n'] ['properties'] [$lang ['locale']] ['properties'] ['fields'] [$name] = $config;
                            }
                        }
                        break;
                    case 'externalMediaField' :
                    case 'Rubedo.view.externalMediaField' :
                        $config = [
                            'type' => 'object',
                            'store' => $store,
                            'properties'=>[
                                "url" => ["type" => "string",'store' => $store],
                                "maxHeight" => ["type" => "integer",'store' => $store,"index"=>"no"],
                                "minHeight" => ["type" => "integer",'store' => $store,"index"=>"no"],
                            ]
                        ];
                        if ($notAnalyzed) {
                            $config ['index'] = 'not_analyzed';
                        }
                        if (!$field ['config'] ['localizable']) {
                            $mapping ['fields'] ['properties'] [$name] = $config;
                        } else {
                            foreach ($this->_activeLanguages as $lang) {
                                $mapping ['i18n'] ['properties'] [$lang ['locale']] ['properties'] ['fields'] [$name] = $config;
                            }
                        }
                        break;
                    case 'numberfield' :
                        $config = [
                            'type' => 'float',
                            'store' => $store
                        ];
                        if ($notAnalyzed) {
                            $config ['index'] = 'not_analyzed';
                        }
                        if (!$field ['config'] ['localizable']) {
                            $mapping ['fields'] ['properties'] [$name] = $config;
                        } else {
                            foreach ($this->_activeLanguages as $lang) {
                                $mapping ['i18n'] ['properties'] [$lang ['locale']] ['properties'] ['fields'] [$name] = $config;
                            }
                        }
                        break;
                    case 'document' :
                        $config = [
                            'type' => 'attachment',
                            'store' => $store
                        ];
                        if ($notAnalyzed) {
                            $config ['index'] = 'not_analyzed';
                        }
                        if (!$field ['config'] ['localizable']) {
                            $mapping ['fields'] ['properties'] [$name] = $config;
                        } else {
                            foreach ($this->_activeLanguages as $lang) {
                                $mapping ['i18n'] ['properties'] [$lang ['locale']] ['properties'] ['fields'] [$name] = $config;
                            }
                        }
                        break;
                    case 'Rubedo.view.localiserField' :
                    case 'localiserField' :
                        $config = [
                            'properties' => [
                                'location' => [
                                    'properties' => [
                                        'coordinates' => [
                                            'type' => 'geo_point',
                                            'store' => 'yes'
                                        ]
                                    ]
                                ],
                                'address' => [
                                    'type' => 'string',
                                    'store' => 'yes'
                                ]
                            ]
                        ];
                        if (!$field ['config'] ['localizable']) {
                            $mapping ['fields'] ['properties'] [$name] = $config;
                        } else {
                            foreach ($this->_activeLanguages as $lang) {
                                $mapping ['i18n'] ['properties'] [$lang ['locale']] ['properties'] ['fields'] [$name] = $config;
                            }
                        }
                        break;
                    default :
                        // Default mapping for non localizable fields
                        if (!isset($field ['config'] ['localizable']) || !$field ['config'] ['localizable']) {
                            $config = [
                                'type' => 'string',
                                'index' => (!$notAnalyzed) ? 'analyzed' : 'not_analyzed',
                                'copy_to' => ['all_nonlocalized'],
                                'store' => $store
                            ];
                            // User name particular case
                            if ($name == 'name') {
                                $config['fields']['first_letter'] = [
                                    'type' => 'string',
                                    'analyzer' => 'first_letter'
                                ];
                            }
                            $mapping ['fields'] ['properties'] [$name] = $config;
                        } else {
                            // Mapping for localizable fields
                            foreach ($this->_activeLanguages as $lang) {
                                $locale = $lang ['locale'];
                                $fieldName = $name . '_' . $locale;
                                $_all = 'all_' . $locale;
                                $_autocomplete = 'autocomplete_' . $locale;
                                if (in_array($locale . '_analyzer', $this->_activeAnalysers)) {
                                    $lg_analyser = $locale . '_analyzer';
                                } else {
                                    $lg_analyser = 'default';
                                }
                                $config = [
                                    'type' => 'string',
                                    'index' => (!$notAnalyzed) ? 'analyzed' : 'not_analyzed',
                                    'analyzer' => $lg_analyser,
                                    'copy_to' => [
                                        $_all
                                    ],
                                    'store' => $store
                                ];

                                $mapping [$fieldName] = $config;
                                $mapping ['i18n'] ['properties'] [$locale] ['properties'] ['fields'] ['properties'] [$name] = $config;
                            }
                        }
                }
            } else { // Product variation field
                $_all = 'all_nonlocalized';
                $config = [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'copy_to' => [
                        $_all
                    ],
                    'store' => $store
                ];
                $mapping ['productProperties']['properties']['variations']['properties'][$name] = $config;
            }
        }
    }

    /**
     * Set mapping for new or updated object type
     *
     * @param string $indexName
     * @param string $typeId
     * @param array $mapping
     *
     * @return array
     */
    public function putMapping($indexName, $typeId, $mapping)
    {

        // Delete existing content type
        $this->deleteMapping($indexName, $typeId);

        // Create new ES type if not empty
        if (!empty($mapping)) {

            // Create new type

            $indexParams = [
                'index' => $indexName,
                'type' => $typeId,
                'body' => [
                    $typeId => ['properties' => $mapping]
                ]
            ];

            $this->_client->indices()->putMapping($indexParams);

            // Return indexed field list
            return array_flip(array_keys($mapping));
        } else {
            // If there is no searchable field, the new type is not created
            return [];
        }
    }

    /**
     * Delete object type mapping
     *
     * @param string $indexName
     * @param string $typeId
     *
     * @return array
     */
    public function deleteMapping($indexName, $typeId)
    {
        $params = [
            'index' => $indexName,
            'type' => $typeId
        ];

        if ($this->_client->indices()->existsType($params)) {
            return $this->_client->indices()->deleteMapping($params);
        } else {
            return true;
        }
    }

    /**
     * Reindex all objects for one given type
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

        $type = $this->_getService($serviceType)->findById($id);

        // Index all dam or contents from given type
        $useQueue = class_exists("ZendJobQueue");

        if ($useQueue) {
            try {
                $queue = new \ZendJobQueue();
            } catch (\Exception $e) {
                $useQueue = false;
            }
        }

        if (!$useQueue) {
            do {

                $nbIndexedItems = $this->bulkIndex($option, $id, $itemCount,
                        $bulkSize);

                $itemCount += $nbIndexedItems;
            } while ($nbIndexedItems == $bulkSize);
        } else {

            // Get total items to be indexed
            $dataService = $this->_getService($serviceData);

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
                $indexName = $this->getIndexNameFromConfig('contentIndex');
                break;
            case 'dam':
                $serviceData = 'Dam';
                $indexName = $this->getIndexNameFromConfig('damIndex');
                break;
            case 'user':
                $serviceData = 'Users';
                $indexName = $this->getIndexNameFromConfig('userIndex');
                break;
            default:
                throw new \Rubedo\Exceptions\Server(
                "Option argument should be set to content or dam",
                "Exception65");
                break;
        }

        $this->_documents = [];

        $dataService = $this->_getService($serviceData);
        $wasFiltered = $dataService::disableUserFilter();
        $itemList = $dataService->getByType($typeId, (int)$start, (int)$bulkSize);

        $dataService::disableUserFilter($wasFiltered);
        foreach ($itemList["data"] as $item) {
            switch ($option) {
                case 'content':
                    $indexIntermedResult = $this->_getService('ElasticContents')->index($item, TRUE);
                    if(is_array($indexIntermedResult)) {
                        $this->_documents = array_merge($this->_documents, $indexIntermedResult);
                    }
                    break;
                case 'dam':
                    $indexIntermedResult = $this->_getService('ElasticDam')->index($item, TRUE);
                    if(is_array($indexIntermedResult)) {
                        $this->_documents = array_merge($this->_documents, $indexIntermedResult);
                    }
                    break;
                case 'user':
                    $indexIntermedResult = $this->_getService('ElasticUsers')->index($item, TRUE);
                    if(is_array($indexIntermedResult)) {
                        $this->_documents = array_merge($this->_documents, $indexIntermedResult);
                    }
                    break;
            }
        }

        if (!empty($this->_documents)) {

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
