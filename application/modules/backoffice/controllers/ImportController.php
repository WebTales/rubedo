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
require_once ('DataAccessController.php');

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
class Backoffice_ImportController extends Backoffice_DataAccessController
{

    /**
     * Array with the read only actions
     */
    protected $_readOnlyAction = array();
    
    /**
     * Check if the given string is encoded in UTF-8 and encode it if it is not the case
     * 
     * @param string $string Contains the string which will be encoded
     * @return string UTF-8 string
     */
    protected function checkEncoding($string) {
        //Get current encoding
        $encoding = mb_detect_encoding($string, null, true);
        
        //Encode string in UTF-8
        if($encoding == false || ($encoding != "UTF-8" && $encoding != "ASCII")) {
            //If we don't know the encoding of the string, we let the function detecting it
            if($encoding != false) {
                $string = mb_convert_encoding($string, "UTF-8", $encoding);
            } else {
                $string = mb_convert_encoding($string, "UTF-8");
            }
            
            //Get the new encoding to check if it's the good one
            $newEncoding = mb_detect_encoding($string, null, true);
            
            //If the string is not in UTF-8, we throw an exception
            if($newEncoding != "UTF-8" && $newEncoding != "ASCII") {
                throw new \Rubedo\Exceptions\Server("Failed to encode in UTF-8, current encoding is ".mb_detect_encoding($string, null, true));
            }
        }
        
        return $string;
    }
    
