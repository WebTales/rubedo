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
namespace Rubedo\Backoffice\Controller;

use Rubedo\Services\Manager;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;
use Zend\Console\Prompt\Number;

/**
 * Controller providing data import for csv
 *
 *
 *
 *
 * @author adobre
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 *         
 */
class ImportController extends DataAccessController
{

    /**
     * Array with the read only actions
     */
    protected $_readOnlyAction = array();

    /**
     * Return the encoding of the string
     *
     * @param string $string            
     * @return array List of possible encodings of the string
     */
    protected function getEncoding ($string)
    {
        $result = array();
        
        // Get the list of possible encodings
        foreach (mb_list_encodings() as $value) {
            if (in_array($value, array(
                'auto',
                'pass'
            ))) {
                continue;
            }
            if (mb_check_encoding($string, $value)) {
                $result["charsetList"][] = $value;
            }
        }
        
        // Throw an exception if neither encoding match with the string
        if (! isset($result["charsetList"])) {
            throw new \Rubedo\Exceptions\Server("The server cannot find the charset of the current file.", "Exception95");
        }
        
        // Define the main encodings
        $mainEncodings = array(
            "UTF-8",
            "ISO-8859-15",
            "ISO-8859-1",
            "Windows-1252"
        );
        
        // If one of the main encodings is in the list of possible encodings, we send the first value
        foreach ($mainEncodings as $encoding) {
            if (in_array($encoding, $result["charsetList"])) {
                $result["defaultEncoding"] = $encoding;
                break;
            }
        }
        
        return $result;
    }

