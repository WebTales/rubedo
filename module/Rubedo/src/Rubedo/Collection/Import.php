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

use Rubedo\Services\Manager;
use Rubedo\Mongo\DataAccess;
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
    public function __construct()
    {
        $this->_collectionName = 'Import';
        parent::__construct();
    }
    
    public function run($fileName, $options) {

    	// Get import settings
    	$importKeyValue = $options['importKey'];
    	$userEncoding = $options['userEncoding'];
    	$importAsField = $options['importAsField'];
    	$importAsFieldTranslation = $options['importAsFieldTranslation'];
    	$importAsTaxo = $options['importAsTaxo'];
    	$importAsTaxoTranslation = $options['importAsTaxoTranslation'];
    	$workingLanguage = $options['workingLanguage'];
    	$separator = isset($options['separator']) ? $options['separator'] : ';';
    	$vocabularies = $options['vocabularies'];
    	$navigationTaxonomy = $options['contentsNavTaxo'];
    	$target = $options['contentsTarget'];
    	$typeId = $options['typeId'];

    	// Write file to import into Import collection
    	$this->writeImportFile ($fileName, $importKeyValue, $userEncoding, $separator);
    	
    	// Extract taxonomy to ImportTaxonomy collection
    	$this->extractTaxonomy($importKeyValue,$importAsTaxo,$importAsTaxoTranslation,$workingLanguage, $vocabularies);
    	
    	// Processing Import data taxonomy and localisation fields
    	$this->preProcess ($importAsTaxo, $importAsField);
    	
    	// Transform taxonomy terms into id
    	$this->turnTermsToId ($importAsTaxo);
    	
    	// write taxonomy terms
    	$this->writeTaxonomy ($importAsTaxo);
    	
    	// Extract contents to ImportContents collection
    	$this->extractContents ($importKeyValue, $importAsField, $importAsFieldTranslation, $importAsTaxo, $workingLanguage, $vocabularies, $navigationTaxonomy, $target, $typeId);
    	
    	// Finally write contents
    	$response = $this->writeContents();
    	
    	return $response;
    	
    }
    
    protected function writeImportFile ($fileName, $importKeyValue, $userEncoding, $separator) {
    	
    	// Read file to import
    	$receivedFile = fopen($fileName, 'r');
    	
    	// Read the first line to start at the second line
    	fgetcsv($receivedFile, 1000000, $separator, '"', '\\');
    	   	
		$this->_dataService->emptyCollection();
		
		$data = array();
		
    	while (($currentLine = fgetcsv($receivedFile, 1000000, $separator, '"', '\\')) !== false) {
    		   	
    		// Encode fields
    		foreach ($currentLine as $key => $string) {
    			$utf8String = $this->forceUtf8($string, $userEncoding);
    			$currentLine['col'.$key] = $utf8String;
    			unset($currentLine[$key]);
    		}
    		
    		// Add import unique key to handle multiple imports
    		$currentLine['importKey'] = $importKeyValue;
    	
    		$data[] = $currentLine;
    	
    	}
    	$this->_dataService->batchInsert($data, array());

    	fclose($receivedFile);
    	return  true;
    }
	
    protected function extractContents ($importKeyValue, $importAsField, $importAsFieldTranslation, $importAsTaxo, $workingLanguage, $vocabularies, $navigationTaxonomy, $target, $typeId) {
    	
    	// Create fields
    	$fields = array();
    	foreach ($importAsField as $key => $value) {
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

    	// Copy in i18n
    	$contenti18n = array(
    			$workingLanguage => array(
    					'fields' => $fields,
    					'locale' => $workingLanguage
    			)
    	);

    	// Add translations
    	$languages = array();
    	foreach ($importAsFieldTranslation as $fieldKey => $value) {
    	
    		foreach ($importAsField as $key => $importedField) {
    			if ($importedField["csvIndex"] == $value["translatedElement"]) {
    				$importedFieldKey = $key;
    				break;
    			}
    		}
    		$translatedElement = $importAsField[$importedFieldKey];
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
    	
    	$currentUserService = Manager::getService('CurrentUser');
    	$currentUser = $currentUserService->getCurrentUserSummary();
    		
    	$currentTimeService = Manager::getService('CurrentTime');
    	$currentTime = $currentTimeService->getCurrentTime();
    	
    	// add taxonomy
    	
    	$taxonomy = array();
    	$taxonomy['navigation'] = $navigationTaxonomy;
    	
    	foreach ($importAsTaxo as $key => $value) {
    		$taxonomy[$vocabularies[$key+1]] = 'this.col'.$value['csvIndex'];
    	}
    	
    	$live = array(
    			'text' => 'this.col'.$textFieldIndex,
    			'summary' => isset($summaryFieldIndex) ? 'this.col.'.$summaryFieldIndex : '',
    			'fields' => $fields,
    			'status' =>  'published',
    			'writeWorkspace' =>  'global',
    			'startPublicationDate' =>  '',
    			'endPublicationDate' =>  '',
    			'nativeLanguage' =>  $workingLanguage,
    			'readOnly' => false,
    			'i18n' => $contenti18n,
    			'taxonomy' => $taxonomy
    	);
    	
    	// json encode of live array
    	
    	$live = Json::encode($live);
    	
    	// get rid off "" around javascript vars
    	
    	$patterns = array ('/\"(this.col[^\"]*)\"/');
    	$replace = array('\1');
    	$live = preg_replace($patterns, $replace, $live);
    	
    	$user = array(
    			'id' => $currentUser['id'],
    			'login' => $currentUser['login'],
    			'fullName' => $currentUser['fullName']
    	);

    	$mapCode =	"
    	function() {
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
				workspace: ".$live."
			};
			emit(this._id, value);
		};";

    	$map = new \MongoCode($mapCode);
    	
    	$reduce = new \MongoCode("function(key, values) { return {key: values[0]} }");
    	
    	// global JavaScript variables passed to map, reduce and finalize functions
    	$scope = array(
    			"currentTime" => $currentTime,
    			"currentUser" => $currentUser,
    			"typeId" => $typeId,
    			"target" => $target
    	);
    	
    	$params = array(
    			"mapreduce" => "Import", // collection
    			"query" => array("importKey" => $importKeyValue), // query
    			"map" => $map, // map
    			"reduce" => $reduce, // reduce
    			"scope" => $scope, // scope
    			"out" => array("replace" => "ImportContents") // out
    	);
    	$response = $this->_dataService->command($params);

    	if ($response['ok']!=1) {
				throw new \Rubedo\Exceptions\Server("Extracting Contents error");
			}
		
    	return true;

    }
    
	/**
	 * Extract tanonomy terms from Import collection
	 *
	 * and copy it to ImporTaxo collection
	 * 
	 * @param array $options
	 *
	 */
	protected function extractTaxonomy ($importKeyValue,$importAsTaxo,$importAsTaxoTranslation,$workingLanguage, $vocabularies) {	

		// Get current time and user
		
		$currentUserService = Manager::getService('CurrentUser');
		$currentUser = $currentUserService->getCurrentUserSummary();
			
		$currentTimeService = Manager::getService('CurrentTime');
		$currentTime = $currentTimeService->getCurrentTime();
		
		// Create map reduce
		foreach ($importAsTaxo as $key => $value) {
				
			$vocabularyId = $vocabularies[$key+1];
		
			$mapCode =	"
					function() {
					var terms_".$workingLanguage." = this.col".$value["csvIndex"].".split(',');";
				
			foreach ($importAsTaxoTranslation as $transKey => $transValue) {
				if ($transValue["translatedElement"] == $value['csvIndex']) {
					$mapCode.=	"var terms_".$transValue["translateToLanguage"]." = this.col".$transValue["csvIndex"].".split(',');";
				}
			}
		
			$mapCode.=	"
						for (var i = 0; i < terms_".$workingLanguage.".length; i++) {
						var key = terms_".$workingLanguage."[i];
						if (key) { 
								var value = {".$workingLanguage.": terms_".$workingLanguage."[i]};";
				foreach ($importAsTaxoTranslation as $transKey => $transValue) {
					if ($transValue["translatedElement"] == $value['csvIndex']) {
						$mapCode.= "if (terms_".$transValue["translateToLanguage"]."[i]) {";
						$mapCode.=	"value.".$transValue["translateToLanguage"]." = terms_".$transValue["translateToLanguage"]."[i];";
					    $mapCode.=	"};";
					}
				}
				$mapCode.=	"
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
							".$workingLanguage.": {
							'text':key,
							'locale': workingLanguage
							}
						}
					};";
			
			foreach ($importAsTaxoTranslation as $transKey => $transValue) {
				
				if ($transValue["translatedElement"] == $value['csvIndex']) {
					
					$finalizeCode.=	"if (value.".$transValue["translateToLanguage"].") {";
					$finalizeCode.=	"finalValue.i18n.".$transValue["translateToLanguage"]."= { 
							text:value.".$transValue["translateToLanguage"].",
							locale:'".$transValue["translateToLanguage"]."'};};";
				}
			}
			
			$finalizeCode .= "return (finalValue);}";

			$finalize = new \MongoCode($finalizeCode);

			// global JavaScript variables passed to map, reduce and finalize functions
			$scope = array(
					"workingLanguage" => $workingLanguage,
					"currentTime" => $currentTime,
					"currentUser" => $currentUser,
					"vocabularyId" => $vocabularyId
			);
				
			$params = array(
					"mapreduce" => "Import", // collection
					"query" => array("importKey" => $importKeyValue), // query
					"map" => $map, // map
					"reduce" => $reduce, // reduce
					"finalize" => $finalize, // finalyse
					"scope" => $scope, // scope
					"out" => array("replace" => "ImportTaxo") // out
			);
			$response = $this->_dataService->command($params);

			if ($response['ok']!=1) {
				throw new \Rubedo\Exceptions\Server("Extracting Taxonomy error",$response["errmsg"]);
			}
			
			
			
			return true;

		}
	
	}
	
	/**
	 * Preprocessing Data inti Import collection :
	 * Transform the taxnonomy comma separated string into array 
	 * Transform the localization comma separated lat,lon string into array
	 *
	 * @param array $importAsTaxo
	 *           
	 */	
	protected function preProcess ($importAsTaxo, $importAsField) {
		
		$code = "db.Import.find().snapshot().forEach(function(e){";
			
		foreach($importAsTaxo as $taxo) {
			$code.= "e.col".$taxo['csvIndex']." = e.col".$taxo['csvIndex'].".split(',');";
		}
			
		foreach ($importAsField as $field) {
				
			if ($field['cType']=='localiserField') {
				$code.= "e.col".$field['csvIndex']."= e.col".$field['csvIndex'].".split(',').map(parseFloat);";
			}
		
		}
			
		$code.= "db.Import.save(e);})";
			
		$response = $this->_dataService->execute($code);
		return $response;
	}
	
	/**
	 * Transform the array of terms into array of terms id
	 *
	 * @param array $importAsTaxo
	 *
	 */
	protected function turnTermsToId ($importAsTaxo) {
		
		foreach($importAsTaxo as $taxo) {
			
			$code = "db.ImportTaxo.find().forEach(
			function(e) {
				var text = e._id;
				var id = e.value._id;
				db.Import.update({col".$taxo['csvIndex'].": text},{\$set: {\"col".$taxo['csvIndex'].".\$\" : id.str}},{ multi: true });
			})";
			$response = $this->_dataService->execute($code);
			if ($response['ok']!=1) {
				throw new \Rubedo\Exceptions\Server("Turning Terms to id error");
			}

		}
		
		return true;
		
	}

	/**
	 * Write taxonomy terms and flush import collection
	 *
	 * @param array $importAsTaxo
	 *
	 */
	protected function writeTaxonomy ($importAsTaxo) {
	
		foreach($importAsTaxo as $taxo) {
				
			$code = "db.ImportTaxo.find().forEach(
			function(foo) {
				if (foo.value.text > '') {
					db.TaxonomyTerms.insert(foo.value);
				}
			})";
			$response = $this->_dataService->execute($code);
			if ($response['ok']!=1) {
				throw new \Rubedo\Exceptions\Server("Writing Taxonomy error");
			}
		}
	
		return true;
	
	}

	/**
	 * Write contents and flush import collection
	 *
	 * @param array $importAsTaxo
	 *
	 */
	protected function writeContents () {
	
		$code = "var counter = 0;
				db.ImportContents.find().forEach(function(foo) {
					db.Contents.insert(foo.value);
					counter++;
				});
				return counter;
				";
		$response = $this->_dataService->execute($code);
		
		if ($response['ok']!=1) {
			throw new \Rubedo\Exceptions\Server("Writing Contents error");
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
    protected function forceUtf8 ($string, $encoding)
    {
    	return mb_convert_encoding($string, "UTF-8", $encoding);
    }

}

