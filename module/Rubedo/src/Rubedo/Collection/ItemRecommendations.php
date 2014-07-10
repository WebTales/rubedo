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
namespace Rubedo\Collection;

/**
 * Service to handle Item to item recommendations
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class ItemRecommendations extends AbstractCollection
{
    public function __construct()
    {
        $this->_collectionName = 'ItemRecommendations';
        parent::__construct();
    }
    
    public function init()
    {  	 
    }
       
    public function build() {
    
    	$mapCode =	"
	    	function() {
				var content = this;
				if (content.live) {
					if (content.live.taxonomy){
						for (var vocabulary in content.live.taxonomy) {
							if ((content.live.taxonomy.hasOwnProperty(vocabulary))
    								&& (typeof content.live.taxonomy[vocabulary] != 'string')
    									&& (content.live.taxonomy[vocabulary])) {
								content.live.taxonomy[vocabulary].forEach (function(term) {
    								if (term>'') {
										value = {};
										value[content._id.valueOf()]=1;
										emit(term, value);
    								}
								});
							}
						}
					}
				}
			};";
    	 
    	$reduceCode = "
			function(key, values) {
				var result = {};
				values.forEach( function(element) {
					key = Object.keys(element)[0];
					result[key] = 1;
				});
				return result;
			}";
    
    	$params = array(
    			"mapreduce" => "Contents", // collection
    			"map" => new \MongoCode($mapCode), // map
    			"reduce" => new \MongoCode($reduceCode), // reduce
    			"out" => array("replace" => "tmpRecommendations") // out
    	);
    
    	$response = $this->_dataService->command($params);
    
    	$mapCode =	"
			function() {
				var term = this;
				var ids = Object.keys(term.value);
				if (ids.length>1) {
					for (var i=0; i < ids.length; i++) {
						for (var j=i+1; j < ids.length; j++) {
							value = {};
							value[ids[j]]=1;
							emit(ids[i], value);
							value = {};
							value[ids[i]]=1;
    						emit(ids[j], value);
    					}
    				}
    			}
    		}";
    
    	$reduceCode = "
			function(key, values) {
				var result = {};
				values.forEach( function(element) {
					key = Object.keys(element)[0];
					if (result[key]) {
						result[key]=result[key]+1;
					} else {
						result[key]=1;
					}
				});
				return result;
			}";
    
    	$params = array(
    			"mapreduce" => "tmpRecommendations", // collection
    			"map" => new \MongoCode($mapCode), // map
    			"reduce" => new \MongoCode($reduceCode), // reduce
    			"out" => array("replace" => "ItemRecommendations") // out
    	);
    
    	$response = $this->_dataService->command($params);
    
    	$code = "db.tmpRecommendations.drop();";
    	$this->_dataService->execute($code);
    	
    	return $response;
    }
}