    /**
     * Return the encoding of the string
     *
     * @param string $string            
     * @param string $encoding
     *            Contain the expected encoding of the string
     *            for example : UTF-8
     *            ISO-8859-15
     *            ISO-8859-1
     * @return boolean true if the encoding match with the string
     */
    protected function checkEncoding ($string, $encoding)
    {
        return mb_check_encoding($string, $encoding);
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

    /**
     * Create new content type
     *
     * @param string $options
     *            Options
     * @return String id of the created content type
     */
    protected function createContentType ($options)
    {
    	$CTfields = array();
    	foreach ($options['importAsField'] as $key => $value) {
    		if ($value['protoId']!='text' && $value['protoId']!='summary') {
    			if ($value['cType'] == "localiserField") {
    				$value['newName'] = "position";
    			}
    			$newFieldForCT = array(
    					"cType" => $value['cType'],
    					"config" => array(
    							"name" => $value['newName'],
    							"fieldLabel" => $value['label'],
    							"allowBlank" => ! $value['mandatory'],
    							"localizable" => $value['localizable'],
    							"searchable" => $value['searchable'],
    							"multivalued" => false,
    							"tooltip" => "",
    							"labelSeparator" => " "
    					),
    					"protoId" => $value['protoId'],
    					"openWindow" => null
    			);
    			// For products only
    			if ($options['isProduct']) {
    				$newFieldForCT['config']['useAsVariation'] = isset($value['useAsVariation']) ? $value['useAsVariation'] : false;
    			}
    			$CTfields[] = $newFieldForCT;
    		}
    	}
    	
    	$newCTi18n = array();
    	$newCTi18n[$options['workingLanguage']] = array(
    			"type" => $options['ContentTypeType']
    	);
    	$contentTypeParams = array(
    			"dependant" => false,
    			"code"=>$options['ContentTypeType'],
    			"dependantTypes" => array(),
    			"type" => $options['ContentTypeType'],
    			"fields" => $CTfields,
    			"vocabularies" => $options['vocabularies'],
    			"workspaces" => $options['ContentTypeWorkspaces'],
    			"workflow" => $options['ContentTypeWorkflow'],
    			"activateDisqus" => false,
    			"nativeLanguage" => $options['workingLanguage'],
    			"i18n" => $newCTi18n
    	);
    		
    	// For products only
    	if ($options['isProduct']) {
    		$productTypeParams = array(
    				"canOrderNotInStock" => false,
    				"manageStock" => true,
    				"notifyForQuantityBelow" => 1,
    				"outOfStockLimit" => 1,
    				"preparationDelay" => 0,
    				"productType" => "configurable",
    				"resupplyDelay" => 0,
    				"shippers" => ""
    		);
    		$contentTypeParams = array_merge($contentTypeParams, $productTypeParams);
    	}
    		
    	$contentType = Manager::getService('ContentTypes')->create($contentTypeParams);
    	return $contentType['data']['id'];    	
    }
    
    /**
     * Create new vocabularies if they do not exist
     *
     * @param string $options
     *            Options
     * @return array ids of the created vocabularies
     */
    protected function createTaxonomy ($options)
    {

    	$newTaxos = array();
    	$newTaxos[]= "navigation";
		$taxonomyService = Manager::getService('Taxonomy');
		foreach ($options['importAsTaxo'] as $key => $value) {

			$taxonomy = $taxonomyService->findByName($value['newName']);
			
			if ($taxonomy) { // If vocabulary already exists
				
				$newTaxos[] = $taxonomy['id'];
				
			} else { // Create a new one if necessary						
			
				$newTaxoi18n = array();
				$newTaxoi18n[$options['workingLanguage']] = array(
						"name" => $value['newName'],
						"description" => "",
						"helpText" => "",
						"locale" => $options['workingLanguage']
				);
				
				// translate vocabulary if terms are translated
				foreach ($options['importAsTaxoTranslation'] as $transKey => $transValue) {
					if ($transValue["translatedElement"] == $value['csvIndex']) {
						$newTaxoLang=$transValue["translateToLanguage"];
						$newTaxoi18n[$newTaxoLang] = array(
								"name" => $value['newName'],
								"description" => "",
								"helpText" => "",
								"locale" => $newTaxoLang
						);
					}
				}
				$newTaxoParams = array(
						"name" => $value['newName'],
						"description" => "",
						"helpText" => "",
						"expandable" => false,
						"multiSelect" => true,
						"mandatory" => $value['mandatory'],
						"nativeLanguage" => $options['workingLanguage'],
						"i18n" => $newTaxoi18n
				);
				$newTaxo = $taxonomyService->create($newTaxoParams);
				$newTaxos[]= $newTaxo['data']['id'];
				
			}
		}
		return $newTaxos;

    }
    
    public function analyseAction ()
    {
        $separator = $this->params()->fromPost('separator', ";");
        $userEncoding = $this->params()->fromPost('encoding');
        $returnArray = array();
        $fileInfos = $this->params()->fromFiles('csvFile');
        if (! isset($fileInfos)) {
            $returnArray['success'] = false;
            $returnArray['message'] = "Pas de fichier reçu.";
        } else {
            $mimeType = mime_content_type($fileInfos['tmp_name']);
            $contentType = isset($mimeType) ? $mimeType : $fileInfos['type'];
            
            
            if (($contentType != "text/plain") && ($contentType!= "text/csv")) {
                $returnArray['success'] = false;
                $returnArray['message'] = "Le fichier doit doit être au format CSV.";
            } else {
                // Load csv
                $recievedFile = fopen($fileInfos['tmp_name'], 'r');
                
                // Get first line
                $csvColumns = fgetcsv($recievedFile, 1000000, $separator, '"', '\\');
                
                // get the encoding of the line
                $stringCsvColumns = implode($separator, $csvColumns);
                $encoding = $this->getEncoding($stringCsvColumns);
                
                // Overwrite default encoding if it is specified
                if (isset($userEncoding)) {
                    $encoding["defaultEncoding"] = $userEncoding;
                }
                
                // Encode fields
                if (isset($encoding["defaultEncoding"])) {
                    foreach ($csvColumns as $key => $string) {
                        $utf8String = $this->forceUtf8($string, $encoding["defaultEncoding"]);
                        $csvColumns[$key] = $utf8String;
                    }
                }
                
                // Get the number of lines
                $lineCounter = 0;
                while (fgets($recievedFile) !== false) {
                    $lineCounter ++;
                }
                
                // Close csv
                fclose($recievedFile);
                
                // Build response
                $returnArray['encoding'] = $encoding;
                $returnArray['detectedFields'] = array();
                $returnArray['detectedFieldsCount'] = count($csvColumns);
                $returnArray['detectedContentsCount'] = $lineCounter;
                foreach ($csvColumns as $index => $column) {
                    $intermed = array();
                    $intermed['name'] = $column;
                    $intermed['csvIndex'] = $index;
                    $returnArray['detectedFields'][] = $intermed;
                }
                $returnArray['success'] = true;
                $returnArray['message'] = "OK";
            }
        }
        
        
        if (! $returnArray['success']) {
            $this->getResponse()->setStatusCode(500);
        }
        return new JsonModel($returnArray);
    }


	public function importAction ()
	{
		set_time_limit(5000);
		$options = array();
		$options['separator'] = $this->params()->fromPost('separator', ";");
		$options['userEncoding'] = $this->params()->fromPost('encoding');
		$options['workingLanguage'] = $this->params()->fromPost('workingLanguage', 'en');
		$options['importKey'] = (string) new \MongoId();
		//$options['importMode'] = $this->params()->fromPost('importMode', 'insert');
		$options['importMode'] = "insert";
		$options['typeId'] = isset($configs['contentTypeId']) ? $configs['contentTypeId'] : null;
		
		if (! isset($options['userEncoding'])) {
			throw new \Rubedo\Exceptions\Server("Missing parameter encoding", "Exception96", "encoding");
		}
		
		$returnArray = array();
		$brokenLines = array();
	
		$fileInfos = $this->params()->fromFiles('csvFile');
		if (! isset($fileInfos)) {
			$returnArray['success'] = false;
			$returnArray['message'] = "Pas de fichier reçu.";
		} else {
			$mimeType = mime_content_type($fileInfos['tmp_name']);
			$contentType = isset($mimeType) ? $mimeType : $fileInfos['type'];
	
			if (($contentType != "text/plain") && ($contentType!= "text/csv")) {
				$returnArray['success'] = false;
				$returnArray['message'] = "Le fichier doit doit être au format CSV.";
			} else {
				// receive params
				$configs = Json::decode($this->params()->fromPost('configs', "[ ]"), Json::TYPE_ARRAY);
							
				// Get general params
				$options['isProduct'] = isset($configs['isProduct']) ? $configs['isProduct'] : false;
				$options['vocabularies'] = array();

				// Params for insert mode
				if ($options['importMode'] == "insert") {
					$options['importAsField'] = Json::decode($this->params()->fromPost('inportAsField', "[ ]"), Json::TYPE_ARRAY);
					$options['importAsFieldTranslation'] = Json::decode($this->params()->fromPost('inportAsFieldTranslation', "[ ]"), Json::TYPE_ARRAY);
					$options['importAsTaxo'] = Json::decode($this->params()->fromPost('inportAsTaxo', "[ ]"), Json::TYPE_ARRAY);
					$options['importAsTaxoTranslation'] = Json::decode($this->params()->fromPost('inportAsTaxoTranslation', "[ ]"), Json::TYPE_ARRAY);
					$options['contentsNavTaxo'] = isset($configs['ContentsNavTaxo']) ? $configs['ContentsNavTaxo'] : "";
					$options['contentsTarget'] = isset($configs['ContentsTarget']) ? $configs['ContentsTarget'] : "";
				}
				
				// Add configs
				$options = array_merge($options,$configs);
				
				// INSERT MODE : create vocabularies and content type
				if ($options['importMode'] == 'insert') {
	
					// Create or update vocabularies
					$options['vocabularies'] = $this->createTaxonomy ($options);

					// create content type if needed
					if (is_null($options['typeId'])) {
						$options['typeId'] = $this->createContentType($options);
					}
					
				} else { // Update mode, populate importAsFields
					
					$contentType = Manager::getService("ContentTypes")->findById($options['typeId']);
					$options['importAsField'] = array();
					foreach ($contentType['fields'] as $field) {
						$fieldName = $field['config']['name'];
						if (isset($options[$fieldName]) && is_numeric($options[$fieldName])) {
							$field['config']['csvIndex'] = $options[$fieldName];
							$field['config']['newName'] = $fieldName;
							$field['config']['protoId'] = $field['protoId'];
							$field['config']['cType'] = $field['cType'];
							$options['importAsField'][] = $field['config'];
						}
					}
					
				}

				// Run Import
				$ImportService = Manager::getService('Import');
				$lineCounter=$ImportService->run($fileInfos['tmp_name'], $options);
				
				// Indexing
				$ElasticDataIndexService = Manager::getService('ElasticDataIndex');
				$ElasticDataIndexService->init();
				$ElasticDataIndexService->indexByType('content', $options['typeId']);
	
				// Return result
				$returnArray['importedContentsCount'] = $lineCounter;
				$returnArray['success'] = true;
				$returnArray['message'] = "OK";
				$returnArray['errors'] = $brokenLines;
			}
			
		}
	
		if (! $returnArray['success']) {
			$this->getResponse()->setStatusCode(500);
		}
		$content = Json::encode($returnArray);
		$response = $this->getResponse();
		$headers = $response->getHeaders();
		$headers->addHeaderLine('Content-Type', 'text/html');
		$response->setContent($content);
		return $response;
	}
}