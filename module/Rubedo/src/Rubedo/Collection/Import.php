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
namespace Rubedo\Collection;

use Rubedo\Services\Manager;
use Zend\Json\Json;

/**
 * Service to handle Import
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class Import extends AbstractCollection
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
     * User running the import process
     *
     * @var array
     */
    protected $currentUser;
    /**
     * Current Time
     *
     * @var string
     */
    protected $currentTime;
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
        $this->_collectionName = 'Import';
        parent::__construct();
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
            $this->uniqueKeyIndex = $options['uniqueKeyIndex'];
            $this->uniqueKeyField = $options['uniqueKeyField'];
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

        // Get current user and time

        $currentUserService = Manager::getService('CurrentUser');
        $this->currentUser = $currentUserService->getCurrentUserSummary();

        $currentTimeService = Manager::getService('CurrentTime');
        $this->currentTime = $currentTimeService->getCurrentTime();

        // Write file to import into Import collection
        $this->writeImportFile();

        // Extract taxonomy to ImportTaxonomy collection
        $this->extractTaxonomy();

        // Processing Import data taxonomy and localisation fields
        $this->preProcess();

        // Transform taxonomy terms into id
        $this->turnTermsToId();

        if ($this->_importMode == 'insert') { // INSERT mode

            // write taxonomy terms
            $this->writeTaxonomy();

            // Extract contents to ImportContents collection
            $this->extractContentsToInsert();

            // Finally write contents
            $response = $this->writeContents();

        } else { // UPDATE mode

            // Extract contents to ImportContents collection
            $this->extractContentsToUpdate();

            // Finally update contents
            $response = $this->updateContents();

        }

        return $response;

    }

    /**
     * Write file to Import collection
     */
    protected function writeImportFile()
    {

        // Read file to import
        $receivedFile = fopen($this->_fileName, 'r');

        // Read the first line to start at the second line
        fgetcsv($receivedFile, 1000000, $this->_separator, '"', '\\');

        $this->_dataService->emptyCollection();

        $data = array();

        while (($currentLine = fgetcsv($receivedFile, 1000000, $this->_separator, '"', '\\')) !== false) {

            // Encode fields
            foreach ($currentLine as $key => $string) {
                $utf8String = $this->forceUtf8($string, $this->_userEncoding);
                $currentLine['col' . $key] = $utf8String;
                unset($currentLine[$key]);
            }

            // Add import unique key to handle multiple imports
            $currentLine['importKey'] = $this->_importKeyValue;

            $data[] = $currentLine;

        }

        $this->_dataService->batchInsert($data, array());

        fclose($receivedFile);

        // create index on importKey

        $this->_dataService->ensureIndex('importKey');

        return true;
    }

    /**
     * Extract medias from Import
     * to ImportMedias collection
     */
    protected function extractMedia()
    {

        $filter = Filter::Factory('Value', array(
            'name' => 'importKey',
            'value' => $this->_importKeyValue
        ));
        $this->_dataService->addFilter($filter);
        $this->_dataService->addToFieldList(array("col1"));
        //var_dump($this->_dataService->read());

    }

    /**
     * Extract contents to insert from Import
     * to ImportContents collection
     */
    protected function extractContentsToInsert () {
    	
    	// Create fields
    	$fields = array();
    	
    	foreach ($this->_importAsField as $key => $value) {
    		
    		// Fields that are not product variations
	    	if (!isset($value['useAsVariation']) || ($value['useAsVariation'] == false)) {
	    			
		    	switch ($value['protoId']) {
		    		case 'text':
		    			$textFieldIndex = $value['csvIndex'];
		    			$fields['text'] = 'this.col'.$value['csvIndex'];
		    			break;
		    		case 'summary':
		    			$fields['summary'] = 'this.col'.$value['csvIndex'];
		    			break;
		    		default:
		    			if ($value['cType']!='localiserField') {
		    				$fields[$value['newName']] = 'this.col'.$value['csvIndex'];
		    			} else {
			   				$fields['position'] = array(
			    				'address' => '',
			    				'altitude' => '',
			   					'lat' => 'this.col'.$value['csvIndex'].'[0]',
			   					'lon' => 'this.col'.$value['csvIndex'].'[1]',
			    				'location' => array(
			    					'type' => 'Point',
			    					'coordinates' => array('this.col'.$value['csvIndex'].'[1]','this.col'.$value['csvIndex'].'[0]')
			    				)
			   				);
		    			}
		    			break;
		    	}
	    	}
    	}

    	// Copy in i18n
    	$contenti18n = array(
    			$this->_workingLanguage => array(
    					'fields' => $fields,
    					'locale' => $this->_workingLanguage
    			)
    	);

    	// Add translations
    	$languages = array();
    	foreach ($this->_importAsFieldTranslation as $fieldKey => $value) {
    	
    		foreach ($this->_importAsField as $key => $importedField) {
    			if ($importedField["csvIndex"] == $value["translatedElement"]) {
    				$importedFieldKey = $key;
    				break;
    			}
    		}
    		$translatedElement = $this->_importAsField[$importedFieldKey];
    		switch ($translatedElement['protoId']) {
    			case 'text':
    				$fieldName = "text";
    				break;
    			case 'summary':
    				$fieldName = "summary";
    				break;
    			default:
    				$fieldName = $translatedElement["newName"];
    				break;
    		}
    		if (! isset($contenti18n[$value["translateToLanguage"]]["locale"])) {
    			$contenti18n[$value["translateToLanguage"]]["locale"] = $value["translateToLanguage"];
    		}
    		$contenti18n[$value["translateToLanguage"]]["fields"][$fieldName] = 'this.col'.$value['csvIndex'];
    		if (! isset($languages[$value["translateToLanguage"]])) {
    			$languages[] = $value["translateToLanguage"];
    		}
    	}
    	
    	// Unset translation with empty text (title)
    	foreach ($languages as $lang) {
    		if (isset($contenti18n[$lang]["fields"]["text"]) && trim($contenti18n[$lang]["fields"]["text"]) == "") {
    			unset($contenti18n[$lang]);
    		}
    	}
    	
    	// add taxonomy
    	
    	$taxonomy = array();
    	$taxonomy['navigation'] = $this->_navigationTaxonomy;
    	
    	foreach ($this->_importAsTaxo as $key => $value) {
    		$taxonomy[$this->_vocabularies[$key+1]] = 'this.col'.$value['csvIndex'];
    	}
    	
    	$live = array(
    			'text' => 'this.col'.$textFieldIndex,
    			'summary' => isset($summaryFieldIndex) ? 'this.col.'.$summaryFieldIndex : '',
    			'fields' => $fields,
    			'status' =>  'published',
    			'writeWorkspace' =>  'global',
    			'startPublicationDate' =>  '',
    			'endPublicationDate' =>  '',
    			'nativeLanguage' =>  $this->_workingLanguage,
    			'readOnly' => false,
    			'i18n' => $contenti18n,
    			'taxonomy' => $taxonomy
    	);
    	
    	// json encode of live array
    	
    	$live = Json::encode($live);
    	
    	// gets rid off "" around javascript vars
    	
    	$patterns = array ('/\"(this.col[^\"]*)\"/');
    	$replace = array('\1');
    	$live = preg_replace($patterns, $replace, $live);
    	
    	$mapCode =	"function() {
    		var value = {
 				online: true,
				version: '1',
				createTime: currentTime,
				lastUpdateTime: currentTime,
				createUser: {
					id: currentUser['id'],
					login: currentUser['login'],
					fullName: currentUser['fullName']
				},
				lastUpdateUser: {
					id: currentUser['id'],
					login: currentUser['login'],
					fullName: currentUser['fullName']
				},
				text: this.col".$textFieldIndex.",
				typeId: typeId,
				target: target,
				live: ".$live.",
				workspace: ".$live;
			
    	
    	if ($this->_isProduct) {
    		$mapCode.=",isProduct:true, 
    				baseSku: this.col".$this->_productOptions['baseSkuFieldIndex'].",
    				basePrice: this.col".$this->_productOptions['basePriceFieldIndex'].",
    				sku: this.col".$this->_productOptions['skuFieldIndex'].",
    				price: this.col".$this->_productOptions['priceFieldIndex'].",
    				stock: this.col".$this->_productOptions['stockFieldIndex'].",
    				preparationDelay: this.col".$this->_productOptions['preparationDelayFieldIndex'];
    				// add variation fields
    				foreach ($this->_importAsField as $key => $value) {
    					if (isset($value['useAsVariation']) && $value['useAsVariation']) {
    						$mapCode.=",'".$value['newName']."': this.col".$value['csvIndex'];
    					}
    				}
    	}

    	$mapCode.= "};";
		$mapKey = $this->_isProduct ? "this.col".$this->_productOptions['baseSkuFieldIndex'] : "this._id";

		$mapCode.="emit(".$mapKey.", value);};";

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
    				$reduceCode.="variation['".$value['newName']."']=v['".$value['newName']."'];";    				
    			}	   			
    		}
    		
    		$reduceCode.="
    				variations.push(variation);
    			});
    			
    			productProperties['variations'] = variations;	
    			value['productProperties'] = productProperties;
				return value;
    			
    		};";	
    	}
    	
    	$reduce = new \MongoCode($reduceCode);

    	$finalizeCode = "function(key,value) {

    		if (value['productProperties']==null) {
    			
    			value['productProperties'] = {
    				sku : value.baseSku,
					basePrice: value.basePrice,
					preparationDelay: value.preparationDelay,
					canOrderNotInStock: false,
					outOfStockLimit: 1,
					notifyForQuantityBelow : 1,
					resupplyDelay : 1,
    			};
    			oid = ObjectId();
    			var variation = {
    				price: value.price,
    				stock: value.stock,
    				sku: value.sku,
    				id: oid.valueOf()
				};
    	";		
    		
    	// add variation fields
    	foreach ($this->_importAsField as $key => $value) {    		
    		if (isset($value['useAsVariation']) && $value['useAsVariation']) {
    			$finalizeCode.="variation['".$value['newName']."']=value['".$value['newName']."'];";    				
    		}	   			
    	}

   		$finalizeCode.= "
   			value['productProperties']['variations']=[variation];
	   		};
    		delete value['baseSku'];
    		delete value['basePrice'];
    		delete value['sku'];
    		delete value['price'];
    		delete value['stock'];	
    	";
    	
    	foreach ($this->_importAsField as $key => $value) {
    		if (isset($value['useAsVariation']) && $value['useAsVariation']) {
    			$finalizeCode.="delete value['".$value['newName']."'];";
    		}
    	}		
		$finalizeCode .="return (value);}";
    	
    	$finalize = new \MongoCode($finalizeCode);
    	
    	// global JavaScript variables passed to map, reduce and finalize functions
    	$scope = array(
    			"currentTime" => $this->currentTime,
    			"currentUser" => $this->currentUser,
    			"typeId" => $this->_typeId,
    			"target" => $this->_target
    	);
    	
    	$params = array(
    			"mapreduce" => "Import", // collection
    			"query" => array("importKey" => $this->_importKeyValue), // query
    			"map" => $map, // map
    			"reduce" => $reduce, // reduce
    			"scope" => $scope, // scope
    			"out" => array("replace" => "ImportContents") // out
    	);
    	if ($this->_isProduct) $params["finalize"] = $finalize;

    	$response = $this->_dataService->command($params);

    	if ($response['ok']!=1) {
				throw new \Rubedo\Exceptions\Server($response);
			}
		
    	return true;

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
            if ($this->_productOptions['textFieldIndex'] != "") $mapCode .= "text: this.col" . $this->_productOptions['textFieldIndex'] . ",";
            if ($this->_productOptions['summaryFieldIndex'] != "") $mapCode .= "summary: this.col" . $this->_productOptions['summaryFieldIndex'] . ",";
            if ($this->_productOptions['baseSkuFieldIndex'] != "") $mapCode .= "baseSku: this.col" . $this->_productOptions['baseSkuFieldIndex'] . ",";
            if ($this->_productOptions['basePriceFieldIndex'] != "") $mapCode .= "basePrice: this.col" . $this->_productOptions['basePriceFieldIndex'] . ",";
            if ($this->_productOptions['preparationDelayFieldIndex'] != "") $mapCode .= "preparationDelay: this.col" . $this->_productOptions['preparationDelayFieldIndex'] . ",";
            if ($this->_productOptions['priceFieldIndex'] != "") $mapCode .= "price: this.col" . $this->_productOptions['priceFieldIndex'] . ",";
            if ($this->_productOptions['stockFieldIndex'] != "") $mapCode .= "stock: this.col" . $this->_productOptions['stockFieldIndex'] . ",";
            if ($this->_productOptions['skuFieldIndex'] != "") $mapCode .= "sku: this.col" . $this->_productOptions['skuFieldIndex'];

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
            "mapreduce" => "Import", // collection
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

    /**
     * Extract tanonomy terms from Import collection
     * and copy it to ImporTaxo collection
     */
    protected function extractTaxonomy()
    {

        // Create map reduce
        foreach ($this->_importAsTaxo as $key => $value) {

            $vocabularyId = $this->_vocabularies[$key + 1];

            $mapCode = "
					function() {
					var terms_" . $this->_workingLanguage . " = this.col" . $value["csvIndex"] . ".split(',');";

            foreach ($this->_importAsTaxoTranslation as $transKey => $transValue) {
                if ($transValue["translatedElement"] == $value['csvIndex']) {
                    $mapCode .= "var terms_" . $transValue["translateToLanguage"] . " = this.col" . $transValue["csvIndex"] . ".split(',');";
                }
            }

            $mapCode .= "
						for (var i = 0; i < terms_" . $this->_workingLanguage . ".length; i++) {
						var key = terms_" . $this->_workingLanguage . "[i];
						if (key) { 
								var value = {" . $this->_workingLanguage . ": terms_" . $this->_workingLanguage . "[i]};";
            foreach ($this->_importAsTaxoTranslation as $transKey => $transValue) {
                if ($transValue["translatedElement"] == $value['csvIndex']) {
                    $mapCode .= "if (terms_" . $transValue["translateToLanguage"] . "[i]) {";
                    $mapCode .= "value." . $transValue["translateToLanguage"] . " = terms_" . $transValue["translateToLanguage"] . "[i];";
                    $mapCode .= "};";
                }
            }
            $mapCode .= "
							}
							emit(key, value);
						}
				};";

            $map = new \MongoCode($mapCode);

            $reduce = new \MongoCode("function(key, values) { return {key: values[0]} }");

            $finalizeCode = "function(key,value) {
					oid = ObjectId();
					finalValue = {
						_id: oid,
						text: key,
						vocabularyId: vocabularyId,
						parentId: 'root',
						leaf:  true,
						expandable:  'false',
						nativeLanguage:  workingLanguage,
						version: '1',
						createTime: currentTime,
						lastUpdateTime: currentTime,
						createUser: {
							'id': currentUser['id'],
							'login': currentUser['login'],
							'fullName': currentUser['fullName']
						},
						lastUpdateUser: {
							'id': currentUser['id'],
							'login': currentUser['login'],
							'fullName': currentUser['fullName']
						},
						i18n: {
							" . $this->_workingLanguage . ": {
							'text':key,
							'locale': workingLanguage
							}
						}
					};";

            foreach ($this->_importAsTaxoTranslation as $transKey => $transValue) {

                if ($transValue["translatedElement"] == $value['csvIndex']) {

                    $finalizeCode .= "if (value." . $transValue["translateToLanguage"] . ") {";
                    $finalizeCode .= "finalValue.i18n." . $transValue["translateToLanguage"] . "= {
							text:value." . $transValue["translateToLanguage"] . ",
							locale:'" . $transValue["translateToLanguage"] . "'};};";
                }
            }

            $finalizeCode .= "return (finalValue);}";

            $finalize = new \MongoCode($finalizeCode);

            // global JavaScript variables passed to map, reduce and finalize functions
            $scope = array(
                "workingLanguage" => $this->_workingLanguage,
                "currentTime" => $this->currentTime,
                "currentUser" => $this->currentUser,
                "vocabularyId" => $vocabularyId
            );

            $outAction = ($key == 0) ? "replace" : "merge";

            $params = array(
                "mapreduce" => "Import", // collection
                "query" => array("importKey" => $this->_importKeyValue), // query
                "map" => $map, // map
                "reduce" => $reduce, // reduce
                "finalize" => $finalize, // finalyse
                "scope" => $scope, // scope
                "out" => array($outAction => "ImportTaxo") // out
            );

            $response = $this->_dataService->command($params);

            if ($response['ok'] != 1) {
                throw new \Rubedo\Exceptions\Server("Extracting Taxonomy error", $response["errmsg"]);
            }
        }
        return true;
    }

    /**
     * Preprocessing Data in Import collection :
     * Transform the taxononomy comma separated string into array
     * Transform the localization comma separated lat,lon string into array
     * Transform prices with comma separtor to dot separator, cast to float
     */
    protected function preProcess()
    {

        $code = "db.Import.find().snapshot().forEach(function(e){";

        foreach ($this->_importAsTaxo as $taxo) {
            $code .= "e.col" . $taxo['csvIndex'] . " = e.col" . $taxo['csvIndex'] . ".split(',');";
        }

        foreach ($this->_importAsField as $field) {

            if (isset($field['cType']) && ($field['cType'] == 'localiserField')) {
                $code .= "e.col" . $field['csvIndex'] . "= e.col" . $field['csvIndex'] . ".split(',').map(parseFloat);";
            }

            if (isset($field['cType']) && (in_array($field['cType'], array('numberField', 'slider', 'ratingField')))) {
                $code .= "e.col" . $field['csvIndex'] . "= parseInt(e.col" . $field['csvIndex'] . ".replace(',', '.'));";
            }

        }

        if ($this->_isProduct) {
            if ($this->_productOptions['basePriceFieldIndex'] != '') {
                $code .= "e.col" . $this->_productOptions['basePriceFieldIndex'] . "= parseFloat(e.col" . $this->_productOptions['basePriceFieldIndex'] . ".replace(',', '.'));";
            }
            if ($this->_productOptions['priceFieldIndex'] != '') {
                $code .= "e.col" . $this->_productOptions['priceFieldIndex'] . "= parseFloat(e.col" . $this->_productOptions['priceFieldIndex'] . ".replace(',', '.'));";
            }
            if ($this->_productOptions['stockFieldIndex'] != '') {
                $code .= "e.col" . $this->_productOptions['stockFieldIndex'] . "= parseInt(e.col" . $this->_productOptions['stockFieldIndex'] . ".replace(',', '.'));";
            }
            if ($this->_productOptions['preparationDelayFieldIndex'] != '') {
                $code .= "e.col" . $this->_productOptions['preparationDelayFieldIndex'] . "= parseInt(e.col" . $this->_productOptions['preparationDelayFieldIndex'] . ".replace(',', '.'));";
            }
        }

        $code .= "db.Import.save(e);})";

        $response = $this->_dataService->execute($code);
        return $response;
    }

    /**
     * Transform the array of terms into array of terms id
     */
    protected function turnTermsToId()
    {

        foreach ($this->_importAsTaxo as $taxo) {

            $code = "db.Import.ensureIndex({col" . $taxo['csvIndex'] . ":1});";
            $response = $this->_dataService->execute($code);

            $code = "db.ImportTaxo.find().snapshot().forEach(
			function(e) {
				var text = e._id;
				var id = e.value._id;
				db.Import.update({col" . $taxo['csvIndex'] . ": text},{\$set: {\"col" . $taxo['csvIndex'] . ".\$\" : id.str}},{ multi: true });
			})";
            $response = $this->_dataService->execute($code);
            if ($response['ok'] != 1) {
                throw new \Rubedo\Exceptions\Server("Turning Terms to id error");
            }

        }

        return true;

    }

    /**
     * Write taxonomy terms and flush import collection
     */
    protected function writeTaxonomy()
    {

        foreach ($this->_importAsTaxo as $taxo) {

            $code = "db.ImportTaxo.find().snapshot().forEach(
			function(foo) {
				if (foo.value.text > '') {
					db.TaxonomyTerms.insert(foo.value);
				}
			})";
            $response = $this->_dataService->execute($code);
            if ($response['ok'] != 1) {
                throw new \Rubedo\Exceptions\Server("Writing Taxonomy error");
            }
        }

        return true;

    }

    /**
     * Write contents and flush import collection
     */
    protected function writeContents()
    {

        $code = "var counter = 0;
				db.ImportContents.find().snapshot().forEach(function(foo) {
					db.Contents.insert(foo.value);
					counter++;
				});
				return counter;
				";
        $response = $this->_dataService->execute($code);

        if ($response['ok'] != 1) {
            throw new \Rubedo\Exceptions\Server("Writing Contents error");
        }

        return $response['retval'];

    }

    /**
     * Update contents and flush import collection
     */
    protected function updateContents()
    {

        // get variation fields from content type

        $variationFields = Manager::getService("ContentTypes")->getVariationFieldForCType($this->_typeId);

        // 2 Map reduce : one for generic product and one for

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

            if ($variationToUpdate && $value['useAsVariation']) {
                $queryVariations .= "'" . $fieldName . "': v['" . $fieldName . "'],";
            }

            if ($value['localizable']) { // localizable field is written in working language in i18n (live AND workspace)

                $fieldsToUpdate[] = "'live.i18n." . $this->_workingLanguage . ".fields." . $fieldName . "'"; // live
                $fieldsToUpdate[] = "'workspace.i18n." . $this->_workingLanguage . ".fields." . $fieldName . "'"; // workspace

            } else { // non localizable field is written in fields (live AND workspace)

                if (!in_array($value['name'], $variationFields)) {
                    $fieldsToUpdate[] = "'live.fields." . $fieldName . "'"; // live
                    $fieldsToUpdate[] = "'workspace.fields." . $fieldName . "'"; // workspace
                }

            }

        }

        $updateProduct .= "}";
        if ($variationToUpdate) {
            $queryVariations .= "}}";
            $updateVariations .= "}";
        }

        $code = "var counter = 0;
				db.ImportContents.find().snapshot().forEach(function(foo) {
					db.Contents.findAndModify({query:{" . $queryProduct . "},update:{" . $updateProduct . "}});";
        if ($variationToUpdate) {
            $code .= "foo.value.productProperties.variations.forEach(function(v) {
						db.Contents.update({" . $queryVariations . "},{" . $updateVariations . "});
					});";
        }
        $code .= "counter++;});return counter;";

        $response = $this->_dataService->execute($code);

        if ($response['ok'] != 1) {
            throw new \Rubedo\Exceptions\Server($code);
        }

        return $response['retval'];

    }

    /**
     * Return the given string encoded in UTF-8
     *
     * @param string $string
     *            The string wich will be encoded
     * @param string $encoding
     *            The current encoding of the string
     * @return string Encoded string in UTF-8
     */
    protected function forceUtf8($string, $encoding)
    {
        return mb_convert_encoding($string, "UTF-8", $encoding);
    }

}

