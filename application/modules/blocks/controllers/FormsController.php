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

require_once ('AbstractController.php');

/**
 *
 * @author nduvollet
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_FormsController extends Blocks_AbstractController
{

	protected $_validatedFields = array();
	protected $_formResponse = null;
	protected $_hasError = true;
	protected $_formId;
	protected $_form;
	
	public function init(){
		parent::init();
		
		$blockConfig = $this->getParam('block-config', array());
		$this->_formId = $blockConfig["formId"];
		$this->_form = Manager::getService('Forms')->findById($this->_formId);
		 
		//Check if form already exist on current session
		$this->formsSessionArray = Manager::getService('Session')->get("forms",array()); //get forms from session
		if(isset($this->formsSessionArray[$this->_formId]) && isset($this->formsSessionArray[$this->_formId]['id'])){
			 $this->_formResponse = Manager::getService('FormsResponses')->findById($this->formsSessionArray[$this->_formId]['id']);
		}else{
			$this->getRequest()->setActionName('new');
		}
	}
	
    /**
     * Default Action
     */
    public function indexAction ()
    {
    	//recupération de paramètre éventuels de la page en cours
    	$currentFormPage=$this->formsSessionArray[$this->_formId]['currentFormPage'];
    	 
    	//traitement et vérification
    	if($this->getRequest()->isPost()){
    		/*Verification des champs envoyés*/
    		foreach($this->_form["formPages"][$currentFormPage]["elements"] as $field)
    		{
    			if($field['itemConfig']['fType']=='richText'){
    				continue;
    			}
    			$this->_validInput($field, $this->getParam($field['id']));
    		}
    	}
    	
    	if($this->_hasError){
    		$output['values'] = $this->getAllParams();
    		$output['errors'] = array();
    	}else{
    		//stockage eventuel
    		$this->_updateResponse();
    		
    		//mise à jour de la page à afficher
    		$this->_computeNewPage();
    	}
    	
    	//pass fields to the form template
    	$output['formFields'] = $this->_form["formPages"][$this->formsSessionArray[$this->_formId]['currentFormPage']];

    	//affichage de la page
    	$output['currentFormPage'] = $this->formsSessionArray[$this->_formId]['currentFormPage'];
    	
    	$template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/form.html.twig");
    	$css = array();
    	$js = array();
    	$this->_sendResponse($output, $template, $css, $js);
    }
    
    
    public function newAction(){
    	$this->formsSessionArray[$this->_formId] = array('status'=>'new');
    	$this->_formResponse = array('status'=>'new');
    	$result = Manager::getService('FormsResponses')->create($this->_formResponse);
    	if($result['success']){
    		$this->_formResponse = $result['data'];
    		$this->formsSessionArray[$this->_formId]['id'] = $this->_formResponse['id'];
    		$this->formsSessionArray[$this->_formId]['currentFormPage'] = 0;
    		Manager::getService('Session')->set("forms",$this->formsSessionArray);
    		$this->forward('index');
    	}    	
    }
    
    public function finishAction(){
    	
    $this->_formResponse["status"]="finished";
    	$result=Manager::getService('FormsResponses')->update($this->_formResponse);
    	if($result['success']){
    		$this->_formResponse = $result['data'];
    		$this->formsSessionArray[$this->_formId]['id'] = $this->_formResponse['id'];
    		$this->formsSessionArray[$this->_formId]['currentPage'] = 0;
    		Manager::getService('Session')->set("forms",$this->formsSessionArray);
    	}
    	//Ferme le formulaire et renvois a une page de remerciement
    }
    
    private function _validInput($field,$response)
    {
    	$this->_hasError = true;
		return;
    	if($field["itemConfig"]["fType"]!="richText")
    	{
	    	$validationRules=$field["itemConfig"]["fieldConfig"];
	    	if($validationRules["allowBlank"]==false){
	    		if(!empty($response)){
	    			$valid=true;
	    		}
	    		else{
	    			$valid=false;
	    		}
	    	}else{$valid=true;}
	    	if(isset($validationRules["vtype"]) && $valid==true){
		    	switch($validationRules["vtype"])
		    	{
		    		case "alpha":
		    			$valid=ctype_alpha($response);
		    			break;
		    		case "alphanum":
		    			$valid=ctype_alnum($response);
		    			break;
		    		case "email":
		    			$valid=preg_match('#^(([a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+\.?)*[a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+)@(([a-z0-9-_]+\.?)*[a-z0-9-_]+)\.[a-z]{2,}$#i',$response)==1?true:false;
		    			break;
		    		case "url":
		    			$valid=preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i',$response)==1?true:false;
		    			break;
		    		default:
		    			$valid=true;
		    			break;
		    	}
	    	}
	    	if($valid==true){
	    		if(isset($validationRules["minLenght"])){
	    			if($response.length<$validationRules["minLenght"]){
	    				$valid=false;
	    			}
	    		}
	    		if(isset($validationRules["maxLenght"])){
	    			if($response.length>$validationRules["maxLenght"]){
	    				$valid=false;
	    			}
	    		}
	    	}
    	}
    	return $valid;
    }

    
    protected function _updateResponse(){
    	//mise à jour du status de la réponse
    	$this->_formResponse["status"]="pending";
    	$this->_formResponse["lastAnsweredPage"]=$this->formsSessionArray[$this->_formId]['currentFormPage'];
    	
    	if(!isset($this->_formResponse['data'])){
    		$this->_formResponse['data'] = array();
    	}
    	foreach ($this->_validatedFields as $key => $value){
    		$this->_formResponse['data'][$key]=$value;
    	}
    	$result=Manager::getService('FormsResponses')->update($this->_formResponse);
    	if(!$result['success']){
    		throw new Rubedo\Exceptions\Server('Impossible de mettre à jour la réponse.');
    	}
    }
    
    protected function _computeNewPage(){
    	$this->formsSessionArray[$this->_formId]['currentFormPage'] = 0;
    	Manager::getService('Session')->set("forms",$this->formsSessionArray);
    }

}