    public function analyseAction ()
    {
        $separator = $this->getParam('separator', ";");
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $returnArray = array();
        
        if (! $adapter->receive("csvFile")) {
            $returnArray['success'] = false;
            $returnArray['message'] = "Pas de fichier reçu.";
        } else {
            $filesArray = $adapter->getFileInfo();
            $fileInfos = $filesArray["csvFile"];
            if (($fileInfos['type'] != "text/plain") && ($fileInfos['type'] != "text/csv")) {
                $returnArray['success'] = false;
                $returnArray['message'] = "Le fichier doit doit être au format CSV.";
            } else {
                //Load csv
                $recievedFile = fopen($fileInfos['tmp_name'], 'r');
                
                //Get first line
                $csvColumns = fgetcsv($recievedFile, 1000000, $separator, '"', '\\');
                
                //check the encoding of the line
                $stringCsvColumns = implode(";", $csvColumns);
                $stringCsvColumns = $this->checkEncoding($stringCsvColumns);
                
                $csvColumns = explode(";", $stringCsvColumns);
                
                //Get the number of lines
                $lineCounter = 0;
                while (fgets($recievedFile) !== false) {
                    $lineCounter ++;
                }
                
                //Close csv
                fclose($recievedFile);
                
                //Build response
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
        
        //Disable view
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        
        //Encode the response in json
        $returnValue = Zend_Json::encode($returnArray);
        if ($this->_prettyJson) {
            $returnValue = Zend_Json::prettyPrint($returnValue);
        }
        
        //Return the repsonse
        $this->getResponse()->setBody($returnValue);
    }

    public function importAction ()
    {
        Zend_Registry::set('Expects_Json', true);
        set_time_limit(5000);
        $separator = $this->getParam('separator', ";");
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $returnArray = array();
        $taxonomyService = Rubedo\Services\Manager::getService('Taxonomy');
        $taxonomyTermsService = Rubedo\Services\Manager::getService('TaxonomyTerms');
        $contentsService = Rubedo\Services\Manager::getService('Contents');
        $brokenLines = array();
        
        if (! $adapter->receive("csvFile")) {
            $returnArray['success'] = false;
            $returnArray['message'] = "Pas de fichier reçu.";
        } else {
            $filesArray = $adapter->getFileInfo();
            $fileInfos = $filesArray["csvFile"];
            if (($fileInfos['type'] != "text/plain") && ($fileInfos['type'] != "text/csv")) {
                $returnArray['success'] = false;
                $returnArray['message'] = "Le fichier doit doit être au format CSV.";
            } else {
                // recieve params
                $configs = Zend_Json::decode($this->getParam('configs', "[ ]"));
                $importAsField = Zend_Json::decode($this->getParam('inportAsField', "[ ]"));
                $importAsTaxo = Zend_Json::decode($this->getParam('inportAsTaxo', "[ ]"));
                
                // create vocabularies
                $newTaxos = array();
                $CTvocabularies = array();
                $CTvocabularies[] = "navigation";
                foreach ($importAsTaxo as $key => $value) {
                    $newTaxoParams = array(
                        "name" => $value['newName'],
                        "description" => "",
                        "helpText" => "",
                        "expandable" => false,
                        "multiSelect" => true,
                        "mandatory" => $value['mandatory']
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
                                    "localizable" => false,
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
                $contentTypeParams = array(
                    "dependant" => false,
                    "dependantTypes" => array(),
                    "type" => $configs['ContentTypeType'],
                    "fields" => $CTfields,
                    "vocabularies" => $CTvocabularies,
                    "workspaces" => $configs['ContentTypeWorkspaces'],
                    "workflow" => $configs['ContentTypeWorkflow'],
                    "activateDisqus" => false
                )
                ;
                $contentType = Rubedo\Services\Manager::getService('ContentTypes')->create($contentTypeParams);
                
                // add contents to CT and terms to vocabularies
                $recievedFile = fopen($fileInfos['tmp_name'], 'r');
                //fgetcsv($recievedFile, 1000000, $separator, '"', '\\'); -> useless
                $lineCounter = 0;
                $csvLine = 0;
                while (($currentLine = fgetcsv($recievedFile, 1000000, $separator, '"', '\\')) !== false) {
                    $csvLine ++;
                    
                    //check the encoding of the line
                    try {
                        $stringCsvColumns = implode(";", $currentLine);
                        $stringCsvColumns = $this->checkEncoding($stringCsvColumns);
                        
                        $currentLine = explode(";", $stringCsvColumns);
                    } catch (\Rubedo\Exceptions $error) {
                        $brokenLines[$csvLine] = $error;
                        continue;
                    }
                    
                    // add taxo terms if not already in correspondent vocabulary
                    // create content fields
                    $contentParamsFields = array(
                        "text" => $currentLine[$textFieldIndex],
                        "summary" => ""
                    );
                    if ($summaryFieldIndex !== null) {
                        $contentParamsFields['summary'] = $currentLine[$summaryFieldIndex];
                    }
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
                                                    $lon,
                                                    $lat
                                                )
                                            )
                                        );
                                    }
                                }
                            } else {
                                $contentParamsFields[$value['newName']] = $currentLine[$value['csvIndex']];
                            }
                        }
                    }
                    // create content taxo
                    $contentParamsTaxonomy = array();
                    foreach ($importAsTaxo as $key => $value) {
                        $theTaxoId = $newTaxos[$key]['data']['id'];
                        $contentParamsTaxonomy[$theTaxoId] = array();
                        if (isset($currentLine[$value['csvIndex']])) {
                            $detectedTermText = $currentLine[$value['csvIndex']];
                            if (! empty($detectedTermText)) {
                                $theTerm = $taxonomyTermsService->findByVocabularyIdAndName($theTaxoId, $detectedTermText);
                                if ($theTerm == null) {
                                    $termParams = array(
                                        "text" => $detectedTermText,
                                        "vocabularyId" => $theTaxoId,
                                        "parentId" => "root",
                                        "leaf" => true,
                                        "expandable" => false
                                    );
                                    $theTerm = $taxonomyTermsService->create($termParams);
                                }
                                if (isset($theTerm['id'])) {
                                    $contentParamsTaxonomy[$theTaxoId][] = $theTerm['id'];
                                }
                            }
                        }
                    }
                    // create content
                    $contentParams = array(
                        "online" => "true",
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
                        "readOnly" => false
                    );
                    try {
                        $contentsService->create($contentParams, array(), false, false);
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
        
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $returnValue = Zend_Json::encode($returnArray);
        if ($this->_prettyJson) {
            $returnValue = Zend_Json::prettyPrint($returnValue);
        }
        $this->getResponse()->setBody($returnValue);
    }
}
