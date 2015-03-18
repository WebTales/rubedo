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
            // Add system fields text and summary
            if (isset($options['text']) && is_int($options['text'])) {
            	$this->_importAsField[] = array(
            		'csvIndex' => $options['text'],
            		'protoId' => 'text'
            	);
            }
            if (isset($options['summary']) && is_int($options['summary'])) {
            	$this->_importAsField[] = array(
            			'csvIndex' => $options['summary'],
            			'protoId' => 'summary'
            	);
            }
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
	   	
   			// Fetch content to update
   			
   			$findFilter = Filter::Factory();
   			
   			$filter = Filter::factory('Value')->setName('typeId')->setValue($this->_typeId);
   			$findFilter->addFilter($filter);

   			switch ($this->_uniqueKeyField) {
   				case 'sku':
   					$filterName = 'productProperties.sku';
   					$filterValue = $record['col'.$this->_productOptions['baseSkuFieldIndex']];
   					break;
   				default:			
   					$filterName = "live.i18n.".$this->_workingLanguage.".fields.".$this->_uniqueKeyField;
   					$filterValue = $record['col'.$this->_uniqueKeyIndex];
   					break;
   			}
   			
   			$filter = Filter::factory('Value')->setName($filterName)->setValue($filterValue);
   			$findFilter->addFilter($filter);	   			
   			
   			$contentToUpdate = Manager::getService('Contents')->findOne($findFilter,true,false);

   			// If the content to update exists
   			
   			if ($contentToUpdate) {

	   			// Process fields
	
	   			$fields = [];
	   			$variationFields = [];
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
		            	$variationFields[$value['newName']] = $record[$column];

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
		        	
		        	
		        	// Update variation fields
		        	$importProducts = Manager::getService('ImportProducts');
		        	$filter = Filter::factory('Value')->setName('_id')->setValue($record['col'.$this->_productOptions['baseSkuFieldIndex']]);
		        	$variation = Manager::getService('ImportProducts')->findOne($filter);
		        	$variationPrice = [];
		        	$variationStock = [];
		        	foreach($variation['value']['productProperties']['variations'] as $variationValue) {
		        		if ($variationValue['sku'] == $record['col'.$this->_productOptions['skuFieldIndex']]) {
			        		if (is_int($this->_productOptions['priceFieldIndex'])) $variationPrice[$variationValue['sku']] = $record['col'.$this->_productOptions['priceFieldIndex']];
			        		if (is_int($this->_productOptions['stockFieldIndex'])) $variationStock[$variationValue['sku']] = $record['col'.$this->_productOptions['stockFieldIndex']];
			        		break;
		        		}
		        	}
	
		        	foreach ($contentToUpdate['productProperties']['variations'] as $variationKey => $variationValue) {
		        		if ($variationValue['sku'] == $record['col'.$this->_productOptions['skuFieldIndex']]) {
		        		
			        		// Variation price update
			        		if (is_integer($this->_productOptions['priceFieldIndex'])) {
			        			$contentToUpdate['productProperties']['variations'][$variationKey]['price'] = $variationPrice[$variationValue['sku']];
			        		}
			        		// Variation stock update
			        		if (is_integer($this->_productOptions['stockFieldIndex'])) {
			        			$contentToUpdate['productProperties']['variations'][$variationKey]['stock'] = $variationStock[$variationValue['sku']];
			        		}
			        		// Variations update			        		
			        		foreach($variationFields as $variationFieldName => $variationFieldValue) {
			        			$contentToUpdate['productProperties']['variations'][$variationKey][$variationFieldName] = $variationFieldValue;
			        		}
			        		break;
		        		}
		        	}
		        	      
		        }		        
		        
		        if (is_array($contentToUpdate['fields'])) {
		        	
		        	$contentToUpdate['fields'] = array_replace_recursive($contentToUpdate['fields'],$fields);
		        	
		        	$contentToUpdate['i18n'][$this->_workingLanguage]['fields'] = array_replace_recursive($contentToUpdate['i18n'][$this->_workingLanguage]['fields'],$fields);

		        	// Finally update content
		        	$result = Manager::getService('Contents')->update($contentToUpdate, array(), false);
	
		        	if ($result['success']) {
		        		if ($this->_isProduct && ($previousRecordBaseSku == $record['col'.$this->_productOptions['baseSkuFieldIndex']])) {
		        		
		        			// skip record
		        			 
		        		} else {
		        			$counter++;
		        		}
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
   
}

