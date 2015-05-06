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

/**
 * Class implementing the Rubedo API to Elastic Search indexing services using
 * PHP elasticsearch API
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataIndex extends DataAbstract
{

    /**
     * Documents queue for indexing
     */
    protected $_documents;
       
    /**
     * Reindex all content or dam
     *
     * @param string $option
     *            : dam, content, user or all
     *
     * @return array
     */
    public function indexAll($option = 'all')
    {
        // for big data set
        set_time_limit(240);

        // Initialize result array
        $result = [];

        // Destroy and re-create content, dam and user indexes        
        if ($option == 'all' or $option == 'content') {
        	$contentsIndexName = $this->getIndexNameFromConfig('contentIndex');
          	if ($this->_client->indices()->exists(['index' => $contentsIndexName])) {
            	$this->_client->indices()->delete(['index' => $contentsIndexName]);
          	}
        }

        if ($option == 'all' or $option == 'dam') {
        	$damIndexName = $this->getIndexNameFromConfig('damIndex');
          	if ($this->_client->indices()->exists(['index' => $damIndexName])) {
            	$this->_client->indices()->delete(['index' => $damIndexName]);
          	}
        }

        if ($option == 'all' or $option == 'users') {
        	$usersIndexName = $this->getIndexNameFromConfig('userIndex');
          	if ($this->_client->indices()->exists(['index' => $usersIndexName])) {
            	$this->_client->indices()->delete(['index' => $usersIndexName]);
          	}
        }

        if ($option == 'all' or $option == 'content') {

            // Retreive all content types
            $contentTypeList = $this->_getService('ContentTypes')->getList();

            foreach ($contentTypeList["data"] as $contentType) {

                // System contents are not indexed
                if (!isset($contentType['system']) or
                    $contentType['system'] == FALSE
                ) {
                    // Create content type mapping
                    $this->_getService('ElasticContentTypes')->setMapping($contentType["id"], $contentType);

                    // Reindex all contents from given type
                    $result = array_merge($result, $this->_getService('ElasticContentTypes')->index($contentType["id"]));
                }
            }
        }

        if ($option == 'all' or $option == 'dam') {

            // Retreive all dam types
            $damTypeList = $this->_getService('DamTypes')->getList();

            foreach ($damTypeList["data"] as $damType) {

                // Create dam type mapping
            	$this->_getService('ElasticDamTypes')->setMapping($damType["id"], $damType);

                // Reindex all assets from given type
                $result = array_merge($result, $this->_getService('ElasticDamTypes')->index($damType["id"]));
            }
        }

        if ($option == 'all' or $option == 'user') {

            // Retreive all user types
            $userTypeList = $this->_getService('UserTypes')->getList();

            foreach ($userTypeList["data"] as $userType) {

                // Create user type mapping with overwrite set to true
            	$this->_getService('ElasticUserTypes')->setMapping($userType["id"], $userType);

                // Reindex all assets from given type
                $result = array_merge($result, $this->_getService('ElasticUserTypes')->index($userType["id"]));
            }
        }

        return ($result);
    }
    
    public function indexContent($data, $bulk = false) {
    	
    	$this->_getService('ElasticContents')->index($data, $bulk = false);
    	
    }
    
    public function indexDam($data, $bulk = false) {
    	 
    	$this->_getService('ElasticDam')->index($data, $bulk = false);
    	 
    }
    
    public function indexUser($data, $bulk = false) {
    	 
    	$this->_getService('ElasticUsers')->index($data, $bulk = false);
    	 
    }

}
