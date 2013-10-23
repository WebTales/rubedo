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

use Rubedo\Services\Manager;
use Zend\Json\Json;
/**
 * Class implementing the Rubedo API to Elastic Search indexing services using Elastica API
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
     * Elastica Client
     *
     * @var \Elastica_Client
     */
    protected $_client;

    /**
     * Configuration options
     *
     * @var array
     */
    protected static $_options;

    /**
     * Object which represent the content ES index
     *
     * @var \Elastica_Index
     */
    protected static $_content_index;

    /**
     * Object which represent the default ES index param
     * 
     * @var \Elastica_Index
     */
    protected static $_content_index_param;

    /**
     * Object which represent the dam ES index
     *
     * @var \Elastica_Index
     */
    protected static $_dam_index;

    /**
     * Object which represent the default dam ES index param
     * @TODO : get param from config
     * 
     * @var \Elastica_Index
     */
    protected static $_dam_index_param;

    /**
     * Object which represent the user ES index
     *
     * @var \Elastica_Index
     */
    protected static $_user_index;
    
    /**
     * Object which represent the default user ES index param
     * @TODO : get param from config
     *
     * @var \Elastica_Index
     */
    protected static $_user_index_param;
    
    
    public function __construct(){
        if(!isset(self::$_options)){
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
    public function init ($host = null, $port = null)
    {
        if (is_null($host)) {
            $host = self::$_options['host'];
        }
        
        if (is_null($port)) {
            $port = self::$_options['port'];
        }
        
        $this->_client = new \Elastica\Client(array(
            'port' => $port,
            'host' => $host
        ));
        
        $this->_client->setLogger(Manager::getService('SearchLogger')->getLogger());
        
        // Get content index
        self::$_content_index = $this->_client->getIndex(self::$_options['contentIndex']);
        
        // Create content index if not exists
        if (! self::$_content_index->exists()) {
            self::$_content_index->create(self::$_content_index_param, true);
        }
        
        // Get dam index
        self::$_dam_index = $this->_client->getIndex(self::$_options['damIndex']);
        
        // Create dam index if not exists
        if (! self::$_dam_index->exists()) {
            self::$_dam_index->create(self::$_dam_index_param, true);
        }
        
        // Get user index
        self::$_user_index = $this->_client->getIndex(self::$_options['userIndex']);
        
        // Create user index if not exists

        if (! self::$_user_index->exists()) {
            self::$_user_index->create(self::$_user_index_param, true);
        }
        
    }

    /**
     * Set the options for ES connection
     *
     * @param array $options            
     */
    public static function setOptions (array $options)
    {
        self::$_options = $options;
    }

    /**
     *
     * @return the $_options
     */
    public static function getOptions ()
    {
        if(!isset(self::$_options)){
            self::lazyLoadConfig();
        }
        return self::$_options;
    }

    /**
     * Set the options for the content index
     *
     * @param string $host            
     */
    public static function setContentIndexOption (array $options)
    {
        self::$_content_index_param = $options;
    }

    /**
     * Set the options for the dam index
     *
     * @param string $host
     */
    public static function setDamIndexOption (array $options)
    {
        self::$_dam_index_param = $options;
    }

    /**
     * Set the options for the user index
     *
     * @param string $host
     */
    public static function setUserIndexOption (array $options)
    {
        self::$_user_index_param = $options;
    }
    
    /**
     * Return the ElasticSearch Server Version
     * 
     * @return string
     */
    public function getVersion ()
    {
        $data = $this->_client->request('/', 'GET')->getData();
        if (isset($data['version']) && isset($data['version']['number'])) {
            return $data['version']['number'];
        }
        return null;
    }

    /**
     * Read configuration from global application config and load it for the current class
     */
    public static function lazyLoadConfig ()
    {
        $options = Manager::getService('config');
        if (isset($options)) {
            self::setOptions($options['elastic']);
        }
        $indexOptionsJson = file_get_contents($options['elastic']['configFilePath']);
        $indexOptions = Json::decode($indexOptionsJson, Json::TYPE_ARRAY);
        self::setContentIndexOption($indexOptions);
        self::setDamIndexOption($indexOptions);
        self::setUserIndexOption($indexOptions);
    } 
}
