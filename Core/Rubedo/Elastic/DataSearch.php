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

use Rubedo\Interfaces\Elastic\IDataSearch;
use Rubedo\Services\Manager;

/**
 * Class implementing the Rubedo API to Elastic Search using Elastica API
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataSearch implements IDataSearch
{

    /**
     * Default value of hostname
     *
     * Used by the constructor if no specific params
     *
     * @var string
     */
    private static $_defaultHost;
	
    /**
     * Default transport value
     *
     * Used by the constructor if no specific params
     *
     * @var string
     */
    private static $_defaultTransport;


    /**
     * Default port value
     *
     * Used by the constructor if no specific params
     *
     * @var string
     */
    private static $_defaultPort;

    /**
     * Elastica Client
     *
     * @var \Elastica_Client
     */
    private $_client;
	
    /**
     * Configuration options
     *
     * @var array
     */
    private static $_options;

    /**
     * Object which represent the content ES index
     *
     * @var \Elastica_Index
     */
    private static $_content_index = "content";

    /**
     * Object which represent the default ES index param
     *
     * @var \Elastica_Index
     */
    // TODO : get params into .ini
    private static $_content_index_param = array('index' => array(
		'number_of_shards' => 1, 
		'number_of_replicas' => 0 ));
		
    /**
     * Object which represent the document ES index
     *
     * @var \Elastica_Index
     */
    private static $_document_index = "document";

    /**
     * Object which represent the default document ES index param
     *
     * @var \Elastica_Index
     */
     // TODO : get params into .ini
    private static $_document_index_param = array('index' => array(
		'number_of_shards' => 1, 
		'number_of_replicas' => 0 ));
	
    /**
     * Initialize a search service handler to index or query Elastic Search
     *
	 * @see \Rubedo\Interfaces\IDataSearch::init()
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
     * ES search
     *     
	 * @see \Rubedo\Interfaces\IDataSearch::search()
	 * @param string $terms terms to search
	 * @param string $type optional content type filter
	 * @param string $lang optional lang filter
	 * @param string $author optional author filter
	 * @param string $date optional date filter
	 * @param string $pager optional pager, default set to 10
	 * @param string $orderBy optional  orderBy, default sort on score
	 * @param string $pageSize optional page size, "all" for everything
     * @return Elastica_ResultSet
     */
    public function search ($terms, $type=null, $lang=null, $author=null, $date=null, $pager=null, $orderBy=null, $pageSize=null) {
    	
		// set default options
		if (is_null($lang)) {
        	$session = Manager::getService('Session');
        	$lang = $session->get('lang','fr');
		}
		
		if (is_null($pager)) $pager = 0;
		
		if (is_null($orderBy)) $orderBy = "_score";
		
		if (is_null($pageSize)) $pageSize = 10;
				
		try{

			// Build global filter
			
			$globalFilter = new \Elastica_Filter_And();
			$setFilter = false;
						
			// filter on lang TOTO add lang filter
			/*
			if ($lang != '') {
				$langFilter = new \Elastica_Filter_Term();
        		$langFilter->setTerm('lang', $lang);
				$globalFilter->addFilter($langFilter);
				$setFilter = true;
        	}
			 */
			
			// filter on type
			if ($type != '') {
				$typeFilter = new \Elastica_Filter_Term();
        		$typeFilter->setTerm('type', $type);
				$globalFilter->addFilter($typeFilter);
				$setFilter = true;
			}
			
			// filter on author
			if ($author != '') {
				$authorFilter = new \Elastica_Filter_Term();
        		$authorFilter->setTerm('author', $author);
				$globalFilter->addFilter($authorFilter);
				$setFilter = true;
			}
			
			// filter on date
			if ($date!= '') {
				$dateFilter = new \Elastica_Filter_Range();
				$d = $date/1000;
				$dateFrom = $dateTo = mktime(0, 0, 0, date('m',$d), date('d',$d), date('Y',$d))*1000; 
				$dateTo = mktime(0, 0, 0, date('m',$d)+1, date('d',$d), date('Y',$d))*1000;  
        		$dateFilter->addField('lastUpdateTime', array('from' => $dateFrom, "to" => $dateTo));
				$globalFilter->addFilter($dateFilter);
				$setFilter = true;
			}			
			
			// Set query on terms
			$elasticaQueryString = new \Elastica_Query_QueryString($terms."*");
			
			$elasticaQuery = new \Elastica_Query();
			
			$elasticaQuery->setQuery($elasticaQueryString);
			
			// Apply filters if needed
			if ($setFilter) $elasticaQuery->setFilter($globalFilter);
		
			// Define the type facet.
			$elasticaFacetType = new \Elastica_Facet_Terms('typeFacet');
			$elasticaFacetType->setField('type');
			$elasticaFacetType->setSize(10);
			$elasticaFacetType->setOrder('reverse_count');
			if ($setFilter) $elasticaFacetType->setFilter($globalFilter);
						
			// Add type facet to the search query object.
			$elasticaQuery->addFacet($elasticaFacetType);
			
			// Define the author facet.
			$elasticaFacetAuthor = new \Elastica_Facet_Terms('authorFacet');
			$elasticaFacetAuthor->setField('author');
			$elasticaFacetAuthor->setSize(5);
			$elasticaFacetAuthor->setOrder('reverse_count');
			if ($setFilter) $elasticaFacetAuthor->setFilter($globalFilter);
						
			// Add that facet to the search query object.
			$elasticaQuery->addFacet($elasticaFacetAuthor);

			// Define the date facet.
			$elasticaFacetDate = new \Elastica_Facet_DateHistogram('dateFacet');
			$elasticaFacetDate->setField('lastUpdateTime');
			$elasticaFacetDate->setInterval('month');
			if ($setFilter) $elasticaFacetDate->setFilter($globalFilter);
												
			// Add that facet to the search query object.
			$elasticaQuery->addFacet($elasticaFacetDate);
			
			// Add pagination 		
			if ($pageSize!="all") {
				$elasticaQuery->setSize($pageSize)->setFrom($pager*$pageSize);
			} 
						
			// add sort
			$elasticaQuery->setSort(array($orderBy =>"desc"));

			// run query
			$elasticaResultSet = $this->_content_index->search($elasticaQuery);
			
			// Return resultset
			return($elasticaResultSet);
			
		} catch (Exception $e) {
            var_dump($e->getMessage());
			exit;
        }    	

    }
	
}
