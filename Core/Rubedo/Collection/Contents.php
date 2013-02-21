<?php

/**
 * Rubedo -- ECM solution Copyright (c) 2012, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
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
        $this->_dataService->addToExcludeFieldList(array(
            'nestedContents'
        ));
        
        //filter contents with user rights
        if (! self::isUserFilterDisabled()) {
            $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
            if (in_array('all', $readWorkspaceArray)) {
                return;
            }
            $readWorkspaceArray[] = null;
            $filter = array(
                'target' => array(
                    '$in' => $readWorkspaceArray
                )
            );
            $this->_dataService->addFilter($filter);
        }
    }

    /**
     * Return the visible contents list
     *
     * @param array $filters
     *            array of filter
     * @param array $sort
     *            array of sorting fields
     * @param integer $start
     *            offset of the list
     * @param integer $limit
     *            max number of items in the list
     * @return array:
     */
    public function getOnlineList ($filters = null, $sort = null, $start = null, $limit = null)
    {
        $filters[] = array(
            'property' => 'online',
            'value' => true
        );
        
        if (\Zend_Registry::isRegistered('draft')) {
            $live = ! \Zend_Registry::get('draft');
        } else {
            $live = true;
        }
        
        $returnArray = $this->getList($filters, $sort, $start, $limit, $live);
        
        return $returnArray;
    }
    
    
    
    /* (non-PHPdoc)
     * @see \Rubedo\Collection\WorkflowAbstractCollection::getList()
     */
    public function getList ($filters = null, $sort = null, $start = null, $limit = null, $live = true)
    {
        $list = parent::getList($filters,$sort,$start,$limit,$live);
		
		foreach ($list['data'] as &$obj){
            $obj = $this->_addReadableProperty($obj);
        }
		
        return $list;
    }

	/*
     * (non-PHPdoc) @see \Rubedo\Collection\WorkflowAbstractCollection::create()
     */
    public function create (array $obj, $options = array('safe'=>true), $live = false)
    {
        $obj = $this->_setDefaultWorkspace($obj);
        $obj = $this->_filterInputData($obj);

        if ($this->_isValidInput) {
            $returnArray = parent::create($obj, $options, $live);
        } else {
            $returnArray = array(
                'success' => false,
                'msg' => 'invalid input data',
                'inputErrors' => $this->_inputDataErrors
            );
        }
        
        if ($returnArray["success"]) {
            $this->_indexContent($returnArray['data']);
        }
        
        return $returnArray;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\WorkflowAbstractCollection::update()
     */
    public function update (array $obj, $options = array('safe'=>true), $live = true)
    {        
        $origObj = $this->findById($obj['id'],$live,false);
        if (! self::isUserFilterDisabled()) {
            if ($origObj['readOnly']) {
                throw new \Rubedo\Exceptions\Access('no rights to update this content');
            }
        }
        
		if(count(array_intersect(array($obj['writeWorkspace']), $obj['target']))==0){
			$obj['target'][] = $obj['writeWorkspace'];
		}
		
        $obj = $this->_filterInputData($obj);
        if ($this->_isValidInput) {
            $returnArray = parent::update($obj, $options, $live);
        } else {
            $returnArray = array(
                'success' => false,
                'msg' => 'invalid input data',
                'inputErrors' => $this->_inputDataErrors
            );
        }
        
        if ($returnArray["success"]) {
            $this->_indexContent($returnArray['data']);
        }
        
        return $returnArray;
    }
        
        /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy (array $obj, $options = array('safe'=>true))
    {
        $origObj = $this->findById($obj['id'], false, false);
        if (! self::isUserFilterDisabled()) {
            if ($origObj['readOnly']) {
                throw new \Rubedo\Exceptions\Access('no rights to destroy this content');
            }
        }
        $returnArray = parent::destroy($obj, $options);
        if ($returnArray["success"]) {
            $this->_unIndexContent($obj);
        }
        return $returnArray;
    }

    /**
     * Push the content to Elastic Search
     *
     * @param array $obj            
     */
    protected function _indexContent ($obj)
    {
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->indexContent($obj['id']);
    }

    /**
     * Remove the content from Indexed Search
     *
     * @param array $obj            
     */
    protected function _unIndexContent ($obj)
    {
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->deleteContent($obj['typeId'], $obj['id']);
    }

    /**
     * Return validated data from input data based on content type
     *
     * @param array $obj            
     * @return array:
     */
    protected function _filterInputData (array $obj)
    {        
		if (! self::isUserFilterDisabled()) {
        	$writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
            
            if (! in_array($obj['writeWorkspace'], $writeWorkspaces)) {
                throw new \Rubedo\Exceptions\Access('You can not assign to this workspace');
            }
        
	        $readWorkspaces = Manager::getService('CurrentUser')->getReadWorkspaces();
	        if ((!in_array('all', $readWorkspaces)) && count(array_intersect($obj['target'], $readWorkspaces))==0) {
	            throw new \Rubedo\Exceptions\Access('You can not assign to this workspace');
	        }
		}
        
        $contentTypeId = $obj['typeId'];
        $contentType = Manager::getService('ContentTypes')->findById($contentTypeId);
        $contentTypeFields = $contentType['fields'];
		
        $fieldsArray = array();
        $missingField = array();
		
		$tempFields = array();
		$tempFields['text'] = $obj['text'];
        $tempFields['summary'] = $obj['fields']['summary'];
		
        foreach ($contentTypeFields as $value) {
            $fieldsArray[$value['config']['name']] = $value;
            if (! isset($value['config']['allowBlank']) || ! $value['config']['allowBlank']) {
            	$result = false;
            	if($value['config']['name'] == "text" || $value['config']['name'] == "summary"){
            		$field = $value['config']['name'];
            		$result = $this->_controlAllowBlank($tempFields[$field], false);
            	}
				if($result == false){
                	$missingField[$value['config']['name']] = $value['config']['name'];
				}
            }
        }
        
        $fieldsList = array_keys($fieldsArray);
        
        foreach ($obj['fields'] as $key => $value) {
            if (in_array($key, array(
                'text',
                'summary'
            ))) {
                continue;
            }
            if (! in_array($key, $fieldsList)) {
                $this->_inputDataErrors[$key] = 'unknown field';
            } else {
                unset($missingField[$key]);
                
                if (isset($fieldsArray[$key]['config']['multivalued']) && $fieldsArray[$key]['config']['multivalued'] == true) {
                    $tempFields[$key] = array();
                    if (! is_array($value)) {
                        $value = array(
                            $value
                        );
                    }
                    foreach ($value as $valueItem) {
                        $this->_validateFieldValue($valueItem, $fieldsArray[$key]['config'], $key);
                        $tempFields[$key][] = $this->_filterFieldValue($valueItem, $fieldsArray[$key]['cType']);
                    }
                } else {
                    $this->_validateFieldValue($value, $fieldsArray[$key]['config'], $key);

                    $tempFields[$key] = $this->_filterFieldValue($value, $fieldsArray[$key]['cType']);
                }
            }
        }
        
        $obj['fields'] = $tempFields;
        
        if (count($missingField) > 0) {
            foreach ($missingField as $value) {
                $this->_inputDataErrors[$value] = 'missing field';
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
     * @param mixed $value
     *            data value
     * @param array $config
     *            field config array
     * @param string $key
     *            field name
     * @return boolean
     */
    protected function _validateFieldValue ($value, $config, $key)
    {
        if (isset($config['allowBlank'])) {
            $result = $this->_controlAllowBlank($value, $config['allowBlank']);
            
            if (! $result) {
                $this->_inputDataErrors[] = "The field " . $key . " must be specified";
            }
        }
        
        if (isset($config['minLength'])) {
            $result = $this->_controlMinLength($value, $config['minLength']);
            
            if (! $result) {
                $this->_inputDataErrors[] = "The Length of the field " . $key . " must be greater than " . $config['minLength'];
            }
        }
        
        if (isset($config['maxLength'])) {
            $result = $this->_controlMaxLength($value, $config['maxLength']);
            
            if (! $result) {
                $this->_inputDataErrors[] = "The Length of the field " . $key . " must be greater than " . $config['maxLength'];
            }
        }
        
        if (isset($config['vtype'])) {
            $result = $this->_controlVtype($value, $config['vtype']);
            
            if (! $result) {
                $this->_inputDataErrors[] = "The value \"" . $value . "\" doesn't match with the condition of validation \"" . $config['vtype'] . "\"";
            }
        }
    }

    /**
     * Filter value based on field ctype
     * Mostly used for HTML fields
     *
     * @param mixed $value
     *            data value
     * @param string $cType
     *            field ctype
     * @return mixed
     */
    protected function _filterFieldValue ($value, $cType)
    {
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
     * @param mixed $value
     *            data value
     * @param bool $allowBlank
     *            configuration value
     * @return bool
     */
    protected function _controlAllowBlank ($value, $allowBlank)
    {
        if ($allowBlank == false) {
            if ($value == "" || $value == null) {
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
     * @param mixed $value
     *            data value
     * @param integer $minLength
     *            configuration value
     * @return bool
     */
    protected function _controlMinLength ($value, $minLength)
    {
        if (mb_strlen($value) > 0 && mb_strlen($value) < $minLength) {
            $response = false;
        } else {
            $response = true;
        }
        
        return $response;
    }

    /**
     * Check if the maxLength condition is respected
     *
     * @param mixed $value
     *            data value
     * @param integer $maxLength
     *            configuration value
     * @return bool
     */
    protected function _controlMaxLength ($value, $maxLength)
    {
        if (mb_strlen($value) > $maxLength) {
            $response = false;
        } else {
            $response = true;
        }
        
        return $response;
    }

    protected function _controlVtype ($value, $vtype)
    {
        if ($value != "") {
            switch ($vtype) {
                case 'alpha':
                    if (ctype_alpha($value)) {
                        return true;
                    } else {
                        return false;
                    }
                    break;
                case 'alphanum':
                    if (ctype_alnum($value)) {
                        return true;
                    } else {
                        return false;
                    }
                    break;
                case 'url':
                    if (preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $value)) {
                        return true;
                    } else {
                        return false;
                    }
                    break;
                case 'email':
                    if (preg_match('|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]{2,})+$|i', $value)) {
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

	public function getByType($typeId) {
		$filter = array(array('property' => 'typeId', 'value' => $typeId));
		
		return $this->getList($filter);
	}

	public function clearOrphanContents() {
		$contentTypesService = Manager::getService('ContentTypes');
		
		$result = $contentTypesService->getList();
		
		//recovers the list of contentTypes id
		foreach ($result['data'] as $value) {
			$contentTypesArray[] = $value['id'];
		}

		$result = $this->customDelete(array('typeId' => array('$nin' => $contentTypesArray)));
		
		if($result['ok'] == 1){
			return array('success' => 'true');
		} else {
			return array('success' => 'false');
		}
	}
	
	public function countOrphanContents() {
		$contentTypesService = Manager::getService('ContentTypes');

		$result = $contentTypesService->getList();
		
		//recovers the list of contentTypes id
		foreach ($result['data'] as $value) {
			$contentTypesArray[] = $value['id'];
		}
		
		return $this->count(array(array('property' => 'typeId', 'operator' => '$nin', 'value' => $contentTypesArray)));
	}
	
	/**
	 * Set workspace if none given based on User main group.
	 * 
	 * @param array $content
	 * @return array
	 */
	protected function _setDefaultWorkspace($content){
	    if(!isset($content['writeWorkspace']) || $content['writeWorkspace']=='' || $content['writeWorkspace']==array()){
	        $mainWorkspace = Manager::getService('CurrentUser')->getMainWorkspace();
	        $content['writeWorkspace'] = $mainWorkspace['id'];
	    }
	    if(!isset($content['target']) || $content['target']=='' || $content['target']==array() || !is_array($content['target'])){
	    	$mainWorkspace = Manager::getService('CurrentUser')->getMainWorkspace();
	        $content['target'] = array($mainWorkspace['id']);
        } else {
        	$readWorkspaces = array_values(Manager::getService('CurrentUser')->getReadWorkspaces());
			
			if(count(array_intersect($content['target'], $readWorkspaces))==0 && $readWorkspaces[0]!="all"){
				throw new \Rubedo\Exceptions\Access('You don\'t have access to this workspace ');
			}
        }
        return $content;
    }

    /**
     * Add a readOnly field to contents based on user rights
     * 
     * @param array $obj
     * @return array
     */
    protected function _addReadableProperty ($obj)
    {
        if (! self::isUserFilterDisabled()) {
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();

            //Set the workspace/target for old items in database
            if(!isset($obj['writeWorkspace']) || $obj['writeWorkspace']=="" || $obj['writeWorkspace']==array()){
            	$obj['writeWorkspace'] = "";
            }
            if(!isset($obj['target']) || $obj['target']=="" || $obj['target']==array()){
            	$obj['target'] = array('global');
            }
            
            $contentTypeId = $obj['typeId'];
            $contentType = Manager::getService('ContentTypes')->findById($contentTypeId);
            
            if ($contentType['readOnly']) {
                $obj['readOnly'] = true;
            } elseif (! in_array($obj['writeWorkspace'], $writeWorkspaces)) {
                $obj['readOnly'] = true;
            } else {
                
                $obj['readOnly'] = false;
            }
        }
        
        return $obj;
    }
	
	/**
	 *  (non-PHPdoc)
     * @see \Rubedo\Collection\WorkflowAbstractCollection::findById()
     */
    public function findById ($contentId, $live = true, $raw = true)
    {
        
        $obj = parent::findById ($contentId, $live,$raw);
        $obj = $this->_addReadableProperty($obj);
        return $obj;
        
    }
	public function getListByTypeId($typeId)
	{
		$filterArray[]=array("property"=>"typeId","value"=>$typeId);
		return $this->getList($filterArray);
	}

	
	
}
