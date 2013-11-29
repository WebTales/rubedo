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

use Rubedo\Content\Context;
use Rubedo\Interfaces\Collection\IContents;
use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\EventManager\EventInterface;
use Zend\Json\Json;

/**
 * Service to handle contents
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Contents extends WorkflowAbstractCollection implements IContents
{

    protected $_indexes = array(
        array(
            'keys' => array(
                'workspace.target' => 1,
                'createTime' => -1
            )
        ),
        array(
            'keys' => array(
                'workspace.target' => 1,
                'typeId' => 1,
                'createTime' => -1
            )
        ),
        array(
            'keys' => array(
                'live.target' => 1,
                'createTime' => -1
            )
        ),
        array(
            'keys' => array(
                'live.target' => 1,
                'typeId' => 1,
                'createTime' => -1
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

    protected static $nonLocalizableFields = array(
        'online',
        'typeId',
        'status',
        'startPublicationDate',
        'endPublicationDate',
        'target',
        'writeWorkspace',
        'pageId',
        'maskId',
        'blockId',
        'taxonomy'
    );

    protected static $localizableFiledForCType = array();

    protected static $isLocaleFiltered = true;

    public function __construct()
    {
        $this->_collectionName = 'Contents';
        parent::__construct();
    }

    /**
     * ensure that no nested contents are requested directly
     */
    protected function _init()
    {
        parent::_init();
        $this->_dataService->addToExcludeFieldList(array(
            'nestedContents'
        ));

        // filter contents with user rights
        if (!self::isUserFilterDisabled()) {
            $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
            if (!in_array('all', $readWorkspaceArray)) {
                $readWorkspaceArray[] = null;
                $readWorkspaceArray[] = 'all';
                $filter = Filter::factory('In')->setName('target')->setValue($readWorkspaceArray);
                $this->_dataService->addFilter($filter);
            }
        }

        if (static::$_isFrontEnd) {

            $now = (string)Manager::getService('CurrentTime')->getCurrentTime(); // cast to string as date are stored as text in DB
            $startPublicationDateField = (Context::isLive() ? 'live' : 'workspace') . '.startPublicationDate';
            $endPublicationDateField = (Context::isLive() ? 'live' : 'workspace') . '.endPublicationDate';

            $this->_dataService->addFilter(Filter::factory('EmptyOrOperator')->setName($startPublicationDateField)
                ->setOperator('$lte')
                ->setValue($now));
            $this->_dataService->addFilter(Filter::factory('EmptyOrOperator')->setName($endPublicationDateField)
                ->setOperator('$gte')
                ->setValue($now));
        }
        $this->initLocaleFilter();
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
    public function getOnlineList(\WebTales\MongoFilters\IFilter $filters = null, $sort = null, $start = null, $limit = null)
    {
        if (is_null($filters)) {
            $filters = Filter::factory();
        }
        $filters->addFilter(Filter::factory('Value')->setName('online')
            ->setValue(true));

        $returnArray = $this->getList($filters, $sort, $start, $limit, Context::isLive());

        return $returnArray;
    }

    /*
     * (non-PHPdoc) @see \Rubedo\Collection\WorkflowAbstractCollection::create()
     */
    public function create(array $obj, $options = array(), $live = false, $ignoreIndex = false)
    {
        $config = Manager::getService('config');
        $mongoConf = $config['datastream']['mongo'];
        if ((isset($mongoConf['maximumDataSize'])) && (!empty($mongoConf['maximumDataSize']))) {
            $dbStats = $this->_dataService->getMongoDBStats();
            $dataSize = $dbStats["dataSize"];
            if ($dataSize > $mongoConf['maximumDataSize']) {
                $returnArray = array(
                    'success' => false,
                    'msg' => 'Maximum database size reached.'
                );
                return $returnArray;
            }
        }
        $obj = $this->_setDefaultWorkspace($obj);
        $obj = $this->_filterInputData($obj);

        if ($this->_isValidInput) {
            $returnArray = parent::create($obj, $options, $live, $ignoreIndex);
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
    public function update(array $obj, $options = array(), $live = true)
    {
        $origObj = $this->findById($obj['id'], $live, false);
        if (!self::isUserFilterDisabled()) {
            if (isset($origObj['readOnly']) && $origObj['readOnly']) {
                throw new \Rubedo\Exceptions\Access('No rights to update this content', "Exception33");
            }
        }
        if (!is_array($obj['target'])) {
            $obj['target'] = array(
                $obj['target']
            );
        }
        if (count(array_intersect(array(
                $obj['writeWorkspace']
            ), $obj['target'])) == 0
        ) {
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

        if ($returnArray["success"] && $live) {
            $this->_indexContent($returnArray['data']);
        }

        return $returnArray;
    }

    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy(array $obj, $options = array())
    {
        $origObj = $this->findById($obj['id'], false, false);
        if (!self::isUserFilterDisabled()) {
            if ($origObj['readOnly']) {
                throw new \Rubedo\Exceptions\Access('No rights to destroy this content', "Exception34");
            }
        }
        $returnArray = parent::destroy($obj, $options);
        if ($returnArray["success"]) {
            $this->_unIndexContent($obj);
        }
        return $returnArray;
    }

    public function unsetTerms($vocId, $termId)
    {
        if (!$termId) {
            throw new \Rubedo\Exceptions\Server("You can not unset a term without its id", "Exception92");
        }

        $data = array(
            '$unset' => array(
                'taxonomy.' . $vocId . '.$' => 1
            )
        );

        $filters = Filter::factory('Value')->setName('taxonomy.' . $vocId)->setValue($termId);

        return $this->_dataService->customUpdate($data, $filters);
    }

    /**
     * Push the content to Elastic Search
     *
     * @param array $obj
     */
    protected function _indexContent($obj)
    {
        $contentType = Manager::getService('ContentTypes')->findById($obj['typeId']);
        if (!$contentType || (isset($contentType['system']) && $contentType['system'] == true)) {
            return;
        }

        $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->indexContent($obj);
    }

    /**
     * Remove the content from Indexed Search
     *
     * @param array $obj
     */
    protected function _unIndexContent($obj)
    {
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->deleteContent($obj['typeId'], $obj['id']);
    }

    /**
     * Return validated data from input data based on content type
     *
     * @todo : implement match against content type
     * @param array $obj
     * @return array:
     */
    protected function _filterInputData(array $obj, array $model = null)
    {
        if (!self::isUserFilterDisabled()) {
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();

            if (!in_array($obj['writeWorkspace'], $writeWorkspaces)) {
                throw new \Rubedo\Exceptions\Access('You can not assign to this workspace', "Exception35");
            }

            $readWorkspaces = Manager::getService('CurrentUser')->getReadWorkspaces();
            if ((!in_array('all', $readWorkspaces)) && count(array_intersect($obj['target'], $readWorkspaces)) == 0) {
                throw new \Rubedo\Exceptions\Access('You can not assign as target to this workspace', "Exception36");
            }
        }

        $contentTypeId = $obj['typeId'];
        $contentType = Manager::getService('ContentTypes')->findById($contentTypeId);
        if (!self::isUserFilterDisabled() && !in_array($obj['writeWorkspace'], $contentType['workspaces']) && !in_array('all', $contentType['workspaces'])) {
            throw new \Rubedo\Exceptions\Access('You can not assign this content type to this workspace', "Exception37");
        }
        $contentTypeFields = $contentType['fields'];

        foreach ($contentTypeFields as $fieldConfig) {
            switch ($fieldConfig['cType']) {
                case 'CKEField':
                    $obj = $this->filterCKEField($obj, $fieldConfig['config']['name']);
                    break;
                default;
            }
        }


        $fieldsArray = array();
        $missingField = array();

        $tempFields = array();
        $tempFields['text'] = $obj['text'];
        if (isset($obj['i18n'][$obj['nativeLanguage']]['fields']['summary'])) {
            $tempFields['summary'] = $obj['i18n'][$obj['nativeLanguage']]['fields']['summary'];
        } else {
            $tempFields['summary'] = "";
        }

        if (count($this->_inputDataErrors) === 0) {
            $this->_isValidInput = true;
        }

        return $obj;
    }

    protected function filterCKEField($obj, $name)
    {
        $cleanerService = Manager::getService('HtmlCleaner');

        if (isset($obj['fields'][$name])) {
            $obj['fields'][$name] = $cleanerService->clean($obj['fields'][$name]);
        }

        if (isset($obj['i18n'])) {
            foreach ($obj['i18n'] as $locale => $data) {
                if (isset($data['fields'][$name])) {
                    $obj['i18n'][$locale]['fields'][$name] = $cleanerService->clean($obj['i18n'][$locale]['fields'][$name]);
                }
            }
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
    protected function _validateFieldValue($value, $config, $key)
    {
        if (isset($config['allowBlank'])) {
            $result = $this->_controlAllowBlank($value, $config['allowBlank']);

            if (!$result) {
                $this->_inputDataErrors[] = "The field " . $key . " must be specified";
            }
        }

        if (isset($config['minLength'])) {
            $result = $this->_controlMinLength($value, $config['minLength']);

            if (!$result) {
                $this->_inputDataErrors[] = "The Length of the field " . $key . " must be greater than " . $config['minLength'];
            }
        }

        if (isset($config['maxLength'])) {
            $result = $this->_controlMaxLength($value, $config['maxLength']);

            if (!$result) {
                $this->_inputDataErrors[] = "The Length of the field " . $key . " must be greater than " . $config['maxLength'];
            }
        }

        if (isset($config['vtype'])) {
            $result = $this->_controlVtype($value, $config['vtype']);

            if (!$result) {
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
    protected function _filterFieldValue($value, $cType, array $config = array(), $fieldName = null)
    {
        switch ($cType) {
            case 'CKEField':
                $returnValue = Manager::getService('HtmlCleaner')->clean($value);
                break;
            case "numberfield":
                $max = isset($config["maxValue"]) ? $config["maxValue"] : null;
                $min = isset($config["minValue"]) ? $config["minValue"] : null;

                if (!is_null($max)) {
                    if ($value > $max) {
                        if (isset($fieldName)) {
                            $this->_inputDataErrors[$fieldName] = "The value of this field is higher than its maximum value";
                        } else {
                            $this->_inputDataErrors[] = "The value of this field (" . $value . ") is higher than its maximum value (" . $max . ")";
                        }
                    }
                }

                if (!is_null($min)) {
                    if ($value < $min) {
                        if (isset($fieldName)) {
                            $this->_inputDataErrors[$fieldName] = "The value of this field is lower than its minimum value";
                        } else {
                            $this->_inputDataErrors[] = "The value of this field (" . $value . ") is lower than its minimum value (" . $min . ")";
                        }
                    }
                }

                $returnValue = $value;
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
    protected function _controlAllowBlank($value, $allowBlank)
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
    protected function _controlMinLength($value, $minLength)
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
    protected function _controlMaxLength($value, $maxLength)
    {
        if (mb_strlen($value) > $maxLength) {
            $response = false;
        } else {
            $response = true;
        }

        return $response;
    }

    protected function _controlVtype($value, $vtype)
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

    public function getByType($typeId, $start = null, $limit = null)
    {
        $filter = Filter::factory('Value')->setName('typeId')->SetValue($typeId);
        return $this->getList($filter, null, $start, $limit);
    }

    public function clearOrphanContents()
    {
        $contentTypesService = Manager::getService('ContentTypes');

        $result = $contentTypesService->getList();

        // recovers the list of contentTypes id
        foreach ($result['data'] as $value) {
            $contentTypesArray[] = $value['id'];
        }
        $filter = Filter::factory('NotIn')->setName('typeId')->SetValue($contentTypesArray);
        $options = array(
            'multiple' => true
        );

        $result = $this->customDelete($filter, $options);

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

    public function countOrphanContents()
    {
        $contentTypesService = Manager::getService('ContentTypes');

        $result = $contentTypesService->getList();

        // recovers the list of contentTypes id
        foreach ($result['data'] as $value) {
            $contentTypesArray[] = $value['id'];
        }
        $filter = Filter::factory('NotIn')->setName('typeId')->SetValue($contentTypesArray);
        return $this->count($filter);
    }

    /**
     * Set workspace if none given based on User main group.
     *
     * @param array $content
     * @return array
     */
    protected function _setDefaultWorkspace($content)
    {
        if (!isset($content['writeWorkspace']) || $content['writeWorkspace'] == '' || $content['writeWorkspace'] == array()) {
            $mainWorkspace = Manager::getService('CurrentUser')->getMainWorkspace();
            $content['writeWorkspace'] = $mainWorkspace['id'];
        } else {
            $readWorkspaces = array_values(Manager::getService('CurrentUser')->getReadWorkspaces());

            if (count(array_intersect(array(
                    $content['writeWorkspace']
                ), $readWorkspaces)) == 0 && $readWorkspaces[0] != "all"
            ) {
                throw new \Rubedo\Exceptions\Access("You don't have access to this workspace", "Exception38");
            }
        }

        if (!is_array($content['target'])) {
            $content['target'] = array(
                $content['target']
            );
        }

        if (!in_array($content['writeWorkspace'], $content['target'])) {
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
    protected function _addReadableProperty($obj)
    {
        if (!self::isUserFilterDisabled()) {
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();

            // Set the workspace/target for old items in database
            if (!isset($obj['writeWorkspace']) || $obj['writeWorkspace'] == "" || $obj['writeWorkspace'] == array()) {
                $obj['writeWorkspace'] = "";
            }
            if (!isset($obj['target']) || $obj['target'] == "" || $obj['target'] == array()) {
                $obj['target'] = array(
                    'global'
                );
            }

            $contentTypeId = $obj['typeId'];
            $aclServive = Manager::getService('Acl');
            $contentType = Manager::getService('ContentTypes')->findById($contentTypeId);

            if (!$aclServive->hasAccess("write.ui.contents")) {
                $obj['readOnly'] = true;
            } elseif (!in_array($obj['writeWorkspace'], $writeWorkspaces)) {
                $obj['readOnly'] = true;
            } else {
                $obj['readOnly'] = true;
                foreach ($writeWorkspaces as $writeWorkspace) {
                    if (in_array($writeWorkspace, $contentType['workspaces'])) {
                        $obj['readOnly'] = false;
                    }
                }
                //dirty dirty fix
                if (((isset($obj['pageId']))&&($obj['pageId']!=""))||((isset($obj['pageId']))&&($obj['pageId']!=""))){
                    $obj['readOnly'] = false;
                }
            }

            $status = isset($obj['status']) ? $obj['status'] : null;
            $obj['readOnly'] = $obj['readOnly'] || !$aclServive->hasAccess("write.ui.contents." . $status);
        }

        return $obj;
    }

    public function getListByTypeId($typeId)
    {
        $filter = Filter::factory('Value')->setName('typeId')->setValue($typeId);
        return $this->getList($filter);
    }

    public function isTypeUsed($typeId)
    {
        $filter = Filter::factory('Value')->setName('typeId')->setValue($typeId);
        $result = $this->_dataService->findOne($filter, false);
        return ($result != null) ? array(
            "used" => true
        ) : array(
            "used" => false
        );
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
     * @return array Return the contents list
     */
    public function getOrderedList($filters = null, $sort = null, $start = null, $limit = null, $live = true)
    {
        $inUidFilter = $this->_getInUidFilter($filters);
        if ($inUidFilter !== null) {
            $order = $inUidFilter->getValue();
            $orderedContents = array();

            $unorderedResults = $this->getList($filters, $sort, $start, $limit, $live);

            $orderedContents = $unorderedResults;

            unset($orderedContents['data']);

            foreach ($order as $id) {
                foreach ($unorderedResults['data'] as $content) {
                    if ($id === $content['id']) {
                        $orderedContents['data'][] = $content;
                    }
                }
            }

            return $orderedContents;
        } else {
            throw new \Rubedo\Exceptions\User("Invalid filter", "Exception39");
        }
    }

    /**
     * Search filter for a InUidFilter in a Filter
     *
     * Return null if not found
     *
     * @param \WebTales\MongoFilters\IFilter $filter
     * @return \WebTales\MongoFilters\InUidFilter null
     */
    protected function _getInUidFilter(\WebTales\MongoFilters\IFilter $filter)
    {
        if ($filter instanceof \WebTales\MongoFilters\InUidFilter) {
            return $filter;
        }
        if ($filter instanceof \WebTales\MongoFilters\CompositeFilter) {
            foreach ($filter->getFilters() as $subFilter) {
                $subResult = $this->_getInUidFilter($subFilter);
                if ($subResult) {
                    return $subResult;
                }
            }
        }
        return null;
    }

    public function deleteByContentType($contentTypeId)
    {
        if (!is_string($contentTypeId)) {
            throw new \Rubedo\Exceptions\User('ContentTypeId should be a string', "Exception40", "ContentTypeId");
        }
        $contentTypeService = Manager::getService('ContentTypes');
        $contentType = $contentTypeService->findById($contentTypeId);
        if (!$contentType) {
            throw new \Rubedo\Exceptions\User('ContentType not found', "Exception41");
        }

        $deleteCond = Filter::factory('Value')->setName('typeId')->setValue($contentTypeId);
        $result = $this->_dataService->customDelete($deleteCond, array());

        if (isset($result['ok']) && $result['ok']) {
            $contentTypeService->unIndexContentType($contentType);
            $contentTypeService->indexContentType($contentType);
            return array(
                'success' => true
            );
        } else {
            throw new \Rubedo\Exceptions\Server($result['err']);
        }
    }

    public function getReflexiveLinkedContents($contentId, $typeId, $fieldName, $sort = null)
    {
        $filterArray = Filter::factory();

        $filterArray->addFilter(Filter::factory('Value')->setName("typeId")
            ->setValue($typeId));
        $filterArray->addFilter(Filter::factory('Value')->setName('fields.' . $fieldName)
            ->setValue($contentId));

        $sort = Json::decode($sort, Json::TYPE_ARRAY);

        return $this->getList($filterArray, $sort, null, null, true);
    }

    /**
     * Search the content published and index it.
     *
     * @param \Zend\EventManager\EventInterface $e the event
     */
    public function indexPublishEvent(EventInterface $e)
    {
        $data = $e->getParam('data', array());
        if (!$data['ignoreIndex']) {
            // get the live content to send it to indexer service
            $content = $this->findById($data['id'], true, false);

            $this->_indexContent($content);
        }
    }

    /* (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractLocalizableCollection::localizeInput()
     */
    protected function localizeInput($obj)
    {
        $metadataFields = $this->getMetaDataFields();
        // force label to contain only native title in DB
        if (isset($obj['i18n'][$obj['nativeLanguage']]['fields']['text'])) {
            $obj['fields']['text'] = $obj['i18n'][$obj['nativeLanguage']]['fields']['text'];
            $obj['text'] = $obj['fields']['text'];
        }

        // prevent localizable data to be stored in root level
        foreach ($obj as $key => $field) {
            if (!in_array($key, $metadataFields) && $key !== static::$labelField && $key !== 'fields') {
                unset($obj[$key]);
            }
            foreach ($obj['fields'] as $field => $value) {
                if (in_array($field, $this->getLocalizableFieldForCType($obj['typeId']))) {
                    unset($obj['fields'][$field]);
                }
            }
        }

        // prevent non localizable data to be store in localization document
        if (isset($obj['i18n'])) {
            foreach ($obj['i18n'] as $locale => $localization) {
                foreach ($localization as $key => $value) {
                    if (in_array($key, $metadataFields) && $key !== static::$labelField && $key !== 'fields') {
                        unset($obj['i18n'][$locale][$key]);
                    }
                    foreach ($obj['i18n'][$locale]['fields'] as $field => $value) {
                        if (!in_array($field, $this->getLocalizableFieldForCType($obj['typeId']))) {
                            unset($obj['i18n'][$locale]['fields'][$field]);
                        }
                    }
                }
                $obj['i18n'][$locale]['locale'] = $locale;
            }
        }



        return $obj;

    }

    /**
     * Set localization information on a not yet localized item
     *
     * @param array $obj
     * @return array
     */
    public function addlocalization($obj)
    {
        if (isset($obj['nativeLanguage'])) {
            return $obj;
        }

        if (!isset($obj['fields'])) {
            $obj['fields'] = array();
        }

        $nativeContent = $obj;



        foreach ($this->getMetaDataFields() as $metaField) {
            if ($metaField !== 'text' && $metaField !== 'fields') {
                unset($nativeContent[$metaField]);
            }
            foreach ($nativeContent['fields'] as $field => $value) {
                if (!in_array($field, $this->getLocalizableFieldForCType($obj['typeId']))) {
                    unset($nativeContent['fields'][$field]);
                }
            }
            $nativeContent['locale'] = static::$defaultLocale;
        }
        foreach ($obj as $key => $field) {
            if (!in_array($key, $this->getMetaDataFields()) && $key != 'fields') {
                unset($obj[$key]);
            }
        }
        foreach ($obj['fields'] as $field => $value) {
            if (in_array($field, $this->getLocalizableFieldForCType($obj['typeId']))) {
                unset($obj['fields'][$field]);
            }
        }
        $obj['nativeLanguage'] = static::$defaultLocale;
        $obj['i18n'] = array(
            static::$defaultLocale => $nativeContent
        );
        return $obj;
    }

    protected function getLocalizableFieldForCType($cTypeId)
    {
        if (!isset(self::$localizableFiledForCType[$cTypeId])) {
            self::$localizableFiledForCType[$cTypeId] = manager::getService('ContentTypes')->getLocalizableFieldForCType($cTypeId);
        }
        return self::$localizableFiledForCType[$cTypeId];
    }

    /**
     * Custom array_merge
     *
     * Do a recursive array merge except that numeric array are overriden
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    protected function merge($array1, $array2)
    {
        if (is_array($array2)) {
            foreach ($array2 as $key => $value) {
                if (isset($array1[$key]) && is_array($value) && !$this->isNumericArray($value)) {
                    $array1[$key] = $this->merge($array1[$key], $array2[$key]);
                } elseif (isset($array1[$key]) && is_array($value) && $key == 'fields') {
                    $array1[$key] = $this->merge($array1[$key], $array2[$key]);
                } else {
                    $array1[$key] = $value;
                }
            }
        }

        return $array1;
    }


    /**
     * Localize not yet localized items of the current collection
     */
    public function addLocalizationForCollection()
    {
        $wasFiltered = parent::disableUserFilter();
        $this->_dataService->clearFilter();
        $this->_dataService->setWorkspace();
        $items = AbstractCollection::getList(Filter::factory('OperatorToValue')->setName('nativeLanguage')
            ->setOperator('$exists')
            ->setValue(false));
        if ($items['count'] > 0) {
            foreach ($items['data'] as $item) {
                if (preg_match('/[\dabcdef]{24}/', $item['id']) == 1) {
                    $item = $this->addlocalization($item);

                    //$this->customUpdate($item, Filter::factory('Uid')->setValue($item['id']));
                    $this->update($item, array(), false);

                    if ($item['status'] == 'published') {
                        $this->publish($item['id']);
                    }
                }
            }
        }

        parent::disableUserFilter($wasFiltered);
    }

}
