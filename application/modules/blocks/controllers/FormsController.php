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

    protected $_errors = array();

    protected $_lastAnsweredPage;

    protected $_send = true;

    protected $_blockConfig;

    public function init ()
    {
        parent::init();
        
        $this->_blockConfig = $this->getParam('block-config', array());
        $this->_formId = $this->_blockConfig["formId"];
        $this->_form = Manager::getService('Forms')->findById($this->_formId);
        if (! $this->getRequest()->isPost() && $this->getParam("getNew") == 1) {
            if ($this->_form["uniqueAnswer"] == "false") {
                $this->_new();
                return;
            }
        }
        
        // Check if form already exist on current session
        $this->formsSessionArray = Manager::getService('Session')->get("forms", array());
        // get forms from session
        if (isset($this->formsSessionArray[$this->_formId]) && isset($this->formsSessionArray[$this->_formId]['id'])) {
            $this->_formResponse = Manager::getService('FormsResponses')->findById($this->formsSessionArray[$this->_formId]['id']);
        } else {
            $this->_new();
        }
    }

    /**
     * Default Action
     */
    public function indexAction ()
    {
        // recupération de paramètre éventuels de la page en cours
        $currentFormPage = $this->formsSessionArray[$this->_formId]['currentFormPage'];
        
        // traitement et vérification
        
        if ($this->getRequest()->isPost()) {
            /* Verification des champs envoyés */
            $this->_lastAnsweredPage = $this->formsSessionArray[$this->_formId]['currentFormPage'];
            foreach ($this->_form["formPages"][$currentFormPage]["elements"] as $field) {
                if ($field['itemConfig']['fType'] == 'richText') {
                    continue;
                }
                $this->_validInput($field, $this->getParam($field['id']));
            }
            foreach ($this->_form["formPages"][$currentFormPage]["elements"] as $field) {
                foreach ($field["itemConfig"]["conditionals"] as $condition) {
                    
                    switch ($condition["operator"]) {
                        case "=":
                            $conditionArray = $this->_checkCondition($condition);
                            if (in_array(false, $conditionArray))                             // si condition
                                                                  // pas remplie
                            {
                                unset($this->_errors[$field["id"]]);
                                unset($this->_formResponse["data"][$field["id"]]);
                            }
                            break;
                        case "≠":
                            $conditionArray = $this->_checkCondition($condition);
                            if (in_array(true, $conditionArray)) {
                                unset($this->_errors[$field["id"]]);
                                unset($this->_formResponse["data"][$field["id"]]);
                            }
                            break;
                    }
                }
            }
            if (empty($this->_errors)) {
                $this->_hasError = false;
                $this->formsSessionArray[$this->_formId]['currentFormPage'] ++;
                Manager::getService('Session')->set("forms", $this->formsSessionArray);
            } else {
                $this->_hasError = true;
            }
        }
        // Si on demande la page précédente
        if (! $this->getRequest()->isPost() && $this->getParam("getPrevious") == 1) {
            if (is_array($this->_formResponse["lastAnsweredPage"]) && count($this->_formResponse["lastAnsweredPage"]) > 0) {
                $pageToBeSet = array_pop($this->_formResponse["lastAnsweredPage"]);
            } else {
                $pageToBeSet = 0;
            }
            $this->_clearPageInDb($currentFormPage);
            $currentFormPage = $this->formsSessionArray[$this->_formId]['currentFormPage'] = $pageToBeSet;
            
            Manager::getService('Session')->set("forms", $this->formsSessionArray);
            $output['values'] = $this->_formResponse["data"];
        }
        if ($this->_hasError) {
            $output['values'] = $this->getAllParams();
            $output['errors'] = $this->_errors;
            $this->_computeNewPage();
        } else {
            $this->_updateResponse();
            $this->_computeNewPage();
        }
        
        // pass fields to the form template
        $output["form"]["id"] = $this->_formId;
        $output["nbFormPages"] = count($this->_form["formPages"]);
        $output['formFields'] = $this->_form["formPages"][$this->formsSessionArray[$this->_formId]['currentFormPage']];
        /*Zend_Debug::dump($this->_formResponse['data']);
        die("test");*/
        foreach ($output['formFields']["elements"] as $key => &$value){
            if ($value["itemConfig"]["fType"]=="predefinedPrefsQuestion"){
                $source1Value=$this->_formResponse['data'][$value["itemConfig"]["source1Id"]];
                $source2Value=$this->_formResponse['data'][$value["itemConfig"]["source2Id"]];
                $source2Value=(float) $source2Value;
                $expPlan=Zend_Json::decode($value["itemConfig"]["experiencePlan"]);
                $expPlanLength=count($expPlan)-1;
                $resultingOptions=array();
                $numberOfQuestions=$value["itemConfig"]["numberOfQuestions"];
                $numberOfOptions=$value["itemConfig"]["numberOfOptions"];
                $usedRows=array();
                for ($i = 1; $i <= $numberOfQuestions; $i++) {
                    $myRow=rand(0, $expPlanLength);
                    while (in_array($myRow, $usedRows)) {
                        $myRow=rand(0, $expPlanLength);
                    }
                    array_push($usedRows, $myRow);
                    $extractedRow=$expPlan[$myRow];
                    $currentOption=array();
                    
                    for ($j = 1; $j <= $numberOfOptions; $j++) {
                        $val1=DateTime::createFromFormat("G:i", $source1Value);
                        $augmentor=date_interval_create_from_date_string($extractedRow["option".$j."source1"]." hours");
                        $val1=$val1->add($augmentor);
                        $val1=$val1->format("G:i");
                        $val2=$source2Value*$extractedRow["option".$j."source2"];
                        $fullValue=$val1." et ".$val2." euros";
                        array_push($currentOption, array($val1,$val2,$fullValue));
                    }
                    array_push($resultingOptions, $currentOption);
                }                
                $value["itemConfig"]["resultingOptions"]=$resultingOptions;
                
                
            //specific implement of special field
            }
        }
        $output["displayNew"] = $this->_form["uniqueAnswer"] == "true" ? false : true;
        if ($this->_formResponse["status"] == "finished") {
            if ($this->_justFinished || $output["displayNew"]) {
                $output["finished"] = $this->_form["endMessage"];
            } else {
                $output["finished"] = $this->_form["uniqueAnswerText"];
            }
        }
        
        // affichage de la page
        $output['currentFormPage'] = $this->formsSessionArray[$this->_formId]['currentFormPage'];
        $output["progression"] = $this->_blockConfig["progression"];
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/form.html.twig");
        $css = array();
        $js = array(
            '/templates/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/forms.js")
        );
        $this->_sendResponse($output, $template, $css, $js);
    }

    protected function _new ()
    {
        $this->formsSessionArray[$this->_formId] = array(
            'status' => 'new'
        );
        $this->_formResponse = array(
            'status' => 'new',
            'formId' => $this->_formId
        );
        $result = Manager::getService('FormsResponses')->create($this->_formResponse);
        if ($result['success']) {
            $this->_formResponse = $result['data'];
            $this->formsSessionArray[$this->_formId]['id'] = $this->_formResponse['id'];
            $this->formsSessionArray[$this->_formId]['currentFormPage'] = 0;
            Manager::getService('Session')->set("forms", $this->formsSessionArray);
        }
    }
    /*
     * @todo finishAction
     */
    protected function _finish ()
    {
        if ($this->_formResponse["status"] != 'finished') {
            $this->_formResponse["status"] = "finished";
            $result = Manager::getService('FormsResponses')->update($this->_formResponse);
            if ($result['success']) {
                $this->_formResponse = $result['data'];
                $this->formsSessionArray[$this->_formId]['id'] = $this->_formResponse['id'];
                $this->formsSessionArray[$this->_formId]['currentPage'] = 0;
                Manager::getService('Session')->set("forms", $this->formsSessionArray);
            } else {
                throw new Rubedo\Exceptions\Server('Impossible to update the response.', "Exception17");
            }
            $this->_justFinished = true;
        } else {
            $this->_justFinished = false;
        }
        
        // Ferme le formulaire et renvois a une page de remerciement
    }

    protected function _validInput ($field, $response)
    {
        $is_valid = true;
        $validationRules = $field["itemConfig"]["fieldConfig"];
        /*
         * Check if field is required
         */
        if ($validationRules["mandatory"] == true) {
            if (empty($response) || $response == "") {
                $is_valid = false;
                $this->_errors[$field["id"]] = "Ce champ est obligatoire";
            }
        }
        
        /*
         * Check validation rules
         */
        $fieldType = $this->_getFieldType($field["id"]);
        // if response is not empty
        if (! empty($response)) {
            // check numberfield if value is numeric
            if ($fieldType == "numberfield") {
                
                $is_valid = is_numeric($response) == true ? true : false;
                if ($is_valid == false)
                    $this->_errors[$field["id"]] = "Ce champ ne doit contenir que des caractères numériques";
                else {
                    // if decimal is not allowed, check if response is decimal
                    if (! isset($field["itemConfig"]["fieldConfig"]["allowDecimals"]) || $field["itemConfig"]["fieldConfig"]["allowDecimals"] != "on") {
                        $is_valid = preg_match("/\\d\\.|,\\d/", $response) == 1 ? false : true;
                    }
                    if ($is_valid == false)
                        $this->_errors[$field["id"]] = "Les décimales ne sont pas autorisées";
                }
            }
            /*
             * check validation rules
             */
            if (isset($validationRules["vtype"]) && $is_valid == true) {
                switch ($validationRules["vtype"]) {
                    case "alpha":
                        $is_valid = ctype_alpha($response) == true ? true : false;
                        break;
                    case "alphanum":
                        $is_valid = ctype_alnum($response) == true ? true : false;
                        break;
                    case "email":
                        $is_valid = preg_match('#^(([a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+\.?)*[a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+)@(([a-z0-9-_]+\.?)*[a-z0-9-_]+)\.[a-z]{2,}$#i', $response) == 1 ? true : false;
                        break;
                    case "url":
                        $is_valid = preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $response) == 1 ? true : false;
                        break;
                    default:
                        $is_valid = true;
                        break;
                }
                if ($is_valid == false) {
                    $this->_errors[$field["id"]] = "Saisie incorrecte";
                }
            }
            /*
             * Check Other params
             */
            // MinLength and maxLength for textfields and textarea
            if ($is_valid) {
                if (isset($validationRules["minLength"]) && ! empty($validationRules["minLength"])) {
                    if (strlen($response) < $validationRules["minLength"]) {
                        $is_valid = false;
                        $this->_errors[$field["id"]] = "Minimum " . $validationRules["minLength"] . " caractères";
                    }
                }
                if (isset($validationRules["maxLength"]) && ! empty($validationRules["maxLength"])) {
                    if (strlen($response) > $validationRules["maxLength"]) {
                        $is_valid = false;
                        $this->_errors[$field["id"]] = "Maximum " . $validationRules["maxLength"] . " caractères";
                    }
                }
            }
            if ($is_valid) {
                // Min value and max value for other fields
                if (isset($validationRules["minValue"])) {
                    switch ($field["itemConfig"]["fieldType"]) {
                        case "numberfield":
                            if ($validationRules["minValue"] > intval($response)) {
                                $is_valid = false;
                                $this->_errors[$field["id"]] = "Valeur minimum " . $validationRules["minValue"];
                            }
                            break;
                        case "datefield":
                            if ($validationRules["minValue"] > Manager::getService('Date')->convertToTimeStamp($response)) {
                                $is_valid = false;
                                $this->_errors[$field["id"]] = "Valeur minimum " . Manager::getService('Date')->convertToYmd($validationRules["minValue"]);
                            }
                            break;
                        case "timefield":
                            if (Manager::getService('Date')->convertToTimeStamp($validationRules["minValue"]) > Manager::getService('Date')->convertToTimeStamp($response)) {
                                $is_valid = false;
                                $this->_errors[$field["id"]] = "Valeur minimum " . $validationRules["minValue"];
                            }
                            break;
                    }
                }
                if (isset($validationRules["maxValue"])) {
                    switch ($field["itemConfig"]["fieldType"]) {
                        case "numberfield":
                            if ($validationRules["maxValue"] < intval($response)) {
                                $is_valid = false;
                                $this->_errors[$field["id"]] = "Valeur maximum " . $validationRules["maxValue"];
                            }
                            break;
                        case "datefield":
                            if ($validationRules["maxValue"] < Manager::getService('Date')->convertToTimeStamp($response)) {
                                $is_valid = false;
                                $this->_errors[$field["id"]] = "Valeur maximum " . Manager::getService('Date')->convertToYmd($validationRules["maxValue"]);
                            }
                            break;
                        case "timefield":
                            if (Manager::getService('Date')->convertToTimeStamp($validationRules["maxValue"]) < Manager::getService('Date')->convertToTimeStamp($response)) {
                                $is_valid = false;
                                $this->_errors[$field["id"]] = "Valeur maximum " . $validationRules["maxValue"];
                            }
                            break;
                    }
                }
            }
        }
        if ($is_valid) {
            // if field is valid add to the response datas
            $this->_validatedFields[$field['id']] = $response;
            if (! isset($this->_formResponse['data'])) {
                $this->_formResponse['data'] = array();
            }
            $this->_formResponse['data'][$field['id']] = $response;
        }
    }

    protected function _clearPageInDb ($pageId)
    {
        foreach ($pageId["elements"] as $field) {
            foreach ($this->_formResponse["data"] as $key => $fieldOnDb) {
                unset($fieldOnDb);
                if ($field["id"] == $key) {
                    unset($this->_formResponse["data"][$key]);
                }
            }
        }
    }

    protected function _updateResponse ()
    {
        if ($this->_formResponse["status"] != 'finished' || $this->getParam("getNew") == 1) {
            
            // mise à jour du status de la réponse
            $this->_formResponse["status"] = "pending";
            // $this->_formResponse["lastAnsweredPage"]=$this->formsSessionArray[$this->_formId]['currentFormPage'];
            if (intval($this->formsSessionArray[$this->_formId]['currentFormPage']) > $this->_lastAnsweredPage && $this->_lastAnsweredPage > 0) {
                $this->_formResponse["lastAnsweredPage"][] = $this->_lastAnsweredPage;
                $this->_formResponse["lastAnsweredPage"] = array_unique($this->_formResponse["lastAnsweredPage"]);
            }
            $result = Manager::getService('FormsResponses')->update($this->_formResponse);
            if (! $result['success']) {
                throw new Rubedo\Exceptions\Server('Impossible to update the response.', "Exception17");
            } else {
                $this->_formResponse = $result['data'];
            }
        }
    }

    protected function _computeNewPage ()
    {
        if ($this->formsSessionArray[$this->_formId]['currentFormPage'] >= count($this->_form["formPages"])) {
            $this->_finish();
            /*
             * $this->formsSessionArray[$this->_formId]['currentFormPage']=0; Manager::getService('Session')->set("forms",$this->formsSessionArray);
             */
        }
        /*
         * Verifications des conditions
         */
        
        // recovery of the page to be processed
        $idToCheck = $this->formsSessionArray[$this->_formId]['currentFormPage'];
        if (isset($this->_form["formPages"][$idToCheck])) {
            $pageToCheck = $this->_form["formPages"][$idToCheck];
        } else {
            $pageToCheck = array(
                'elements' => array()
            );
        }
        
        $checkFields = true;
        // if page have conditionals
        if (! empty($pageToCheck["itemConfig"]["conditionals"])) {
            // for each conditional check if condition and check if the condition is fulfilled
            foreach ($pageToCheck["itemConfig"]["conditionals"] as $condition) {
                
                $conditionsArray = array();
                switch ($condition["operator"]) {
                    case "=":
                        $conditionsArray = $this->_checkCondition($condition);
                        if (in_array(false, $conditionsArray)) {
                            $this->formsSessionArray[$this->_formId]['currentFormPage'] ++;
                            Manager::getService('Session')->set("forms", $this->formsSessionArray);
                            $checkFields = false;
                            $this->_computeNewPage();
                            return;
                        }
                        break;
                    case "≠":
                        $conditionsArray = $this->_checkCondition($condition);
                        if (in_array(true, $conditionsArray)) {
                            $this->formsSessionArray[$this->_formId]['currentFormPage'] ++;
                            Manager::getService('Session')->set("forms", $this->formsSessionArray);
                            $checkFields = false;
                            $this->_computeNewPage();
                            return;
                        }
                        break;
                }
            }
        }
        // if page condition is fulfilled
        if ($checkFields) {
            // checks each field on the page and see if it has conditions
            foreach ($pageToCheck["elements"] as $key => $field) {
                
                if (! empty($field["itemConfig"]["conditionals"])) {
                    // for each field we see if it is a condition of another field
                    foreach ($field["itemConfig"]["conditionals"] as $condition) {
                        foreach ($pageToCheck["elements"] as $id => $item) {
                            if ($condition["field"] == $item["id"]) {
                                $pageToCheck["elements"][$id]["itemConfig"]["isMother"] = true;
                            }
                        }
                        $conditionsArray = array();
                        switch ($condition["operator"]) {
                            case "=":
                                $pageToCheck["elements"][$key]["itemConfig"]["isChild"] = true;
                                $pageToCheck["elements"][$key]["itemConfig"]["target"] = $condition["field"];
                                // check type of required value for condition
                                if (is_array($condition["value"])) {
                                    if (is_array($condition["value"]["value"])) {
                                        $dataValues = "";
                                        $dataValuesArray = array();
                                        foreach ($condition["value"]["value"] as $conditionnalValues) {
                                            $dataValuesArray[] = $conditionnalValues;
                                        }
                                        
                                        $dataValues = implode(';', $dataValuesArray);
                                        $pageToCheck["elements"][$key]["itemConfig"]["value"] = $dataValues;
                                    } elseif (is_string($condition["value"]["value"])) {
                                        
                                        $pageToCheck["elements"][$key]["itemConfig"]["value"] = $condition["value"]["value"];
                                    }
                                } elseif (is_string($condition["value"])) {
                                    
                                    $type = $this->_getFieldType($condition["field"]);
                                    
                                    if ($type == "datefield") {
                                        $dataValue = Manager::getService('Date')->convertToYmd($condition["value"]);
                                    } else {
                                        $dataValue = $condition["value"];
                                    }
                                    $pageToCheck["elements"][$key]["itemConfig"]["value"] = $dataValue;
                                }
                                
                                if (isset($this->_formResponse['data'][$condition["field"]])) {
                                    $conditionsArray = $this->_checkCondition($condition);
                                } else {
                                    $conditionsArray[] = false;
                                }
                                
                                if (! in_array(true, $conditionsArray)) {
                                    $pageToCheck["elements"][$key]["itemConfig"]["hidden"] = true;
                                }
                                break;
                            case "≠":
                                
                                $conditionsArray = $this->_checkCondition($condition);
                                if (in_array(true, $conditionsArray)) {
                                    $pageToCheck["elements"][$key]["itemConfig"]["hidden"] = true;
                                }
                                break;
                        }
                    }
                }
            }
        }
        $this->_form["formPages"][$this->formsSessionArray[$this->_formId]['currentFormPage']] = $pageToCheck;
        Manager::getService('Session')->set("forms", $this->formsSessionArray);
    }
    // End function
    protected function _checkCondition ($condition)
    {
        // check if condition is fulfilled
        $returnArray = array();
        
        if (! isset($this->_formResponse['data'][$condition["field"]]) || empty($this->_formResponse['data'][$condition["field"]])) {
            $returnArray[] = false;
        } else {
            // check type of required value for condition and check if condition is fulfilled
            if (is_array($condition["value"])) {
                if (is_array($condition["value"]["value"])) {
                    foreach ($condition["value"]["value"] as $value) {
                        if (is_array($this->_formResponse['data'][$condition["field"]])) {
                            foreach ($this->_formResponse['data'][$condition["field"]] as $response) {
                                
                                $returnArray[] = in_array($response, $condition["value"]["value"]);
                            }
                        } elseif (is_string($this->_formResponse['data'][$condition["field"]])) {
                            
                            $returnArray[] = in_array($value, $this->_formResponse['data'][$condition["field"]]);
                        }
                    }
                } elseif (is_string($condition["value"]["value"])) {
                    if (is_array($this->_formResponse['data'][$condition["field"]])) {
                        
                        $returnArray[] = in_array($condition["value"]["value"], $this->_formResponse['data'][$condition["field"]]);
                    } elseif (is_string($this->_formResponse['data'][$condition["field"]])) {
                        
                        $returnArray[] = $condition["value"]["value"] == $this->_formResponse['data'][$condition["field"]] ? true : false;
                    }
                }
                // check type of required value for condition and check if condition is fulfilled
            } elseif (is_string($condition["value"])) {
                if (is_array($this->_formResponse['data'][$condition["field"]])) {
                    
                    $returnArray[] = in_array($condition["value"], $this->_formResponse['data'][$condition["field"]]);
                } elseif (is_string($this->_formResponse['data'][$condition["field"]])) {
                    
                    $returnArray[] = in_array($value, $this->_formResponse['data'][$condition["field"]]);
                    
                    $type = $this->_getFieldType($condition["field"]);
                    switch ($type) {
                        case "textfield":
                        case "textareafield":
                            $returnArray[] = strtolower($condition["value"]) == strtolower($this->_formResponse['data'][$condition["field"]]) ? true : false;
                            break;
                        case "datefield":
                            $returnArray[] = $condition["value"] == Manager::getService('Date')->convertToTimeStamp($this->_formResponse['data'][$condition["field"]]) ? true : false;
                            break;
                        case "timefield":
                            $returnArray[] = Manager::getService('Date')->convertToTimeStamp($condition["value"]) == Manager::getService('Date')->convertToTimeStamp($this->_formResponse['data'][$condition["field"]]) ? true : false;
                            break;
                        case "numberfield":
                            $returnArray[] = intval($condition["value"]) == intval($this->_formResponse['data'][$condition["field"]]) ? true : false;
                            break;
                    }
                }
            }
        }
        return $returnArray;
    }

    protected function _getFieldType ($fieldId)
    {
        // return field type
        $toReturn = "";
        foreach ($this->_form["formPages"] as $pages) {
            foreach ($pages["elements"] as $field) {
                if ($field["id"] == $fieldId) {
                    $toReturn = $field["itemConfig"]["fieldType"];
                }
            }
        }
        return $toReturn;
    }
}
