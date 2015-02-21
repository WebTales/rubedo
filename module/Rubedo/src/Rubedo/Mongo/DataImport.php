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
namespace Rubedo\Mongo;

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\Json\Json;

/**
 * Service to handle Import
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataImport extends DataAccess
{

    /**
     * Import file name
     *
     * @var string
     */
    protected $_fileName;
    /**
     * Current import mode : insert or import
     *
     * @var string
     */
    protected $_importMode;
    /**
     * Unique key to define the current import process
     *
     * @var string
     */
    protected $_importKeyValue;
    /**
     * Unique key index in the import file to match with the unique key field
     *
     * @var string
     */
    protected $_uniqueKeyIndex;
    /**
     * Unique key field in the updated content type
     *
     * @var string
     */
    protected $_uniqueKeyField;
    /**
     * User encoding : UTF8, ...
     *
     * @var string
     */
    protected $_userEncoding;
    /**
     * List of fields to import
     *
     * @var array
     */
    protected $_importAsField;
    /**
     * List of fields translation
     *
     * @var array
     */
    protected $_importAsFieldTranslation;
    /**
     * List of taxonomy terms to import
     *
     * @var array
     */
    protected $_importAsTaxo;
    /**
     * List of taxonomy terms translations
     *
     * @var array
     */
    protected $_importAsTaxoTranslation;
    /**
     * Current BO working language
     *
     * @var string
     */
    protected $_workingLanguage;
    /**
     * File separator
     *
     * @var string
     */
    protected $_separator;
    /**
     * List of vocabularies to import
     *
     * @var array
     */
    protected $_vocabularies;
    /**
     * Default navigation taxonomy to create contents
     *
     * @var string
     */
    protected $_contentsNavTaxo;
    /**
     * Default target workspace to create contents
     *
     * @var string
     */
    protected $_contentsTarget;
    /**
     * Content Type Id
     *
     * @var string
     */
    protected $_typeId;
    /**
     * List of product Options
     *
     * @var array
     */
    protected $_productOptions;
    /**
     * Is it a product import
     *
     * @var boolean
     */
    protected $_isProduct;

    public function __construct()
    {
        parent::__construct();
        parent::init('ImportData');

    }

    /**
     * Run the complete import process
     */
    public function run($fileName, $options)
    {

        // Get general params
        $this->_importMode = $options['importMode'];
        $this->_importKeyValue = $options['importKey'];
        $this->_userEncoding = $options['userEncoding'];
        $this->_workingLanguage = $options['workingLanguage'];
        $this->_separator = isset($options['separator']) ? $options['separator'] : ';';
        $this->_termSeparator = isset($options['termSeparator']) ? $options['termSeparator'] : ',';
        $this->_pathSeparator = isset($options['pathSeparator']) ? $options['pathSeparator'] : '##';
        $this->_typeId = $options['typeId'];
        $this->_fileName = $fileName;
        $this->_isProduct = isset($options['isProduct']) ? $options['isProduct'] : false;
        $this->_importAsField = $options['importAsField'];

        // Get params for insert mode
        if ($this->_importMode == 'insert') {
            $this->_importAsFieldTranslation = $options['importAsFieldTranslation'];
            $this->_importAsTaxo = $options['importAsTaxo'];
            $this->_importAsTaxoTranslation = $options['importAsTaxoTranslation'];
            $this->_vocabularies = $options['vocabularies'];
            $this->_navigationTaxonomy = $options['contentsNavTaxo'];
            $this->_target = $options['contentsTarget'];
        } else { // get params for update mode
            $this->_uniqueKeyIndex = $options['uniqueKeyIndex'];
            $this->_uniqueKeyField = $options['uniqueKeyField'];
            $this->_importAsTaxo = array();
            $this->_target = '';
        }

        if ($this->_isProduct) { // Product options
            $this->_productOptions = array(
                // in insert mode, text index is a field
                'textFieldIndex' => isset($options['text']) ? $options['text'] : '',
                // in insert mode, summary is a field
                'summaryFieldIndex' => isset($options['summary']) ? $options['summary'] : '',
                'baseSkuFieldIndex' => $options['baseSkuFieldIndex'],
                'basePriceFieldIndex' => $options['basePriceFieldIndex'],
                'preparationDelayFieldIndex' => $options['preparationDelayFieldIndex'],
                'skuFieldIndex' => $options['skuFieldIndex'],
                'priceFieldIndex' => $options['priceFieldIndex'],
                'stockFieldIndex' => $options['stockFieldIndex']
            );
        } else {
            $this->_productOptions = null;
        }

        // Write file to import into Import Data collection
        
        $importData = Manager::getService('ImportData');
        $importData->writeImportFile($this->_fileName, $this->_separator, $this->_userEncoding, $this->_importKeyValue);  
		
        switch ($this->_importMode) {
        	case "insert":
        		$response = $this->insertData();
        		break;
        	case "update":
        		$response = $this->updateData();
        		break;
        	default:
        		throw new \Rubedo\Exceptions\Server("Unkown import mode : ".$this->_importMode);
        }
        
        return $response;

    }

    /**
     * Extract contents to update from Import
     * to ImportContents collection
     */
    protected function extractContentsToUpdate()
    {

        // Create fields
        $fields = array();

        foreach ($this->_importAsField as $key => $value) {

            // Fields that are not product variations
            if (!isset($value['useAsVariation']) || ($value['useAsVariation'] == false)) {

                switch ($value['protoId']) {
                    case 'text':
                        $textFieldIndex = $value['csvIndex'];
                        $fields['text'] = 'this.col' . $value['csvIndex'];
                        break;
                    case 'summary':
                        $fields['summary'] = 'this.col' . $value['csvIndex'];
                        break;
                    default:
                        if ($value['cType'] != 'localiserField') {
                            $fields[$value['newName']] = 'this.col' . $value['csvIndex'];
                        } else {
                            $fields['position'] = array(
                                'address' => '',
                                'altitude' => '',
                                'lat' => 'this.col' . $value['csvIndex'] . '[0]',
                                'lon' => 'this.col' . $value['csvIndex'] . '[1]',
                                'location' => array(
                                    'type' => 'Point',
                                    'coordinates' => array('this.col' . $value['csvIndex'] . '[1]', 'this.col' . $value['csvIndex'] . '[0]')
                                )
                            );
                        }
                        break;
                }
            }
        }

        // add taxonomy
        $taxonomy = array();

        foreach ($this->_importAsTaxo as $key => $value) {
            $taxonomy[$this->_vocabularies[$key + 1]] = 'this.col' . $value['csvIndex'];
        }

        $mapCode = "function() {
    		var value = {";

        foreach ($this->_importAsField as $key => $value) {
            $mapCode .= "'" . $value['name'] . "' : this.col" . $value['csvIndex'] . ",";
        }

        if ($this->_isProduct) {
            //$mapCode.=",isProduct:true,";
            if ($this->_productOptions['textFieldIndex'] != "") {
                $mapCode .= "text: this.col" . $this->_productOptions['textFieldIndex'] . ",";
            }
            if ($this->_productOptions['summaryFieldIndex'] != "") {
                $mapCode .= "summary: this.col" . $this->_productOptions['summaryFieldIndex'] . ",";
            }
            if ($this->_productOptions['baseSkuFieldIndex'] != "") {
                $mapCode .= "baseSku: this.col" . $this->_productOptions['baseSkuFieldIndex'] . ",";
            }
            if ($this->_productOptions['basePriceFieldIndex'] != "") {
                $mapCode .= "basePrice: this.col" . $this->_productOptions['basePriceFieldIndex'] . ",";
            }
            if ($this->_productOptions['preparationDelayFieldIndex'] != "") {
                $mapCode .= "preparationDelay: this.col" . $this->_productOptions['preparationDelayFieldIndex'] . ",";
            }
            if ($this->_productOptions['priceFieldIndex'] != "") {
                $mapCode .= "price: this.col" . $this->_productOptions['priceFieldIndex'] . ",";
            }
            if ($this->_productOptions['stockFieldIndex'] != "") {
                $mapCode .= "stock: this.col" . $this->_productOptions['stockFieldIndex'] . ",";
            }
            if ($this->_productOptions['skuFieldIndex'] != "") {
                $mapCode .= "sku: this.col" . $this->_productOptions['skuFieldIndex'];
            }

        }

        $mapCode .= "};";
        //$mapKey = $this->_isProduct ? "this.col".$this->_productOptions['baseSkuFieldIndex'] : "this.col".$this->uniqueKeyIndex;
        $mapKey = "this.col" . $this->uniqueKeyIndex;
        $mapCode .= "emit(" . $mapKey . ", value);};";

        $map = new \MongoCode($mapCode);

        if (!$this->_isProduct) {
            $reduceCode = "function(key, values) { return {key: values[0]} }";
        } else {
            $reduceCode = "function(key, values) {
    			var value = values[0];
    			var productProperties = {
    				sku : value.baseSku,
					basePrice: value.basePrice,
					preparationDelay: value.preparationDelay,
					canOrderNotInStock: false,
					outOfStockLimit: 1,
					notifyForQuantityBelow : 1,
					resupplyDelay : 1
    			};
    			var variations = new Array();
    			values.forEach(function(v) {
					oid = ObjectId();
					var variation = {
    					price: v.price,
    					stock: v.stock,
    					sku: v.sku,
    					id: oid.valueOf()
					};";

            // add variation fields

            foreach ($this->_importAsField as $key => $value) {
                if (isset($value['useAsVariation']) && $value['useAsVariation']) {
                    $reduceCode .= "variation['" . $value['newName'] . "']=v['" . $value['newName'] . "'];";
                }
            }

            $reduceCode .= "
    				variations.push(variation);
    			});
    
    			productProperties['variations'] = variations;
    			value['productProperties'] = productProperties;
    
    			delete value['baseSku'];
    			delete value['basePrice'];
    			delete value['sku'];
    			delete value['price'];
    			delete value['stock'];";

            foreach ($this->_importAsField as $key => $value) {
                if (isset($value['useAsVariation']) && $value['useAsVariation']) {
                    $reduceCode .= "delete value['" . $value['newName'] . "'];";
                }
            }

            $reduceCode .= "	return value;
    
    		};";
        }

        $reduce = new \MongoCode($reduceCode);

        // global JavaScript variables passed to map, reduce and finalize functions
		$scope = array(
			"currentTime" => $this->currentTime,
			"currentUser" => $this->currentUser,
			"typeId" => $this->_typeId,
			"target" => $this->_target
		);

        $params = array(
            "mapreduce" => "ImportData", // collection
            "query" => array("importKey" => $this->_importKeyValue), // query
            "map" => $map, // map
            "reduce" => $reduce, // reduce
            "out" => array("replace" => "ImportContents") // out
        );

        $response = $this->_dataService->command($params);

        if ($response['ok'] != 1) {
            throw new \Rubedo\Exceptions\Server("Extracting Contents error");
        }

        return true;

    }

   protected function processTaxonomy($record) {
   	
   		$taxonomy=[];
   		
		foreach ($this->_importAsTaxo as $key => $value) {
   		 
   			$column = 'col'.$value["csvIndex"];
   			$vocabularyId = $this->_vocabularies[$key + 1];
   	
   			$pathList = explode ($this->_termSeparator, $record[$column]);
   			$termList = [];
   			foreach($pathList as $pIndex => $path) {
			   	$termsId = [];
			   	$terms = explode($this->_pathSeparator,$path);
			   	for ($i=0; $i<count($terms); $i++) {
			   		if ($terms[$i]>'') {
			   			if ($i>0) {
			   				$parent = $termsId[$i-1];
			   			} else {
			   				$parent = 'root';
			   			}
			   			$filters = Filter::Factory();
			   			$filter = Filter::factory('Value')->setName('vocabularyId')->setValue($vocabularyId);
			   			$filters->addFilter($filter);
			   			$filter = Filter::factory('Value')->setName('text')->setValue($terms[$i]);
			   			$filters->addFilter($filter);
			   			$filter = Filter::factory('Value')->setName('parentId')->setValue($parent);
			   			$filters->addFilter($filter);
			   			$term = Manager::getService('TaxonomyTerms')->findOne($filters);
			   				
			   			if ($term) {
			   				$newId = $term['id'];
			   				$create = false;
			   			} else {
			   		   
			   				$newId = new \MongoId();
			   				$create = true;
			   			}
			   			 
			   			$termPath = implode($this->_pathSeparator,array_slice($terms, 0, $i+1));
			   				
			   			$termsId[$i]= (string) $newId;
			   				
			   			if ($create) {
			   				$taxonomyTerm = [
				   				'_id' => $newId,
				   				'text' => $terms[$i],
				   				'path' => $termPath,
				   				'vocabularyId' => $vocabularyId,
				   				'parentId' => $parent,
				   				'expandable' => true,
				   				'nativeLanguage' => $this->_workingLanguage,
				   				'i18n' => [
					   				$this->_workingLanguage => [
						   				'text' => $terms[$i],
						   				'locale' => $this->_workingLanguage
					   				]
			   					]
			   				];
			   		   		
			   				foreach ($this->_importAsTaxoTranslation as $transKey => $transValue) {
			   					
			   					if ($value['csvIndex'] == $transValue['translatedElement']) {
			   						
			   						$translatedTermValue = explode($this->_termSeparator,$record['col'.$transValue['csvIndex']])[$pIndex];
			   						if (trim($translatedTermValue) > '') {
				   						$taxonomyTerm['i18n'][$transValue['translateToLanguage']] = [
				   							'text' => $translatedTermValue,
											'locale' => $transValue['translateToLanguage']
										];
			   						}
			   					}
			   				}
			   				
			   				Manager::getService('TaxonomyTerms')->create($taxonomyTerm);
			   		   
			   			}
			   		}
			   	}  
			   	
			   	$termId = end($termsId);
			   	if ($termId) $termList[] = $termId;
			}
			
			if (count($termList)>0) $taxonomy[$this->_vocabularies[$key + 1]] = $termList;
		}

	   	return $taxonomy;
	   		
   } 
   
   protected function getProductVariations () {

   		$variationFields = [];
   	
	   	foreach ($this->_importAsField as $key => $value) {
	   		if (isset($value['useAsVariation']) && $value['useAsVariation']) {
	   			$variationFields[] = [
	   				'name' => $value['newName'],
	   				'column' => 'col'.$value['csvIndex']
	   			];
	   		}
	   	}   	

	   	$scope = array(
	   		'baseSkuFieldIndex' => 'col'.$this->_productOptions['baseSkuFieldIndex'],
	   		'priceFieldIndex' => 'col'.$this->_productOptions['priceFieldIndex'],
	   		'stockFieldIndex' => 'col'.$this->_productOptions['stockFieldIndex'],
	   		'skuFieldIndex' => 'col'.$this->_productOptions['skuFieldIndex'],
	   		'variationFields' => $variationFields
	   	);
	   	
	   	$map = new \MongoCode ( "
	   		function() { 
	   			var row = this;
	   			var value = { 
	   				price: row[priceFieldIndex], 
	   				stock: row[stockFieldIndex], 
	   				sku: row[skuFieldIndex] 
	   			};
	   			variationFields.forEach(function(field) {
	   				value[field.name] = row[field.column];
	   			});   			
	   			emit(this[baseSkuFieldIndex],value);
	   		}");		
	   	
	   	$reduce = new \MongoCode ( "
	   		function(key, values) {
	    		var value = values[0];
	   			var productProperties = {
	   				preparationDelay: 1,
					canOrderNotInStock: false,
					outOfStockLimit: 1,
					notifyForQuantityBelow : 1,
					resupplyDelay : 1
	    		};
	    		var variations = [];
	    		values.forEach(function(v) {
					oid = ObjectId();
					var variation = {
	    				price: v.price,
	    				stock: v.stock,
	    				sku: v.sku,
	    				id: oid.valueOf()
					};
	   				variationFields.forEach(function(field) {
	   					variation[field.name] = v[field.name];
	   				});
					variations.push(variation);
	    		});
	   	
	    		productProperties['variations'] = variations;
	    		value['productProperties'] = productProperties;
	   	
	    		delete value['sku'];
	    		delete value['price'];
	    		delete value['stock'];
				variationFields.forEach(function(v) {
				   	delete value[v.name];
				});
	   			return value;
	   	
	    	}");
	   	
	   	$params = array(
	   			"mapreduce" => "ImportData", // collection
	   			"query" => array("importKey" => $this->_importKeyValue), // query
	   			"scope" => $scope,
	   			"map" => $map, // map
	   			"reduce" => $reduce, // reduce
	   			"out" => array("replace" => "ImportProducts") // out
	   	);
	   	
	   	$response = $this->command($params);
	   	
	   	if ($response['ok'] != 1) {
	   		throw new \Rubedo\Exceptions\Server("Extracting products variations error");
	   	}
	   	
	   	return true;
   }

   protected function updateData() {
   	
   	
	   	// Get data records on import Key
	   	$filter = Filter::factory('Value')->setName('importKey')->setValue($this->_importKeyValue);
	   	$dataCursor = Manager::getService('ImportData')->customFind($filter);
	   	 
	   	// Fill ImportProduct collection with product variations if needed
	   	 
	   	if ($this->_isProduct) $this->getProductVariations();
	   	 
	   	$previousRecordBaseSku = null;
	   	$counter = 0;
	   	 
	   	// Loop on import records
	   	foreach($dataCursor as $record) {
	   	
	   		if ($this->_isProduct && ($previousRecordBaseSku == $record['col'.$this->_productOptions['baseSkuFieldIndex']])) {
	   	
	   			// skip record
	   			 
	   		} else {

	   			// Fetch content to update
	   			
	   			$findFilter = Filter::Factory();
	   			
	   			$filter = Filter::factory('Value')->setName('typeId')->setValue($this->_typeId);
	   			$findFilter->addFilter($filter);
	   			 
	   			if ($this->_uniqueKeyField != 'sku') {
	   				$filter = Filter::factory('Value')->setName($this->_uniqueKeyField)->setValue($record['col'.$this->_uniqueKeyIndex]);
	   			} else {
	   				$filter = Filter::factory('Value')->setName('productProperties.sku')->setValue($record['col'.$this->_productOptions['baseSkuFieldIndex']]);
	   			}
	   			$findFilter->addFilter($filter);	   			
	   			
	   			$contentToUpdate = Manager::getService('Contents')->findOne($findFilter,true,false);

	   			// Process fields

	   			$fields = [];
		        $i18n = [];

		        foreach ($this->_importAsField as $key => $value) {
		
		        	$column = 'col'.$value["csvIndex"];
		        	
		            // Update fields that are not product variations
		            if (!isset($value['useAsVariation']) || ($value['useAsVariation'] == false)) {
		
		                switch ($value['protoId']) {
		                    case 'text':
		                        $textFieldIndex = 'col'.$value['csvIndex'];
		                        $fieldName = 'text';
		                        $fields[$fieldName] = $record[$column];
		                        break;
		                    case 'summary':
		                    	$fieldName = 'summary';
		                        $fields[$fieldName] = $record[$column];
		                        break;
		                    default:
		                        if ($value['cType'] != 'localiserField') {
		                        	$fieldName = $value['newName'];
		                            $fields[$fieldName] = $record[$column];
		                        } else {
		                        	$fieldName = "position";
		                        	$latlon = explode(',',$record[$column]);
		                        	if (count($latlon)==2) {
			                            $fields[$fieldName] = array(
			                                'address' => '',
			                                'altitude' => '',
			                                'lat' => (float) $latlon[0],
			                                'lon' => (float) $latlon[1],
			                                'location' => array(
			                                    'type' => 'Point',
			                                    'coordinates' => array((float) $latlon[1], (float) $latlon[0])
			                                )
			                            );
		                        	}
		                        }
		                        break;
		                }
		                		                
		            } else {
		            	
		            	// Update variation fields : TODO
		            	
		            }
		            
		            
		        }
		        // Add product fields
		        if ($this->_isProduct) {
		        	if (is_integer($this->_productOptions['textFieldIndex'])) { // title
		        		$textValue = $record['col'.$this->_productOptions['textFieldIndex']];
		        		$contentToUpdate['text'] = $textValue;
		        		$contentToUpdate['i18n'][$this->_workingLanguage]['fields']['text'] = $textValue;
		        	}
		        	if (is_integer($this->_productOptions['summaryFieldIndex'])) { // summary
		        		$contentToUpdate['i18n'][$this->_workingLanguage]['fields']['summary'] = $record['col'.$this->_productOptions['summaryFieldIndex']];
		        	}
		        	if (is_integer($this->_productOptions['baseSkuFieldIndex'])) { // base sku
		        		$contentToUpdate['productProperties']['sku'] = $record['col'.$this->_productOptions['baseSkuFieldIndex']];
		        	}
		        	if (is_integer($this->_productOptions['basePriceFieldIndex'])) { // base price
		        		$contentToUpdate['productProperties']['basePrice'] = $record['col'.$this->_productOptions['basePriceFieldIndex']];
		        	}
		        	if (is_integer($this->_productOptions['preparationDelayFieldIndex'])) { // preparation delay
		        		$contentToUpdate['productProperties']['preparationDelay'] = $record['col'.$this->_productOptions['preparationDelayFieldIndex']];
		        	}
		        	if (is_integer($this->_productOptions['skuFieldIndex'])) { // variation sku
		        		//$contentToUpdate['productProperties']['preparationDelay'] = $record['col'.$this->_productOptions['preparationDelayFieldIndex']];
		        		//$updateVariations .= "'productProperties.variations.\$.sku' : v['sku'],";
		        	}
		        	if (is_integer($this->_productOptions['priceFieldIndex'])) { // variation price
		        		//$contentToUpdate['productProperties']['preparationDelay'] = $record['col'.$this->_productOptions['preparationDelayFieldIndex']];
		        		//$updateVariations .= "'productProperties.variations.\$.price' : v['price'],";
		        	}
		        	if (is_integer($this->_productOptions['stockFieldIndex'])) { // variation stock
		        		//$contentToUpdate['productProperties']['preparationDelay'] = $record['col'.$this->_productOptions['preparationDelayFieldIndex']];
		        		//$updateVariations .= "'productProperties.variations.\$.stock' : v['stock'],";
		        	}
		        
		        }		        
		        
		        if (is_array($contentToUpdate['fields'])) {
		        	
		        	$contentToUpdate['fields'] = array_replace_recursive($contentToUpdate['fields'],$fields);

		        	// Finally update content
		        	$result = Manager::getService('Contents')->update($contentToUpdate, array(), false);

		        	if ($result['success']) {
		        		$counter++;
		        	}
		        }

	   			if ($this->_isProduct) {
	   				 
	   				$previousRecordBaseSku = $record['col'.$this->_productOptions['baseSkuFieldIndex']];
	   				 
	   			}
	   		}
	
	   	}
	   	
	   	return $counter;
   	
   }
   
   protected function insertData() {
		
		// Get data records on import Key
	   	$filter = Filter::factory('Value')->setName('importKey')->setValue($this->_importKeyValue);
	   	$dataCursor = Manager::getService('ImportData')->customFind($filter);
	   	
	   	// Fill ImportProduct collection with product variations if needed
	   	
	   	if ($this->_isProduct) $this->getProductVariations();
	   	
	   	$previousRecordBaseSku = null;
	   	$counter = 0;
	   	
	   	// Loop on import records
	   	foreach($dataCursor as $record) {
	   		
	   		if ($this->_isProduct && ($previousRecordBaseSku == $record['col'.$this->_productOptions['baseSkuFieldIndex']])) {
	   		
	   			// skip record
	   			
	   		} else {
	   		
		   		// Process Taxonomy
		   		
		   		$taxonomy = $this->processTaxonomy($record);
		   		$taxonomy['navigation'] = $this->_navigationTaxonomy;
	
		   	    // Process Fields
		   	       
		        $fields = [];
		        $i18n = [];
		
		        foreach ($this->_importAsField as $key => $value) {
		
		        	$column = 'col'.$value["csvIndex"];
		        	
		            // Fields that are not product variations
		            if (!isset($value['useAsVariation']) || ($value['useAsVariation'] == false)) {
		
		                switch ($value['protoId']) {
		                    case 'text':
		                        $textFieldIndex = 'col'.$value['csvIndex'];
		                        $fieldName = 'text';
		                        $fields[$fieldName] = $record[$column];
		                        break;
		                    case 'summary':
		                    	$fieldName = 'summary';
		                        $fields[$fieldName] = $record[$column];
		                        break;
		                    default:
		                        if ($value['cType'] != 'localiserField') {
		                        	$fieldName = $value['newName'];
		                            $fields[$fieldName] = $record[$column];
		                        } else {
		                        	$fieldName = "position";
		                        	$latlon = explode(',',$record[$column]);
		                        	if (count($latlon)==2) {
			                            $fields[$fieldName] = array(
			                                'address' => '',
			                                'altitude' => '',
			                                'lat' => (float) $latlon[0],
			                                'lon' => (float) $latlon[1],
			                                'location' => array(
			                                    'type' => 'Point',
			                                    'coordinates' => array((float) $latlon[1], (float) $latlon[0])
			                                )
			                            );
		                        	}
		                        }
		                        break;
		                }
		                
		                // Add field translations
		                $i18n[$this->_workingLanguage]['fields'] = $fields;
		                $i18n[$this->_workingLanguage]['locale'] = $this->_workingLanguage;
		                foreach ($this->_importAsFieldTranslation as $transKey => $transValue) {
		                	 
		                	if ($value['csvIndex'] == $transValue['translatedElement']) {
		                		$i18n[$transValue['translateToLanguage']]['fields'][$fieldName] = $record['col'.$transValue['csvIndex']];
		                		$i18n[$transValue['translateToLanguage']]['locale'] = $transValue['translateToLanguage'];
		                	}
		                } 
		                
		            }
		        }
	
		        $content = [
		        	'text' => $record[$textFieldIndex],
	            	'summary' => isset($summaryFieldIndex) ? $record[$summaryFieldIndex] : '',
	            	'typeId' => $this->_typeId,
	            	'fields' => $fields,
	            	'status' => 'published',
	            	'online' => true,
	            	'writeWorkspace' => 'global',
	            	'target' => $this->_target,
	            	'startPublicationDate' => '',
	            	'endPublicationDate' => '',
	            	'nativeLanguage' => $this->_workingLanguage,
	            	'readOnly' => false,
	            	'i18n' => $i18n,
	            	'taxonomy' => $taxonomy,
	            	'isProduct' => $this->_isProduct
		        ];
		        
		        // Add product properties and variations
		        
		        if ($this->_isProduct) {
		        	$product = [
		        		'isProduct' => true,
		        		'baseSku' => $record['col'.$this->_productOptions['baseSkuFieldIndex']],
	    				'basePrice' => $record['col'.$this->_productOptions['basePriceFieldIndex']],
	    				'sku' => $record['col'.$this->_productOptions['skuFieldIndex']]
		        	];
		        	
		        	// add variation fields
		        	$importProducts = Manager::getService('ImportProducts');
		        	$filter = Filter::factory('Value')->setName('_id')->setValue($record['col'.$this->_productOptions['baseSkuFieldIndex']]);
		        	$variation = Manager::getService('ImportProducts')->findOne($filter);
		        	$content['preparationDelay'] = 1;
		        	$content['canOrderNotInStock'] = false;
		        	$content['outOfStockLimit'] = 1;
		        	$content['notifyForQuantityBelow'] = 1;
		        	$content['resupplyDelay'] = 11;
		        	$content['productProperties'] = $variation['value']['productProperties'];
		        	$content['productProperties']['sku'] = $record['col'.$this->_productOptions['baseSkuFieldIndex']];
		        	$content['productProperties']['basePrice'] = $record['col'.$this->_productOptions['basePriceFieldIndex']];
		        	$content['productProperties']['preparationDelay'] = 1;
		        	
		        	$previousRecordBaseSku = $record['col'.$this->_productOptions['baseSkuFieldIndex']];
		        	
		        }
		        
		        // Finally create content
		        
        		Manager::getService('Contents')->create($content);
	        	$counter++;
	   		}

	   	}
	   	
	   	return $counter;
   }

    /**
     * Update contents and flush import collection
     */
    protected function updateContents()
    {

        // get variation fields from content type

        $variationFields = Manager::getService("ContentTypes")->getVariationFieldForCType($this->_typeId);

        // 2 different queries for contents and products

        if ($this->uniqueKeyField != 'sku') {
            $queryProduct = "typeId: '" . $this->_typeId . "','" . $this->uniqueKeyField . "': foo._id";
        } else {
            $queryProduct = "typeId: '" . $this->_typeId . "','productProperties.sku': foo._id";
        }
        $updateProduct = "\$set: {";

        $fieldsToUpdate = array();

        // Check if there is any variation price, stock or sku to update

        $variationToUpdate = $this->_productOptions['skuFieldIndex'] || $this->_productOptions['priceFieldIndex'] != "" || $this->_productOptions['stockFieldIndex'];
        if ($variationToUpdate) {
            $queryVariations = $queryProduct . ",'productProperties.variations': {\$elemMatch: {";
            $updateVariations = "\$set: {";
        }

        // Add system fields

        if ($this->_isProduct) {

            if ($this->_productOptions['textFieldIndex'] != "") { // title
            	$updateProduct.= "'text' : foo['value']['text'],"; // live
                $updateProduct .= "'live.i18n." . $this->_workingLanguage . ".fields.text' : foo['value']['text'],"; // live
                $updateProduct .= "'workspace.i18n." . $this->_workingLanguage . ".fields.text'"; // workspace
            }
            if ($this->_productOptions['summaryFieldIndex'] != "") { // summary
                $updateProduct .= "'live.i18n." . $this->_workingLanguage . ".fields.summary' : foo['value']['summary'],"; // live
                $updateProduct .= "'workspace.i18n." . $this->_workingLanguage . ".fields.summary' : foo['value']['summary'],"; // workspace
            }
            if ($this->_productOptions['baseSkuFieldIndex'] != "") { // base sku
                $updateProduct .= "'productProperties.sku' : foo['value']['productProperties']['sku'],";
            }
            if ($this->_productOptions['basePriceFieldIndex'] != "") { // base price
                $updateProduct .= "'productProperties.basePrice' : foo['value']['productProperties']['basePrice'],";
            }
            if ($this->_productOptions['preparationDelayFieldIndex'] != "") { // preparation delay
                $updateProduct .= "'productProperties.preparationDelay' : foo['value']['productProperties']['preparationDelay'],";
            }
            if ($this->_productOptions['skuFieldIndex'] != "") { // variation sku
                $updateVariations .= "'productProperties.variations.\$.sku' : v['sku'],";
            }
            if ($this->_productOptions['priceFieldIndex'] != "") { // variation price
                $updateVariations .= "'productProperties.variations.\$.price' : v['price'],";
            }
            if ($this->_productOptions['stockFieldIndex'] != "") { // variation stock
                $updateVariations .= "'productProperties.variations.\$.stock' : v['stock'],";
            }

        }

		// Add other fields
		foreach ($this->_importAsField as $key => $value) {
		
			$fieldName = $value['name'];
			
			if ($variationToUpdate && isset($value['useAsVariation']) && $value['useAsVariation']) {
				$queryVariations.= "'".$fieldName."': v['".$fieldName."'],";
			}
			
			if ($value['localizable']) { // localizable field is written in working language in i18n (live AND workspace)
			
				$updateProduct.= "'live.i18n.".$this->_workingLanguage.".fields.".$fieldName."' : foo['value']['".$fieldName."'],"; // live
				$updateProduct.= "'workspace.i18n.".$this->_workingLanguage.".fields.".$fieldName."' : foo['value']['".$fieldName."'],"; // workspace
			
			} else { // non localizable field is written in fields (live AND workspace)
				
				if (!in_array($value['name'],$variationFields)) {
					$updateProduct.= "'live.fields.".$fieldName."' : foo['value']['".$fieldName."'],"; // live
					$updateProduct.= "'workspace.fields.".$fieldName."' : foo['value']['".$fieldName."'],"; // workspace
				}
				
			}
			
		}

		$updateProduct.="}";
		if ($variationToUpdate) {
			$queryVariations.= "}}";
			$updateVariations.="}";
		}			
					
		$code = "var counter = 0;
				db.ImportContents.find().snapshot().forEach(function(foo) {
					db.Contents.findAndModify({query:{".$queryProduct."},update:{".$updateProduct."}});";
		if ($variationToUpdate) {
			$code.= "foo.value.productProperties.variations.forEach(function(v) {
						db.Contents.update({".$queryVariations."},{".$updateVariations."});
					});";
		}
		$code.= "counter++;});return counter;";

		$response = $this->_dataService->execute($code);

		if ($response['ok']!=1) {
			throw new \Rubedo\Exceptions\Server($code);
		}
			
		return $response['retval'];

    }


}

