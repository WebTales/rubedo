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
Use Rubedo\Services\Manager;

require_once('DataAccessController.php');
 
/**
 * Controller providing CRUD API for the Forms JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_FormsController extends Backoffice_DataAccessController
{
    /**
     * Array with the read only actions
     */
    protected $_readOnlyAction = array('index', 'find-one', 'read-child', 'tree','model','get-csv');
    
    public function init(){
		parent::init();
		
		// init the data access service
		$this -> _dataService = Rubedo\Services\Manager::getService('Forms');
	}
	
	public function getCsvAction ()
	{
	    $formId = $this->getParam('form-id');
	    if(!$formId){
	        throw new Rubedo\Exceptions\User('pas de formulaire en argument');
	    }
	    $displayQnb = $this->getParam('display-qnb',false);
        $fileName = 'resultat_form_' . $formId . '_' . date('Ymd') . '.csv';
	    $filePath = sys_get_temp_dir() . '/' . $fileName;
	    $csvResource = fopen($filePath, 'w+');
	
	    $form = Manager::getService('Forms')->findById($formId);
	   
	
	    $fieldsArray = array();
	
	    $responsePages = array();
	    $headerArray = array();
	    $definiedAnswersArray = array();
	    
	    foreach ($form['formPages'] as $page){
	        foreach ($page['elements'] as $element){
	            switch ($element['itemConfig']['fType']){
	                case 'multiChoiceQuestion':
	                    if($element['itemConfig']['fieldType']=='checkboxgroup'){
	                        $tempSubField = array();
	                        foreach ($element['itemConfig']['fieldConfig']['items'] as $item){
	                            $headerArray[]=($displayQnb?$element['itemConfig']["qNb"].' - ':'').$element['itemConfig']["label"].' - '.$item['boxLabel'];
	                            $tempSubField[] = $item['inputValue'];
	                            $definiedAnswersArray[$item['inputValue']]=$item['boxLabel'];
	                        }
	                        $fieldsArray[]=array('type'=>'qcm','value'=>array('id'=>$element['id'],'items' => $tempSubField));
	                        break;
	                    }else{
	                        $headerArray[]=($displayQnb?$element['itemConfig']["qNb"].' - ':'').$element['itemConfig']["label"];
	                        $fieldsArray[]=array('type'=>'simple','value'=>$element['id']);
	                        foreach ($element['itemConfig']['fieldConfig']['items'] as $item){
                                $definiedAnswersArray[$item['inputValue']]=$item['boxLabel'];
	                        }
	                        break;
	                    }
	                case 'openQuestion':
	                    $headerArray[]=($displayQnb?$element['itemConfig']["qNb"].' - ':'').$element['itemConfig']["label"];
	                    $fieldsArray[]=array('type'=>'open','value'=>$element['id']);
	                    break;
	                default:
	                    break;
	            }
	        }
	    }
	
	    $list = Manager::getService('FormsResponses')->getValidResponsesByFormId ($formId);
	    
	

	    fputcsv($csvResource, $headerArray, ';');
	
	    foreach ($list['data'] as $response) {
	        $csvLine = array();
            foreach ($fieldsArray as $element) {
                switch ($element['type']) {
                    case 'open':
                        $csvLine[] = $response['data'][$element['value']];
                        break;
                    case 'simple':
                        $result = array_pop($response['data'][$element['value']]);
                        $csvLine[] = $definiedAnswersArray[$result];
                        break;
                    case 'qcm':
                            foreach ($element['value']['items'] as $item){
                                $csvLine[]=in_array($item, $response['data'][$element['value']['id']]);
                            }
                            break;
                    default:
                        break;
                }
            }
	        

	        fputcsv($csvResource, $csvLine, ';');
	    }
	    $this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	
	    $this->getResponse()->clearBody();
	    $this->getResponse()->clearHeaders();
	    $this->getResponse()->setHeader('Content-Type', 'application/csv');
	    $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
	    $this->getResponse()->sendHeaders();
	
	    fclose($csvResource);
	
	    $content = file_get_contents($filePath);
	    echo utf8_decode($content);
	    die();
	}

}