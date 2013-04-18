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

Use Rubedo\Services\Manager;

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
   

    public function analyseAction ()
    {
    	$separator = $this->getParam('separator', ";");
    	$adapter = new Zend_File_Transfer_Adapter_Http();
    	$returnArray = array();
    	
    	if (! $adapter->receive("csvFile")) {
    		$returnArray['success']=false;
        	$returnArray['message']="Pas de fichier reçu.";
    	} else {
    		$filesArray = $adapter->getFileInfo();    		
    		$fileInfos = $filesArray["csvFile"];
    		if (($fileInfos['type']!="text/plain")&&($fileInfos['type']!="text/csv")){
    			$returnArray['success']=false;
    			$returnArray['message']="Le fichier doit doit être au format CSV.";
    		} else {
    			$recievedFile=fopen($fileInfos['tmp_name'],'r');
    			$csvColumns=fgetcsv($recievedFile,10000, $separator,'"','\\');   
    			$lineCounter=0;		
    			while (fgets($recievedFile) !== false) $lineCounter++;
    			fclose($recievedFile);		       	
		        $returnArray['detectedFields']=array();		
		        $returnArray['detectedFieldsCount']=count($csvColumns);
		        $returnArray['detectedContentsCount']=$lineCounter;		        
		        foreach ($csvColumns as $index => $column){
		        	$intermed=array();
		        	$intermed['name']=$column;
		        	$intermed['csvIndex']=$index;
		        	$returnArray['detectedFields'][]=$intermed;
		        }
		        $returnArray['success']=true;
		        $returnArray['message']="OK";
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
    
    public function importAction ()
    {
    $separator = $this->getParam('separator', ";");
    	$adapter = new Zend_File_Transfer_Adapter_Http();
    	$returnArray = array();
    	
    	if (! $adapter->receive("csvFile")) {
    		$returnArray['success']=false;
        	$returnArray['message']="Pas de fichier reçu.";
    	} else {
    		$filesArray = $adapter->getFileInfo();    		
    		$fileInfos = $filesArray["csvFile"];
    		if (($fileInfos['type']!="text/plain")&&($fileInfos['type']!="text/csv")){
    			$returnArray['success']=false;
    			$returnArray['message']="Le fichier doit doit être au format CSV.";
    		} else {
    			//recieve params
    			$configs=Zend_Json::decode($this->getParam('configs',"[ ]"));
    			$importAsField=Zend_Json::decode($this->getParam('inportAsField',"[ ]"));
    			$importAsTaxo=Zend_Json::decode($this->getParam('inportAsTaxo',"[ ]"));

    			
    			//create vocabularies
    			$newTaxos=array();
    			$CTvocabularies=array();
    			$CTvocabularies[]="navigation";
    			foreach ($importAsTaxo as $key => $value){
    				$newTaxoParams= array(
			             "name"=>$value['newName'],
			             "description"=>"",
			             "helpText"=>"",
			             "expandable"=>false,
			             "multiSelect"=>true,
			             "mandatory"=>$value['mandatory']			
			       );
    				$newTaxo=Rubedo\Services\Manager::getService('Taxonomy')->create($newTaxoParams);
    				$newTaxos[]=$newTaxo;
    				$CTvocabularies[]=$newTaxo['data']['id'];
    			}
    			// create CT fields array
    			$CTfields=array();
    			foreach ($importAsField as $key => $value){
    				$newFieldForCT=array(
                           "cType" => $value['cType'],
                           "config" => array(
                                  "name" => $value['newName'],
                                  "fieldLabel" => $value['label'],
                                  "allowBlank" => true,
                                  "localizable" => false,
                                  "searchable" => true,
                                  "multivalued" => false,
                                  "tooltip" => "",
                                  "labelSeparator" => " "
                           ),
                           "protoId" => $value['protoId'],
                           "openWindow" => null
                    );
    				$CTfields[]=$newFieldForCT;
    			}
    			
    			//create CT
    			$contentTypeParams = array(
    					"dependant" => false,
    					"dependantTypes" => array(),
    					"type" => $configs['ContentTypeType'],
    					"fields"=>$CTfields,
    					"vocabularies" => $CTvocabularies,
    					"workspaces" => $configs['ContentTypeWorkspaces'],
    					"workflow" => $configs['ContentTypeWorkflow'],
    					"activateDisqus" => false
    			
    			);
    			$contentType=Rubedo\Services\Manager::getService('ContentTypes')->create($contentTypeParams);
    			
    			//add contents to CT and terms to vocabularies	       		        	        
		        $returnArray['importedContentsCount']=0;
		        $returnArray['success']=true;
		        $returnArray['message']="OK";
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
