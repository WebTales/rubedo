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
/**
 * Controller providing data import for csv
 *
 *
 *
 *
 * @author jbourdin
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
        //Zend_Registry::set('Expects_Json', true); not yet ZF2
        
        set_time_limit(5000);
        $separator = $this->params()->fromPost('separator', ";");
        $userEncoding = $this->params()->fromPost('encoding');
        $workingLanguage = $this->params()->fromPost('workingLanguage', 'en');
        
        if (! isset($userEncoding)) {
            throw new \Rubedo\Exceptions\Server("Missing parameter encoding", "Exception96", "encoding");
        }
        
        $returnArray = array();
        $taxonomyService = Manager::getService('Taxonomy');
        $taxonomyTermsService = Manager::getService('TaxonomyTerms');
        $contentsService = Manager::getService('Contents');
        $damService = Manager::getService('Dam');
        $fileService = Manager::getService('Files');
        $languagesService =  Manager::getService('Languages');
        
        // get active locales for automatic dam translation
        $languagesService = Manager::getService('Languages');
        $activeLocales = $languagesService->getActiveLocales();
        
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
                $importAsField = Json::decode($this->params()->fromPost('inportAsField', "[ ]"), Json::TYPE_ARRAY);
                $importAsTaxo = Json::decode($this->params()->fromPost('inportAsTaxo', "[ ]"), Json::TYPE_ARRAY);
                $importAsFieldTranslation = Json::decode($this->params()->fromPost('inportAsFieldTranslation', "[ ]"), Json::TYPE_ARRAY);
                $importAsTaxoTranslation = Json::decode($this->params()->fromPost('inportAsTaxoTranslation', "[ ]"), Json::TYPE_ARRAY);
                
                // create vocabularies
                $newTaxos = array();
                $CTvocabularies = array();
                $CTvocabularies[] = "navigation";
                foreach ($importAsTaxo as $key => $value) {
                    $newTaxoi18n = array();
                    $newTaxoi18n[$workingLanguage] = array(
                        "name" => $value['newName'],
                        "description" => "",
                        "helpText" => "",
                        "locale" => $workingLanguage
                    );
                    $newTaxoParams = array(
                        "name" => $value['newName'],
                        "description" => "",
                        "helpText" => "",
                        "expandable" => false,
                        "multiSelect" => true,
                        "mandatory" => $value['mandatory'],
                        "nativeLanguage" => $workingLanguage,
                        "i18n" => $newTaxoi18n
                    );
                    $newTaxo = $taxonomyService->create($newTaxoParams);
                    $newTaxos[] = $newTaxo;
                    $CTvocabularies[] = $newTaxo['data']['id'];
                }
                // create CT fields array
                $CTfields = array();
                $textFieldIndex = 0;
                $summaryFieldIndex = null;
                foreach ($importAsField as $key => $value) {
                    if ($value['protoId'] == 'text') {
                        $textFieldIndex = $value['csvIndex'];
                    } else {
                        if ($value['protoId'] == 'summary') {
                            $summaryFieldIndex = $value['csvIndex'];
                        } else {
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
                            $CTfields[] = $newFieldForCT;
                        }
                    }
                }
                
                // create CT
                $newCTi18n = array();
                $newCTi18n[$workingLanguage] = array(
                    "type" => $configs['ContentTypeType']
                );
                $contentTypeParams = array(
                    "dependant" => false,
                    "dependantTypes" => array(),
                    "type" => $configs['ContentTypeType'],
                    "fields" => $CTfields,
                    "vocabularies" => $CTvocabularies,
                    "workspaces" => $configs['ContentTypeWorkspaces'],
                    "workflow" => $configs['ContentTypeWorkflow'],
                    "activateDisqus" => false,
                    "nativeLanguage" => $workingLanguage,
                    "i18n" => $newCTi18n
                );
                $contentType = Manager::getService('ContentTypes')->create($contentTypeParams);
                
                // add contents to CT and terms to vocabularies
                $recievedFile = fopen($fileInfos['tmp_name'], 'r');
                // Read the first line to start at the second line
                fgetcsv($recievedFile, 1000000, $separator, '"', '\\');
                
                $lineCounter = 0;
                
                while (($currentLine = fgetcsv($recievedFile, 1000000, $separator, '"', '\\')) !== false) {
                    // get the encoding of the line
                    $stringCsvColumns = implode($separator, $currentLine);
                    
                    // Encode fields
                    foreach ($currentLine as $key => $string) {
                        $utf8String = $this->forceUtf8($string, $userEncoding);
                        $currentLine[$key] = $utf8String;
                    }
                    
                    // create content fields
                    $contentParamsFields = array(
                        "text" => $currentLine[$textFieldIndex],
                        "summary" => ""
                    );
                    if ($summaryFieldIndex !== null) {
                        $contentParamsFields['summary'] = $currentLine[$summaryFieldIndex];
                    }
                    // create i18n for translated fields
                    $contenti18n = array();
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
                        $contenti18n[$value["translateToLanguage"]]["fields"][$fieldName] = $currentLine[$value["csvIndex"]];
                        if (!isset($languages[$value["translateToLanguage"]])) {
                            $languages[] = $value["translateToLanguage"];
                        }
                    }
                    
                    // Unset translation with empty text (title)
                    foreach ($languages as $lang) {
                        if (isset($contenti18n[$lang]["fields"]["text"]) && trim($contenti18n[$lang]["fields"]["text"]) == "") {
                            unset($contenti18n[$lang]);
                        }
                    }
                    
                    // create fields content
                    foreach ($importAsField as $key => $value) {
                        if (($value['protoId'] != 'text') && ($value['protoId'] != 'summary')) {
                            if ($value['cType'] == "localiserField") {
                                if (! empty($currentLine[$value['csvIndex']])) {
                                    $splitedLatLon = explode(",", $currentLine[$value['csvIndex']]);
                                    $lat = null;
                                    $lon = null;
                                    if (count($splitedLatLon) == 2) {
                                        $lat = $splitedLatLon[0];
                                        $lon = $splitedLatLon[1];
                                    } else {
                                        if (count($splitedLatLon) == 4) {
                                            $lat = (float) ($splitedLatLon[0] . '.' . $splitedLatLon[1]);
                                            $lon = (float) ($splitedLatLon[2] . '.' . $splitedLatLon[3]);
                                        }
                                    }
                                    if (($lat) && ($lon)) {
                                        $contentParamsFields['position'] = array(
                                            "address" => "",
                                            "altitude" => "",
                                            "lat" => $lat,
                                            "lon" => $lon,
                                            "location" => array(
                                                "type" => "Point",
                                                "coordinates" => array(
                                                    (float) $lon,
                                                    (float) $lat
                                                )
                                            )
                                        );
                                    }
                                }
                            } elseif ($value['cType'] == "ImagePickerField") {
                                if (! empty($currentLine[$value['csvIndex']])) {
                                    $splitedImages = explode(",", $currentLine[$value['csvIndex']]);
                                    foreach ($splitedImages as $imageUrl) {
                                        
                                        if ($imageUrl != "") {
                                            
                                            $info = pathinfo($imageUrl);
                                            
                                            // get mime type
                                            $mimeType = "image/" . $info['extension'];
                                            
                                            // search existing file on name
                                            $existingFile = $fileService->findByFileName($info['basename']);
                                            
                                            if (is_null($existingFile)) {
                                                
                                                // if no file found create asset in GridFS
                                                
                                                $c = new Zend_Http_Client();
                                                $c->setUri($imageUrl);
                                                $result = $c->request('GET');
                                                $img = $result->getBody();
                                                
                                                $fileObj = array(
                                                    'bytes' => $img,
                                                    'text' => $info['filename'],
                                                    'filename' => $info['basename'],
                                                    'Content-Type' => $mimeType,
                                                    'mainFileType' => 'Image'
                                                );
                                                
                                                $result = $fileService->createBinary($fileObj);
                                                if (! $result['success']) {
                                                    // TODO change exception
                                                    throw new \Rubedo\Exceptions\Server("The server cannot get image file.", "Exception95");
                                                }
                                                
                                                $fileId = $result['data']['id'];
                                                $newFile = true;
                                            } else {
                                                
                                                // if file is found get file id
                                                
                                                $fileId = (string) $existingFile->file['_id'];
                                                $newFile = false;
                                            }
                                            
                                            if (! $newFile) {
                                                // search DAM referencing File
                                                $existingDam = $damService->findByOriginalFileId($fileId);
                                                
                                                if (is_null($existingDam)) {
                                                    $newDam = true;
                                                } else {
                                                    $newDam = false;
                                                }
                                            } else {
                                                $newDam = true;
                                            }
                                            
                                            if ($newDam) {
                                                
                                                $typeId = $value['mediaTypeId'];
                                                
                                                $obj = array();
                                                $damDirectory = 'notFiled';
                                                $obj['directory'] = $damDirectory;
                                                $obj['typeId'] = $typeId;
                                                $obj['mainFileType'] = 'Image';
                                                $obj['fields'] = array();
                                                $obj['taxonomy'] = array();
                                                $obj['title'] = $info['filename'];
                                                $obj['fields']['title'] = $info['filename'];
                                                $obj['originalFileId'] = $fileId;
                                                $obj['Content-Type'] = $mimeType;
                                                $obj['nativeLanguage'] = $workingLanguage;
                                                $obj['i18n'] = array();
                                                $obj['i18n'][$workingLanguage] = array();
                                                $obj['i18n'][$workingLanguage]['fields'] = $obj['fields'];
                                                unset($obj['i18n'][$workingLanguage]['fields']['writeWorkspace']);
                                                unset($obj['i18n'][$workingLanguage]['fields']['target']);
                                                
                                                // Add i18n for all the other active languages
                                                foreach ($activeLocales as $locale) {
                                                    if ($locale != $workingLanguage) {
                                                        $obj['i18n'][$locale] = array();
                                                        $obj['i18n'][$locale]['fields'] = array("title" => $info["filename"]);  
                                                    }                                                  
                                                }
                                                
                                                $returnArray = $damService->create($obj);
                                                if (! $returnArray['success']) {
                                                    $this->getResponse()->setHttpResponseCode(500);
                                                } else {
                                                    
                                                    // add assets in content data
                                                    $contentParamsFields[$value['newName']] = $returnArray['data']['id'];
                                                }
                                            } else {
                                                $contentParamsFields[$value['newName']] = $existingDam['id'];
                                            }
                                        }
                                    }
                                }
                            } else {
                                $contentParamsFields[$value['newName']] = $currentLine[$value['csvIndex']];
                            }
                        }
                    }
                    // create content taxonomy
                    $contentParamsTaxonomy = array();
                    $contentParamsTaxonomy['navigation'] = isset($configs["ContentsNavTaxo"]) ? $configs["ContentsNavTaxo"] : null;
                    foreach ($importAsTaxo as $key => $value) {
                        $theTaxoId = $newTaxos[$key]['data']['id'];
                        $contentParamsTaxonomy[$theTaxoId] = array();
                        if (isset($currentLine[$value['csvIndex']])) {
                            $detectedTermText = $currentLine[$value['csvIndex']];
                            if (! empty($detectedTermText)) {
                                $termsList = explode(",", $detectedTermText);

                                foreach ($importAsTaxoTranslation as $transKey => $transValue) {
                                    if ($transValue["translatedElement"] == $value['csvIndex']) {
                                        $translationKey = $key;
                                        break;
                                    }
                                }
                                if (isset($translationKey)){
                                    $transLocale = $importAsTaxoTranslation[$translationKey]["translateToLanguage"];
                                    $translatedTermsList = explode(",", $currentLine[$importAsTaxoTranslation[$translationKey]["csvIndex"]]);
                                }

                                foreach ($termsList as $termsListKey => $term) {
                                    if ($term != "") {
                                        $theTerm = $taxonomyTermsService->findByVocabularyIdAndName($theTaxoId, $term);
                                        
                                        if ($theTerm == null) {
                                            $termI18n = array();
                                            $termI18n[$workingLanguage] = array(
                                                "text" => $term,
                                                "locale" => $workingLanguage
                                            );
                                            // Add translation if exists
                                            if (isset($translatedTermsList) && $translatedTermsList[$termsListKey] != "" && $transLocale != "") {
                                                if (! isset($termI18n[$transLocale]["locale"]))
                                                    $termI18n[$transLocale]["locale"] = $transLocale;
                                                $termI18n[$transLocale]["text"] = $translatedTermsList[$termsListKey];
                                            }
                                            $termParams = array(
                                                "text" => $term,
                                                "vocabularyId" => $theTaxoId,
                                                "parentId" => "root",
                                                "leaf" => true,
                                                "expandable" => false,
                                                "nativeLanguage" => $workingLanguage,
                                                "i18n" => $termI18n
                                            );
                                            $theTerm = $taxonomyTermsService->create($termParams);
                                        }
                                    }
                                    
                                    if (isset($theTerm['id'])) {
                                        $contentParamsTaxonomy[$theTaxoId][] = $theTerm['id'];
                                    } else 
                                        if (isset($theTerm['data']['id'])) {
                                            $contentParamsTaxonomy[$theTaxoId][] = $theTerm['data']['id'];
                                        }
                                    
                                }
                            }
                        }
                    }
                    // create content
                    
                    $contenti18n[$workingLanguage] = array(
                        "fields" => $contentParamsFields,
                        "locale" => $workingLanguage
                    );
                    $contentParams = array(
                        "online" => true,
                        "text" => $currentLine[$textFieldIndex],
                        "typeId" => $contentType['data']['id'],
                        "fields" => $contentParamsFields,
                        "status" => "published",
                        "taxonomy" => $contentParamsTaxonomy,
                        "target" => $configs['ContentsTarget'],
                        "writeWorkspace" => $configs['ContentsWriteWorkspace'],
                        "startPublicationDate" => "",
                        "endPublicationDate" => "",
                        "pageId" => "",
                        "maskId" => "",
                        "blockId" => "",
                        "readOnly" => false,
                        "nativeLanguage" => $workingLanguage,
                        "i18n" => $contenti18n
                    );
                    try {
                        $contentsService->create($contentParams, array(), false, true);
                        $lineCounter ++;
                    } catch (Exception $e) {}
                }
                fclose($recievedFile);
                $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
                $ElasticDataIndexService->init();
                
                $ElasticDataIndexService->indexByType('content', $contentType['data']['id']);
                
                $returnArray['importedContentsCount'] = $lineCounter;
                $returnArray['success'] = true;
                $returnArray['message'] = "OK";
                $returnArray['errors'] = $brokenLines;
            }
        }
        
        if (! $returnArray['success']) {
            $this->getResponse()->setStatusCode(500);
        }
        return new JsonModel($returnArray);
    }
}
