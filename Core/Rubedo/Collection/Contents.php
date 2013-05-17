<?php

/**
 * Rubedo -- ECM solution Copyright (c) 2013, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Collection;
use Rubedo\Interfaces\Collection\IContents;
use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;

/**
 * Service to handle contents
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Contents extends WorkflowAbstractCollection implements IContents
{

    protected static $_isFrontEnd = false;

    protected $_indexes = array(
            array(
                    'keys' => array(
                            'workspace.target' => 1,
                            'createTime' => - 1
                    )
            ),
            array(
                    'keys' => array(
                            'workspace.target' => 1,
                            'typeId' => 1,
                            'createTime' => - 1
                    )
            ),
            array(
                    'keys' => array(
                            'live.target' => 1,
                            'createTime' => - 1
                    )
            ),
            array(
                    'keys' => array(
                            'live.target' => 1,
                            'typeId' => 1,
                            'createTime' => - 1
                    )
            ),
            array(
                    'keys' => array(
                            'workspace.target' => 1,
                            'text' => 1
                    )
            ),
            array(
                    'keys' => array(
                            'workspace.target' => 1,
                            'typeId' => 1,
                            'text' => 1
                    )
            ),
            array(
                    'keys' => array(
                            'live.target' => 1,
                            'text' => 1
                    )
            ),
            array(
                    'keys' => array(
                            'live.target' => 1,
                            'typeId' => 1,
                            'text' => 1
                    )
            ),
            array(
                    'keys' => array(
                            'live.startPublicationDate' => 1
                    )
            ),
            array(
                    'keys' => array(
                            'live.endPublicationDate' => 1
                    )
            )
    );

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

    protected static $_userFilter;

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
        
        // filter contents with user rights
        if (! self::isUserFilterDisabled()) {
            $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
            if (! in_array('all', $readWorkspaceArray)) {
                $readWorkspaceArray[] = null;
                $readWorkspaceArray[] = 'all';
                $filter = Filter::Factory('In')->setName('target')->setValue($readWorkspaceArray);
                $this->_dataService->addFilter($filter);
            }
        }
        
        if (self::$_isFrontEnd) {
            if (\Zend_Registry::isRegistered('draft')) {
                $live = (\Zend_Registry::get('draft')==='false'||\Zend_Registry::get('draft')===false)?true:false;
            } else {
                $live = true;
            }
            $now = (string) Manager::getService('CurrentTime')->getCurrentTime(); //cast to string as date are stored as text in DB
            $startPublicationDateField = ($live ? 'live' : 'workspace') .
                     '.startPublicationDate';
            $endPublicationDateField = ($live ? 'live' : 'workspace') .
                     '.endPublicationDate';

             $this->_dataService->addFilter(Filter::Factory('EmptyOrOperator')->setName($startPublicationDateField)->setOperator('$lte')->setValue($now));
             $this->_dataService->addFilter(Filter::Factory('EmptyOrOperator')->setName($endPublicationDateField)->setOperator('$gte')->setValue($now));
        }
    }

    /**
     * Return the visible contents list
     *
     * @param \WebTales\MongoFilters\IFilter $filters
     *            filters
     * @param array $sort
     *            array of sorting fields
     * @param integer $start
     *            offset of the list
     * @param integer $limit
     *            max number of items in the list
     * @return array:
     */
    public function getOnlineList (\WebTales\MongoFilters\IFilter $filters = null, $sort = null, $start = null, 
            $limit = null)
    {
        if(is_null($filters)){
            $filters = Filter::Factory();
        }
        $filters->addFilter(Filter::Factory('Value')->setName('online')->setValue(true));
        
        
        if (\Zend_Registry::isRegistered('draft')) {
            $live = (\Zend_Registry::get('draft')==='false'||\Zend_Registry::get('draft')===false)?true:false;
        } else {
            $live = true;
        }
        $returnArray = $this->getList($filters, $sort, $start, $limit, $live);
        
        return $returnArray;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\WorkflowAbstractCollection::create()
     */
    public function create (array $obj, $options = array(), $live = false, $index = true)
    {
        $obj = $this->_setDefaultWorkspace($obj);
        $this->_filterInputData($obj);
        
        if ($this->_isValidInput) {
            $returnArray = parent::create($obj, $options, $live);
        } else {
            $returnArray = array(
                    'success' => false,
                    'msg' => 'invalid input data',
                    'inputErrors' => $this->_inputDataErrors
            );
        }
        
        if ($returnArray["success"] and $index) {
            $this->_indexContent($returnArray['data']);
        }
        
        return $returnArray;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\WorkflowAbstractCollection::update()
     */
    public function update (array $obj, $options = array(), $live = true)
    {
        $origObj = $this->findById($obj['id'], $live, false);
        if (! self::isUserFilterDisabled()) {
            if (isset($origObj['readOnly']) && $origObj['readOnly']) {
                throw new \Rubedo\Exceptions\Access(
                        'no rights to update this content');
            }
        }
        if (! is_array($obj['target'])) {
            $obj['target'] = array(
                    $obj['target']
            );
        }
        if (count(
                array_intersect(array(
                        $obj['writeWorkspace']
                ), $obj['target'])) == 0) {
            $obj['target'][] = $obj['writeWorkspace'];
        }
        
        $this->_filterInputData($obj);
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
    public function destroy (array $obj, $options = array())
    {
        $origObj = $this->findById($obj['id'], false, false);
        if (! self::isUserFilterDisabled()) {
            if ($origObj['readOnly']) {
                throw new \Rubedo\Exceptions\Access(
                        'no rights to destroy this content');
            }
        }
        $returnArray = parent::destroy($obj, $options);
        if ($returnArray["success"]) {
            $this->_unIndexContent($obj);
        }
        return $returnArray;
    }

    public function unsetTerms ($vocId, $termId)
    {
        $data = array(
                '$unset' => array(
                        'taxonomy.' . $vocId . '.$' => 1
                )
        );

        $filters = Filter::Factory('Value')->setName('taxonomy.' . $vocId)->setValue($termId);
        return $this->_dataService->customUpdate($data, $filters);
    }

    /**
     * Push the content to Elastic Search
     *
     * @param array $obj            
     */
    protected function _indexContent ($obj)
    {   
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService(
                'ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->indexContent($obj);
    }

    /**
     * Remove the content from Indexed Search
     *
     * @param array $obj            
     */
    protected function _unIndexContent ($obj)
    {
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService(
                'ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->deleteContent($obj['typeId'], $obj['id']);
    }

    /**
     * Return validated data from input data based on content type
     *
     * @param array $obj            
     * @return array:
     */
    protected function _filterInputData (array $obj, array $model = null)
    {
        if (! self::isUserFilterDisabled()) {
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
            
            if (! in_array($obj['writeWorkspace'], $writeWorkspaces)) {
                throw new \Rubedo\Exceptions\Access(
                        'You can not assign to this workspace');
            }
            
            $readWorkspaces = Manager::getService('CurrentUser')->getReadWorkspaces();
            if ((! in_array('all', $readWorkspaces)) &&
                     count(array_intersect($obj['target'], $readWorkspaces)) == 0) {
                throw new \Rubedo\Exceptions\Access(
                        'You can not assign as target to this workspace');
            }
        }
        
        $contentTypeId = $obj['typeId'];
        $contentType = Manager::getService('ContentTypes')->findById(
                $contentTypeId);
        if (! self::isUserFilterDisabled() &&
                 ! in_array($obj['writeWorkspace'], $contentType['workspaces']) && ! in_array('all', $contentType['workspaces'])) {
            throw new \Rubedo\Exceptions\Access(
                    'You can not assign this content type to this workspace');
        }
        $contentTypeFields = $contentType['fields'];
        
        $fieldsArray = array();
        $missingField = array();
        
        $tempFields = array();
        $tempFields['text'] = $obj['text'];
        $tempFields['summary'] = $obj['fields']['summary'];
        
        foreach ($contentTypeFields as $value) {
            $fieldsArray[$value['config']['name']] = $value;
            if (! isset($value['config']['allowBlank']) ||
                     ! $value['config']['allowBlank']) {
                $result = false;
                if ($value['config']['name'] == "text" ||
                         $value['config']['name'] == "summary") {
                    $field = $value['config']['name'];
                    $result = $this->_controlAllowBlank($tempFields[$field], 
                            false);
                }
                if ($result == false) {
                    $missingField[$value['config']['name']] = $value['config']['name'];
                }
            }
        }
        
        $fieldsList = array_keys($fieldsArray);
        
        foreach ($obj['fields'] as $key => $value) {
            if (in_array($key, 
                    array(
                            'text',
                            'summary'
                    ))) {
                continue;
            }
            if (! in_array($key, $fieldsList)) {
                $this->_inputDataErrors[$key] = 'unknown field';
            } else {
                unset($missingField[$key]);
                
                if (isset($fieldsArray[$key]['config']['multivalued']) &&
                         $fieldsArray[$key]['config']['multivalued'] == true) {
                    $tempFields[$key] = array();
                    if (! is_array($value)) {
                        $value = array(
                                $value
                        );
                    }
                    foreach ($value as $valueItem) {
                        $this->_validateFieldValue($valueItem, 
                                $fieldsArray[$key]['config'], $key);
                        $tempFields[$key][] = $this->_filterFieldValue(
                                $valueItem, $fieldsArray[$key]['cType']);
                    }
                } else {
                    $this->_validateFieldValue($value, 
                            $fieldsArray[$key]['config'], $key);
                    
                    $tempFields[$key] = $this->_filterFieldValue($value, 
                            $fieldsArray[$key]['cType']);
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
                $this->_inputDataErrors[] = "The field " . $key .
                         " must be specified";
            }
        }
        
        if (isset($config['minLength'])) {
            $result = $this->_controlMinLength($value, $config['minLength']);
            
            if (! $result) {
                $this->_inputDataErrors[] = "The Length of the field " . $key .
                         " must be greater than " . $config['minLength'];
            }
        }
        
        if (isset($config['maxLength'])) {
            $result = $this->_controlMaxLength($value, $config['maxLength']);
            
            if (! $result) {
                $this->_inputDataErrors[] = "The Length of the field " . $key .
                         " must be greater than " . $config['maxLength'];
            }
        }
        
        if (isset($config['vtype'])) {
            $result = $this->_controlVtype($value, $config['vtype']);
            
            if (! $result) {
                $this->_inputDataErrors[] = "The value \"" . $value .
                         "\" doesn't match with the condition of validation \"" .
                         $config['vtype'] . "\"";
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
                    if (preg_match(
                            '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', 
                            $value)) {
                        return true;
                    } else {
                        return false;
                    }
                    break;
                case 'email':
                    if (preg_match(
                            '|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]{2,})+$|i', 
                            $value)) {
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

    public function getByType ($typeId, $start = null, $limit = null)
    {
        $filter = array(
                array(
                        'property' => 'typeId',
                        'value' => $typeId
                )
        );
        
        return $this->getList($filter,null,$start,$limit);
    }

    public function clearOrphanContents ()
    {
        $contentTypesService = Manager::getService('ContentTypes');
        
        $result = $contentTypesService->getList();
        
        // recovers the list of contentTypes id
        foreach ($result['data'] as $value) {
            $contentTypesArray[] = $value['id'];
        }
        
        $result = $this->customDelete(
                array(
                        'typeId' => array(
                                '$nin' => $contentTypesArray
                        )
                ));
        
        if ($result['ok'] == 1) {
            return array(
                    'success' => 'true'
            );
        } else {
            return array(
                    'success' => 'false'
            );
        }
    }

    public function countOrphanContents ()
    {
        $contentTypesService = Manager::getService('ContentTypes');
        
        $result = $contentTypesService->getList();
        
        // recovers the list of contentTypes id
        foreach ($result['data'] as $value) {
            $contentTypesArray[] = $value['id'];
        }
        
        return $this->count(
                array(
                        array(
                                'property' => 'typeId',
                                'operator' => '$nin',
                                'value' => $contentTypesArray
                        )
                ));
    }

    /**
     * Set workspace if none given based on User main group.
     *
     * @param array $content            
     * @return array
     */
    protected function _setDefaultWorkspace ($content)
    {
        if (! isset($content['writeWorkspace']) || $content['writeWorkspace'] ==
                 '' || $content['writeWorkspace'] == array()) {
            $mainWorkspace = Manager::getService('CurrentUser')->getMainWorkspace();
            $content['writeWorkspace'] = $mainWorkspace['id'];
        } else {
            $readWorkspaces = array_values(
                    Manager::getService('CurrentUser')->getReadWorkspaces());
            
            if (count(
                    array_intersect(array(
                            $content['writeWorkspace']
                    ), $readWorkspaces)) == 0 && $readWorkspaces[0] != "all") {
                throw new \Rubedo\Exceptions\Access(
                        'You don\'t have access to this workspace ');
            }
        }
        
        if (! is_array($content['target'])) {
            $content['target'] = array(
                    $content['target']
            );
        }
        
        if (! in_array($content['writeWorkspace'], $content['target'])) {
            $content['target'][] = $content['writeWorkspace'];
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
            
            // Set the workspace/target for old items in database
            if (! isset($obj['writeWorkspace']) || $obj['writeWorkspace'] == "" ||
                     $obj['writeWorkspace'] == array()) {
                $obj['writeWorkspace'] = "";
            }
            if (! isset($obj['target']) || $obj['target'] == "" ||
                     $obj['target'] == array()) {
                $obj['target'] = array(
                        'global'
                );
            }
            
            $contentTypeId = $obj['typeId'];
            $aclServive = Manager::getService('Acl');
            $contentType = Manager::getService('ContentTypes')->findById(
                    $contentTypeId);
            
            if (! $aclServive->hasAccess("write.ui.contents")) {
                $obj['readOnly'] = true;
            } elseif (! in_array($obj['writeWorkspace'], $writeWorkspaces)) {
                $obj['readOnly'] = true;
            } else {
                $obj['readOnly'] = true;
                foreach ($writeWorkspaces as $writeWorkspace){
                    if(in_array($writeWorkspace, $contentType['workspaces'])){
                        $obj['readOnly'] = false;
                    }
                }
            }
            
            $status = $obj['status'];
            $obj['readOnly']= $obj['readOnly'] || ! $aclServive->hasAccess("write.ui.contents.".$status); 
        }
        
        return $obj;
    }

    public function getListByTypeId ($typeId)
    {
        $filter = Filter::Factory('Value')->setName('typeId')->setValue($typeId);
        return $this->getList($filter);
    }

    public function isTypeUsed ($typeId)
    {
        $filter = Filter::Factory('Value')->setName('typeId')->setValue($typeId);
        $result = $this->_dataService->findOne($filter);
        return ($result != null) ? array(
                "used" => true
        ) : array(
                "used" => false
        );
    }

    /**
     *
     * @return the $_isFrontEnd
     */
    public static function getIsFrontEnd ()
    {
        return Contents::$_isFrontEnd;
    }

    /**
     *
     * @param boolean $_isFrontEnd            
     */
    public static function setIsFrontEnd ($_isFrontEnd)
    {
        Contents::$_isFrontEnd = $_isFrontEnd;
    }
    
    /**
     * Return a list of ordered objects
     *
     * @param array $filters
     * @param array $sort
     * @param string $start
     * @param string $limit
     * @param bool $live
     *
     * @todo migrate to new filters
     * @return array Return the contents list
     */
    public function getOrderedList($filters = null, $sort = null, $start = null, $limit = null, $live = true) {
        throw new \Exception('migrate to new Filters');
        $filterKey = null;
        
        foreach ($filters as $key => $filter) {
            
            if($filter["property"] == "id" && $filter["operator"] == "$"."in") {
                $filterKey = $key;
            }
        }
        
        if($filterKey !== null) {
            $orderFilter = $filters[$filterKey];
            $order = $orderFilter['value'];
            $orderedContents = array();
            
            $unorderedResults = $this->getList($filters, $sort, $start, $limit, $live);
            
            $orderedContents = $unorderedResults;
            	
            unset($orderedContents['data']);
            
            foreach ($order as $id) {
                foreach ($unorderedResults['data'] as $content) {
                    if($id === $content['id']) {
                        $orderedContents['data'][] = $content;
                    }
                }
            }
            
            return $orderedContents;
        } else {
            return array("success" => false, "msg" => "Invalid filter");
        }
    }
    
    public function deleteByContentType($contentTypeId){
        if(!is_string($contentTypeId)){
            throw new \Rubedo\Exceptions\User('ContentTypeId should be a string');
        }
        $contentTypeService = Manager::getService('ContentTypes');
        $contentType = $contentTypeService->findById($contentTypeId);
        if(!$contentType){
            throw new \Rubedo\Exceptions\User('ContentType not found');
        }
        
        $deleteCond = Filter::Factory('Value')->setName('typeId')->setValue($contentTypeId);
        $result = $this->_dataService->customDelete($deleteCond, array());
        
        if(isset($result['ok']) && $result['ok']){
            $contentTypeService->unIndexContentType($contentType);
            $contentTypeService->indexContentType($contentType);
            return array('success'=>true);
        }else{
            throw new \Rubedo\Exceptions\Server($result['err']);
        }
        
    }
	
}
