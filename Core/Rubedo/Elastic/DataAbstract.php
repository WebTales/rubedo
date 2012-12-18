<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */
namespace Rubedo\Elastic;

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
    // TODO : get params into .ini
    protected static $_content_index_param = array('index' => array(
		'number_of_shards' => 1, 
		'number_of_replicas' => 0 ));
		
    /**
     * Object which represent the document ES index
     *
     * @var \Elastica_Index
     */
    protected static $_document_index = "document";

    /**
     * Object which represent the default document ES index param
     *
     * @var \Elastica_Index
     */
     // TODO : get params into .ini
    protected static $_document_index_param = array('index' => array(
		'number_of_shards' => 1, 
		'number_of_replicas' => 0 ));
	
    /**
     * Initialize a search service handler to index or query Elastic Search
     *
	 * @see \Rubedo\Interfaces\IDataIndex::init()
     * @param string $host http host name
     * @param string $port http port 
     */
    public function init($host = null, $port= null)
    {
        if (is_null($host)) {
            $host = self::$_options['host'];
        }

        if (is_null($port)) {
            $port = self::$_options['port'];
        }

        $this->_client = new \Elastica_Client(array('port'=>$port,'host'=>$host));
		
		$this->_content_index = $this->_client->getIndex(self::$_options['contentIndex']);
		
		// Create content index if not exists
		if (!$this->_content_index->exists()) {
			$this->_content_index->create(self::$_content_index_param,true);
		}
		$this->_document_index = $this->_client->getIndex(self::$_options['documentIndex']);
		
		// Create document index if not exists
		if (!$this->_document_index->exists()) {
			$this->_document_index->create(self::$_document_index_param,true);
		}
    }

	 /**
     * Set the options for ES connection
     *
     * @param string $host
     */
    public static function setOptions(array $options) {
        self::$_options = $options;
    }
    
    /**
     * Set the options for the content-index 
     *
     * @param string $host
     */
    public static function setContentIndexOption(array $options) {
        self::$_content_index_param = $options;
    }
    

}
