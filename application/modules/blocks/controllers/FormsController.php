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
	protected $_errors=array();
	protected $_pagesArray=array();
	
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
    		//Zend_Debug::dump($this->getAllParams());die();
    		/*Verification des champs envoyés*/
    		foreach($this->_form["formPages"][$currentFormPage]["elements"] as $field)
    		{
    			if($field['itemConfig']['fType']=='richText'){
    				continue;
    			}
    			$this->_validInput($field, $this->getParam($field['id']));
    		
    		}
    		if(empty($this->_errors)){$this->_hasError=false;
    		}else{$this->_hasError=true;}
    		if($this->_hasError==false){$this->formsSessionArray[$this->_formId]['currentFormPage'] ++;}
    	}
    	if($this->_hasError){
    		$output['values'] = $this->getAllParams();
    		$output['errors'] = $this->_errors;
    	}else{
    		//stockage eventuel
    		$this->_updateResponse();
    		
    		//mise à jour de la page à afficher
    		$this->_computeNewPage();
    	}
    	
    	//pass fields to the form template
    	$output["form"]["id"]=$this->_formId;
    	//$output['formFields'] = $this->_form["formPages"][$this->formsSessionArray[$this->_formId]['currentFormPage']];
    	//Zend_Debug::dump($this->_pagesArray);die();
    	$output['formFields'] = $this->_pagesArray[$this->formsSessionArray[$this->_formId]['currentFormPage']];
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
    		if(empty($response)){
    			$is_valid=false;
    			$this->_errors[$field["id"]]="Ce champ est obligatoire";
    			
    		}
    	}
    	/*
    	 * Check validation rules
    	 */
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
    	if($this->formsSessionArray[$this->_formId]['currentFormPage']>=count($this->_form["formPages"]))
    	{
    		$this->formsSessionArray[$this->_formId]['currentFormPage']=0;
    		Manager::getService('Session')->set("forms",$this->formsSessionArray);
    	}else{
    	$nextPage=$this->_form["formPages"][$this->formsSessionArray[$this->_formId]['currentFormPage']];
    	foreach($nextPage["elements"] as $key=>$field)
    	{
   
    		if(!empty($field["itemConfig"]["conditionals"]))
    		{
    			foreach($field["itemConfig"]["conditionals"] as $condition)
    			{
    				
    		
    				if($this->getParam($condition["field"])!=null)
    				{
    						$conditionsArray=array();
    						switch($condition["operator"])
    						{
    							case "=":
    								foreach($condition["value"]["value"] as $value)
    								{
    									$conditionsArray[]=in_array($value,$this->getParam($condition["field"]));
    									
    								}
    								if(in_array(false,$conditionsArray))
    								{
    									$nextPage["elements"][$key]["itemConfig"]["hidden"]=true;
    									
    								}
    								break;
    							case"!=":
    								foreach($condition["value"]["value"] as $value)
    								{
    									$conditionsArray[]=in_array($value,$this->getParam($condition["field"]));
    										
    								}
    								if(in_array(true,$conditionsArray))
    								{
    									$nextPage["elements"][$key]["itemConfig"]["hidden"]=true;
    								}
    								break;
    						}
    				}
    			}
    		}
    	}
    	/*
    	 * @todo look why page doesn't display look into pageArray and if the prog pass here
    	 */
    	//Zend_Debug::dump($nextPage);die();
    	$this->_pagesArray[$this->formsSessionArray[$this->_formId]['currentFormPage']]=$nextPage;
    	Manager::getService('Session')->set("forms",$this->formsSessionArray);
    	}
    		
    		
    }

}
