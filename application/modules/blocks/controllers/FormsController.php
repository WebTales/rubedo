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
	protected $_hasError = false;
	protected $_formId;
	protected $_form;
	protected $_errors=array();
	protected $_lastAnsweredPage;
	protected $_send=true;
	
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
    		$this->_lastAnsweredPage=$this->formsSessionArray[$this->_formId]['currentFormPage'];
    		foreach($this->_form["formPages"][$currentFormPage]["elements"] as $field)
    		{
    			if($field['itemConfig']['fType']=='richText'){
    				continue;
    			}
    			$this->_validInput($field, $this->getParam($field['id']));
    		
    		}
    		if(empty($this->_errors)){
    			$this->_hasError=false;
    			$this->formsSessionArray[$this->_formId]['currentFormPage'] ++;
    			Manager::getService('Session')->set("forms",$this->formsSessionArray);
    		}else{
    		$this->_hasError=true;}
    	}
    	//Si on demande la page précédente
    	if(!$this->getRequest()->isPost() && $this->getParam("getPrevious")==1)
    	{
    		$this->formsSessionArray[$this->_formId]['currentFormPage']=$this->_formResponse["lastAnsweredPage"];;
    		Manager::getService('Session')->set("forms",$this->formsSessionArray);
    	}
    	
    	if($this->_hasError){
    		$output['values'] = $this->getAllParams();
    		$output['errors'] = $this->_errors;
    		$this->_computeNewPage();
    	}else{
    		$this->_updateResponse();
    		$this->_computeNewPage();
    	}
    	
    	//pass fields to the form template
    	$output["form"]["id"]=$this->_formId;
    	$output["nbFormPages"]=count($this->_form["formPages"]);
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
    /*
     * @todo finishAction
     */
    public function finishAction(){
    	die("joué");
    	/*$this->_formResponse["status"]="finished";
    	$result=Manager::getService('FormsResponses')->update($this->_formResponse);
    	if($result['success']){
    		$this->_formResponse = $result['data'];
    		$this->formsSessionArray[$this->_formId]['id'] = $this->_formResponse['id'];
    		$this->formsSessionArray[$this->_formId]['currentPage'] = 0;
    		Manager::getService('Session')->set("forms",$this->formsSessionArray);
    	}*/
    	//Ferme le formulaire et renvois a une page de remerciement
    }
    
    private function _validInput($field,$response)
    {
    	$is_valid = true;
    	$validationRules=$field["itemConfig"]["fieldConfig"];
    	/*
    	 * Check if field is required
    	 */
    
    	if($validationRules["mandatory"]==true){
    		if(empty($response)||$response==""){
    			$is_valid=false;
    			$this->_errors[$field["id"]]="Ce champ est obligatoire";
    		}
    	}
    	/*
    	 * Check validation rules
    	 */
    	if(!empty($response))
    	{
    	if(isset($validationRules["vtype"]) && $is_valid == true){
    		switch($validationRules["vtype"])
    		{
    			case "alpha":
    				$is_valid=ctype_alpha($response)==true?true:false;
    				break;
    			case "alphanum":
    				$is_valid=ctype_alnum($response)==true?true:false;
    				break;
    			case "email":
    				$is_valid=preg_match('#^(([a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+\.?)*[a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+)@(([a-z0-9-_]+\.?)*[a-z0-9-_]+)\.[a-z]{2,}$#i',$response)==1?true:false;
    				break;
    			case "url":
    				$is_valid=preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i',$response)==1?true:false;
    				break;
    			default:
    				$is_valid=true;
    				break;
    		}
    		if($is_valid==false){
    		$this->_errors[$field["id"]]="Saisie incorrecte";}
    	}
    	/*
    	 * Check Other params
    	 */
    	if($is_valid){
    		if(isset($validationRules["minLength"]) && !empty($validationRules["minLength"])){
    			if(strlen($response)<$validationRules["minLength"]){
    				$is_valid=false;
    				$this->_errors[$field["id"]]="Minimum ".$validationRules["minLength"]." caractères";
    			}
    		}
    		if(isset($validationRules["maxLength"]) && !empty($validationRules["maxLength"])){
    			if(strlen($response)>$validationRules["maxLength"]){
    				$is_valid=false;
    				$this->_errors[$field["id"]]="Maximum ".$validationRules["maxLength"]." caractères";
    			}
    		}
    	}
    	if($is_valid)
    	{
    		if(isset($validationRules["minValue"]))
    		{
    			switch($field["itemConfig"]["fieldType"]){
    				case "numberfield":
    					if($validationRules["minValue"]>intval($response))
    					{
    						$is_valid=false;
    						$this->_errors[$field["id"]]="Valeur minimum ".$validationRules["minValue"];
    					}
    					break;
    				case "datefield":
    					if($validationRules["minValue"]>Manager::getService('Date')->convertToTimeStamp($response))
    					{
    						$is_valid=false;
    						$this->_errors[$field["id"]]="Valeur minimum ".Manager::getService('Date')->convertToYmd($validationRules["minValue"]);
    					}
    					break;
    				case "timefield":
    						if(Manager::getService('Date')->convertToTimeStamp($validationRules["minValue"])>Manager::getService('Date')->convertToTimeStamp($response))
    						{
    							$is_valid=false;
    							$this->_errors[$field["id"]]="Valeur minimum ".$validationRules["minValue"];
    						}
    						break;
    			}
    		}
    		if(isset($validationRules["maxValue"]))
    		{
    			switch($field["itemConfig"]["fieldType"]){
    				case "numberfield":
    					if($validationRules["maxValue"]<intval($response))
    					{
    						$is_valid=false;
    						$this->_errors[$field["id"]]="Valeur maximum ".$validationRules["maxValue"];
    					}
    					break;
    				case "datefield":
    					if($validationRules["maxValue"]<Manager::getService('Date')->convertToTimeStamp($response))
    					{
    						$is_valid=false;
    						$this->_errors[$field["id"]]="Valeur maximum ".Manager::getService('Date')->convertToYmd($validationRules["maxValue"]);
    					}
    					break;
    					case "timefield":
    						if(Manager::getService('Date')->convertToTimeStamp($validationRules["maxValue"])<Manager::getService('Date')->convertToTimeStamp($response))
    						{
    							$is_valid=false;
    							$this->_errors[$field["id"]]="Valeur maximum ".$validationRules["maxValue"];
    						}
    						break;
    			}
    		}
    	}
    	}
    	if($is_valid)
    	{
    		$this->_validatedFields[$field['id']]=$response;
    	}
    }

    
    protected function _updateResponse(){
    	//mise à jour du status de la réponse
    	$this->_formResponse["status"]="pending";
    	//$this->_formResponse["lastAnsweredPage"]=$this->formsSessionArray[$this->_formId]['currentFormPage'];
    	$this->_formResponse["lastAnsweredPage"]=$this->_lastAnsweredPage;
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
  
    	if($this->formsSessionArray[$this->_formId]['currentFormPage']>=count($this->_form["formPages"]))
    	{
    		
    		//$this->forward('finish',"forms");
    		//$this->_helper->redirector ('finish');
    		$this->formsSessionArray[$this->_formId]['currentFormPage']=0;
    		Manager::getService('Session')->set("forms",$this->formsSessionArray);
    		$this->forward('finish');
    	
    		
    	}
    	/*
    	 * Verifications des conditions
    	 */
    	
    	//sur la page
    	//Definit la page a verifier
   
    	$pageToCheck=$this->_form["formPages"][$this->formsSessionArray[$this->_formId]['currentFormPage']];
  
    	$checkFields=true;
    	//On regarde si elle a des conditions
    	if(!empty($pageToCheck["itemConfig"]["conditionals"]))
    	{
    		//pour chaques conditions
    		foreach($pageToCheck["itemConfig"]["conditionals"] as $condition)
    		{
    			//On fait selon l'opérateur donné
    			$conditionsArray=array();//On declare un tableau de conditions 
    			switch($condition["operator"])
    			{
    				case "=":
    					
    					$conditionsArray=$this->_checkCondition($condition);
    					if(in_array(false,$conditionsArray))
    					{
    						$this->formsSessionArray[$this->_formId]['currentFormPage']++;
    						Manager::getService('Session')->set("forms",$this->formsSessionArray);
    						$checkFields=false;
    						$this->forward('index');
    					    		
    					}
    					break;
    					case"!=":
    						$conditionsArray=$this->_checkCondition($condition);
    						if(in_array(true,$conditionsArray))
    						{
    							$this->formsSessionArray[$this->_formId]['currentFormPage']++;
    						Manager::getService('Session')->set("forms",$this->formsSessionArray);
    						$checkFields=false;
    						}
    						break;
    					
    			}
    		}
    	}
    	if($checkFields)
    	{
    		foreach($pageToCheck["elements"] as $key=>$field)
    		{
    			 
    			if(!empty($field["itemConfig"]["conditionals"]))
    			{
    				foreach($field["itemConfig"]["conditionals"] as $condition)
    				{
    		
    					$conditionsArray=array();
    					switch($condition["operator"])
    					{
    						case "=":
    							
    							$conditionsArray=$this->_checkCondition($condition);
    							
    							if(in_array(false,$conditionsArray))
    							{
    								$pageToCheck["elements"][$key]["itemConfig"]["hidden"]=true;
    									
    							}
    							break;
    						case"!=":
    							$conditionsArray=$this->_checkCondition($condition);
    							if(in_array(true,$conditionsArray))
    							{
    								$pageToCheck["elements"][$key]["itemConfig"]["hidden"]=true;
    							}
    							break;
    					}
    				}
    			}
    		}
    	}
    	//sur les champs (a faire que si celles de la page sont bonne)
    	$this->_form["formPages"][$this->formsSessionArray[$this->_formId]['currentFormPage']]=$pageToCheck;
    	Manager::getService('Session')->set("forms",$this->formsSessionArray);
    	
    		
    		
    }
    //End function
    protected function _checkCondition($condition)
    {
    	$returnArray=array();
    	if(is_array($condition["value"]))
    	{
    		if(is_array($condition["value"]["value"]))
    		{
    			foreach($condition["value"]["value"] as $value)
    			{
    				 
    				$returnArray[]=in_array($value,$this->_formResponse['data'][$condition["field"]]);
    				 
    			}
    		}elseif(is_string($condition["value"]["value"]))
    		{
    			if(is_array($this->_formResponse['data'][$condition["field"]]))
    			{
    				$returnArray[]=in_array($condition["value"]["value"],$this->_formResponse['data'][$condition["field"]]);
    				
    			}elseif(is_string($this->_formResponse['data'][$condition["field"]])){
    				
    				$returnArray[]=$condition["value"]["value"]==$this->_formResponse['data'][$condition["field"]]?true:false;}
    		}
    	}elseif(is_string($condition["value"]))
    	{
    		$type=null;
    		foreach($this->_form["formPages"] as $pages)
    		{
    			foreach($pages["elements"] as $item){
    				if($condition["field"]==$item['id'])
    				{
    					$type=$item["itemConfig"]["fieldType"];
    				}
    			
    			}
    		}
    		switch($type)
    		{
    			case "textfield":
    			case "textareafield":
    				$returnArray[]=strtolower($condition["value"])==strtolower($this->_formResponse['data'][$condition["field"]])?true:false;
    				break;
    			case "datefield":
    				$returnArray[]=$condition["value"]==Manager::getService('Date')->convertToTimeStamp($this->_formResponse['data'][$condition["field"]])?true:false;
    				break;
    			case "timefield":
    				$returnArray[]=Manager::getService('Date')->convertToTimeStamp($condition["value"])==Manager::getService('Date')->convertToTimeStamp($this->_formResponse['data'][$condition["field"]])?true:false;
    				break;
    			case "numberfield":
    				$returnArray[]=intval($condition["value"])==intval($this->_formResponse['data'][$condition["field"]])?true:false;
    				break;
    				
    		}
    		
    	}
    	return $returnArray;
    }

}
