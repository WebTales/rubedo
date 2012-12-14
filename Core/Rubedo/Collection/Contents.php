<?php

/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */
namespace Rubedo\Collection;
use Rubedo\Interfaces\Collection\IContents;
use Rubedo\Services\Manager;

/**
 * Service to handle contents
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Contents extends WorkflowAbstractCollection implements IContents
{

    /**
     * Is the input obj is valid
     *
     * @var bool
     */
    protected $_isValidInput = false;

    /**
     * contains found errors when validating input data
     *
     * @var array
     */
    protected $_inputDataErrors = array();

    public function __construct ()
    {
        $this->_collectionName = 'Contents';
        parent::__construct();
    }

    /**
     * ensure that no nested contents are requested directly
     */
    protected function _init ()
    {
        parent::_init();
        $this->_dataService->addToExcludeFieldList(
                array(
                        'nestedContents'
                ));
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\WorkflowAbstractCollection::create()
     */
    public function create (array $obj, $safe = true, $live = true)
    {
        $obj = $this->_filterInputData($obj);
        if ($this->_isValidInput) {
            $returnArray = parent::create($obj, $safe, $live);
        } else {
            $returnArray = array(
                    'success' => false,
                    'msg' => 'invalid input data',
                    'inputErrors' => $this->_inputDataErrors
            );
        }
        return $returnArray;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\WorkflowAbstractCollection::update()
     */
    public function update (array $obj, $safe = true, $live = true)
    {
        $obj = $this->_filterInputData($obj);
        if ($this->_isValidInput) {
            $returnArray = parent::update($obj, $safe, $live);
        } else {
            $returnArray = array(
                    'success' => false,
                    'msg' => 'invalid input data',
                    'inputErrors' => $this->_inputDataErrors
            );
        }
        return $returnArray;
    }

    /**
     * Return validated data from input data based on content type
     *
     * @param array $obj            
     * @return array:
     */
    protected function _filterInputData (array $obj)
    {
        $contentTypeId = $obj['typeId'];
        $contentType = Manager::getService('ContentTypes')->findById(
                $contentTypeId);
        $contentTypeFields = $contentType['fields'];
        $fieldsArray = array();
        $missingField = array();
        foreach ($contentTypeFields as $value) {
            $fieldsArray[$value['config']['name']] = $value;
            if(!isset($value['config']['allowBlank']) || ! $value['config']['allowBlank']){
                $missingField[$value['config']['name']] = $value['config']['name'];
            }
        }
        
        $fieldsList = array_keys($fieldsArray);
        $tempFields = array();
        $tempFields['text'] = $obj['text'];
        
        foreach ($obj['fields'] as $key => $value) {
            if($key == 'text'){
                continue;
            }
            if (! in_array($key, $fieldsList)) {
                $this->_inputDataErrors[$key] = 'unknown field';
            } else {
                unset($missingField[$key]);
                
                if(isset($fieldsArray[$key]['config']['multivalued']) && $fieldsArray[$key]['config']['multivalued'] == true){
                	$tempFields[$key]= array();
                	if(!is_array($value)){
                		$value = array($value);
                	}
                	foreach($value as $valueItem){
                		$this->_validateFieldValue($valueItem, $fieldsArray[$key]['config'], $key);
                		$tempFields[$key][] = $this->_filterFieldValue($valueItem, $fieldsArray[$key]['cType']);
                	}
                }else{
                	$this->_validateFieldValue($value, $fieldsArray[$key]['config'], $key);
                	$tempFields[$key]= $this->_filterFieldValue($value, $fieldsArray[$key]['cType']);
                }
            }
        }
        
        $obj['fields'] = $tempFields;
        
        if (count($missingField) > 0) {
            foreach ($missingField as $value){
                $this->_inputDataErrors[$key] = 'missing field';
            }
        }
        
        
        if (count($this->_inputDataErrors) === 0) {
            $this->_isValidInput = true;
        }
        
        return $obj;
    }
    
    
    /**
     * Check if value is valid based on field config from type content
     * 
     * @param mixed $value data value
     * @param array $config field config array
     * @param string $key field name
     * @return boolean
     */
    protected function _validateFieldValue($value, $config, $key){
    	
    	if(isset($config['allowBlank'])){
    		$result = $this->_controlAllowBlank($value, $config['allowBlank']);
			
			if(!$result){
				$this->_inputDataErrors[] = "The field ".$key." must be specified";
			}
    	}
		
		if(isset($config['minLength'])){
			$result = $this->_controlMinLength($value, $config['minLength']);
			
			if(!$result){
				$this->_inputDataErrors[] = "The Length of the field ".$key." must be greater than ".$config['minLength'];
			}
		}
		
		if(isset($config['maxLength'])){
			$result = $this->_controlMaxLength($value, $config['maxLength']);
			
			if(!$result){
				$this->_inputDataErrors[] = "The Length of the field ".$key." must be greater than ".$config['minLength'];
			}
		}
		
		if(isset($config['vtype'])){
			$result = $this->_controlVtype($value, $config['vtype']);
			
			if(!$result){
				$this->_inputDataErrors[] = "The value \"".$value."\" doesn't match with the condition of validation \"".$config['vtype']."\"";
			}
		}
		
    }
    
    
    /**
     * Filter value based on field ctype
     * 
     * Mostly used for HTML fields
     * 
     * @param mixed $value data value
     * @param string $cType field ctype
     * @return mixed
     */
    protected function _filterFieldValue($value, $cType){
    	switch ($cType) {
    		case 'CKEField':
    			$returnValue = Manager::getService('HtmlCleaner')->clean($value);
    			break;
    		default:
    			$returnValue = $value;
    			break;
    	}
    	return $returnValue;
    }
	
	/**
	 * Check if the allowBlank condition is respected
	 * 
	 * @param mixed $value data value
	 * @param bool $allowBlank configuration value
	 * @return bool
	 */
	protected function _controlAllowBlank($value, $allowBlank){
		if($allowBlank == false){
			if($value == "" || $value == null){
				$response = false;
			} else {
				$response = true;
			}
		} else {
			$response = true;
		}
		
		return $response;
	}
	
	/**
	 * Check if the minLength condition is respected
	 * 
	 * @param mixed $value data value
	 * @param integer $minLength configuration value
	 * @return bool
	 */
	protected function _controlMinLength($value, $minLength){
		if(mb_strlen($value)>0 && mb_strlen($value)<$minLength){
			$response = false;
		} else {
			$response = true;
		}
		
		return $response;
	}
	
	/**
	 * Check if the maxLength condition is respected
	 * 
	 * @param mixed $value data value
	 * @param integer $maxLength configuration value
	 * @return bool
	 */
	protected function _controlMaxLength($value, $maxLength){
		if(mb_strlen($value)>$maxLength){
			$response = false;
		} else {
			$response = true;
		}
		
		return $response;
	}
	
	protected function _controlVtype($value, $vtype){
			
		if($value != ""){	
			switch ($vtype) {
				case 'alpha':
					if(ctype_alpha($value)){
						return true;
					} else {
						return false;
					}
					break;
				case 'alphanum':
					if(ctype_alnum($value)){
						return true;
					} else {
						return false;
					}
					break;
				case 'url':
					if(preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $value)){
						return true;
					} else {
						return false;
					}
					break;
				case 'email':
					if(preg_match('|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]{2,})+$|i', $value)){
						return true;
					} else {
						return false;
					}
					break;
			}
		} else {
			return true;
		}
	}
	
}
