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

	public function init(){
		parent::init();
		$blockConfig = $this->getParam('block-config', array());
		$this->formId = $blockConfig["formId"];
		$this->form = Manager::getService('Forms')->findById($this->formId);
		 
		//Check if form already exist on current session
		$this->formsSessionArray = Manager::getService('Session')->get("forms",array()); //get forms from session
		if(isset($this->formsSessionArray[$this->formId]) && isset($this->formsSessionArray[$this->formId]['id'])){
			 $this->formResponse = Manager::getService('FormsResponses')->findById($this->formsSessionArray[$this->formId]['id']);
		}else{
			$this->getRequest()->setActionName('new');
		}
	}
	
    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
    	//$currentPage = $this->formResponse['currentPage'];
    	$currentPage=$this->formsSessionArray[$this->formId]['currentPage'];
    	//recupération de paramètre éventuels de la page en cours
    		//pas encore implémenté
    	//traitement et vérification
    	
    	    /*Verification des champs envoyés*/
    		foreach($this->form["formPages"][$currentPage-1]["elements"] as $field)
    		{
    			$this->_validInput($field, $response);
    		}
    	//mise à jour de la page à afficher
    		
    	//mise à jour du status de la réponse
 	
    	$this->formResponse["status"]="in progress";
    	$result=Manager::getService('FormsResponses')->update($this->formResponse);
    	if($result['success']){
    		$this->formResponse = $result['data'];
    		$this->formsSessionArray[$this->formId]['id'] = $this->formResponse['id'];
    		$this->formsSessionArray[$this->formId]['currentPage'] = 1;
    		Manager::getService('Session')->set("forms",$this->formsSessionArray);
    	}
    	//stockage eventuel
    	//affichage de la page
    	\Zend_Debug::dump($this->formResponse);die();
    	$output=array();
    	$template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/form.html.twig");
    	$css = array();
    	$js = array();
    	$this->_sendResponse($output, $template, $css, $js);
    }
    public function newAction(){
    	$this->formsSessionArray[$this->formId] = array('status'=>'new');
    	$this->formResponse = array('status'=>'new');
    	$result = Manager::getService('FormsResponses')->create($this->formResponse);
    	if($result['success']){
    		$this->formResponse = $result['data'];
    		$this->formsSessionArray[$this->formId]['id'] = $this->formResponse['id'];
    		$this->formsSessionArray[$this->formId]['currentPage'] = 1;
    		Manager::getService('Session')->set("forms",$this->formsSessionArray);
    		$this->forward('index');
    	}    	
    }

    public function formAction(){
    	
    }
    public function finishAction(){
    	
    $this->formResponse["status"]="finished";
    	$result=Manager::getService('FormsResponses')->update($this->formResponse);
    	if($result['success']){
    		$this->formResponse = $result['data'];
    		$this->formsSessionArray[$this->formId]['id'] = $this->formResponse['id'];
    		$this->formsSessionArray[$this->formId]['currentPage'] = 1;
    		Manager::getService('Session')->set("forms",$this->formsSessionArray);
    	}
    	//Ferme le formulaire et renvois a une page de remerciement
    }
    private function _validInput($field,$response)
    {
    	//Check TYPE (Response)
    	//Check Value (AllowBlank)
    	//Check Validation (REGEX)
    	$validationRules=$field["validations"];
    		/*
    		 * switch validation type
    		 * 	case'text':
    		 * check is_String($response);
    		 * break;
    		 */
    	$valid=true;
    	return $valid;
    }


}
