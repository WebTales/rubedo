<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IContentTypes;
use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;

/**
 * Service to handle ContentTypes
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class ContentTypes extends AbstractLocalizableCollection implements IContentTypes
{
    protected static $nonLocalizableFields = array("defaultId","notifyForQuantityBelow", "outOfStockLimit", "preparationDelay", "resupplyDelay", "canOrderNotInStock", "manageStock", "shippers", "productType", "fields", "layouts", "vocabularies", "dependant", "activateDisqus", "dependantTypes", "readOnly", "workspaces", "workflow", "system", "CTType", "code");
    protected static $labelField = 'type';

    protected $_indexes = array(
        array(
            'keys' => array(
                'type' => 1
            )
        ),
        array(
            'keys' => array(
                'productType' => 1
            )
        )
    );

    /**
     * Only access to content with read access
     *
     * @see \Rubedo\Collection\AbstractCollection::_init()
     */
    protected function _init()
    {
        parent::_init();

        if (!self::isUserFilterDisabled()) {
            $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
            if (in_array('all', $readWorkspaceArray)) {
                return;
            }
            $readWorkspaceArray[] = null;
            $readWorkspaceArray[] = 'all';
            // $filter = array('workspaces'=> array('$in'=>$readWorkspaceArray));
            $filter = Filter::factory('OperatorToValue')->setName('workspaces')
                ->setOperator('$in')
                ->setValue($readWorkspaceArray);
            $this->_dataService->addFilter($filter);
        }
    }

    protected $_model = array(
        'type' => array(
            'domain' => 'name',
            'required' => true
        ),
        'dependant' => array(
            'domain' => 'bool',
            'required' => true
        ),
        'dependantTypes' => array(
            'domain' => 'list',
            'required' => true,
            'items' => array(
                'domain' => 'id',
                'required' => false
            )
        ),
        'fields' => array(
            'domain' => 'list',
            'required' => true,
            'items' => array(
                'domain' => 'array',
                'required' => false,
                'items' => array(
                    'domain' => 'array',
                    'required' => false,
                    'cType' => array(
                        'domain' => 'string',
                        'required' => true
                    ),
                    'config' => array(
                        "name" => array(
                            'domain' => 'string',
                            'required' => true
                        ),
                        "fieldLabel" => array(
                            'domain' => 'string',
                            'required' => true
                        ),
                        "allowBlank" => array(
                            'domain' => 'bool',
                            'required' => false
                        ),
                        "localizable" => array(
                            'domain' => 'bool',
                            'required' => false
                        ),
                        "searchable" => array(
                            'domain' => 'bool',
                            'required' => false
                        ),
                        "multivalued" => array(
                            'domain' => 'bool',
                            'required' => false
                        ),
                        "tooltip" => array(
                            'domain' => 'string',
                            'required' => false
                        ),
                        "labelSeparator" => array(
                            'domain' => 'string',
                            'required' => false
                        )
                    )
                )
            )
        ),
        'vocabularies' => array(
            'domain' => 'list',
            'required' => true,
            'items' => array(
                'domain' => 'id',
                'required' => false
            )
        )
    );

    public function __construct()
    {
        $this->_collectionName = 'ContentTypes';
        parent::__construct();
    }

    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create(array $obj, $options = array(), $live = true)
    {
        if (!isset($obj['workspaces']) || $obj['workspaces'] == '' || $obj['workspaces'] == array()) {
            $mainWorkspace = Manager::getService('CurrentUser')->getMainWorkspace();
            $obj['workspaces'] = array(
                $mainWorkspace['id']
            );
        }
        $returnArray = parent::create($obj, $options, $live);

        if ($returnArray["success"]) {
            $this->indexContentType($returnArray['data']);
        }
        return $returnArray;
    }

    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update(array $obj, $options = array(), $live = true)
    {
        if (!isset($obj['workspaces']) || $obj['workspaces'] == '' || $obj['workspaces'] == array()) {
            $mainWorkspace = Manager::getService('CurrentUser')->getMainWorkspace();
            $obj['workspaces'] = array(
                $mainWorkspace['id']
            );
        }
        $returnArray = parent::update($obj, $options, $live);

        if ($returnArray["success"]) {
            $this->indexContentType($returnArray['data']);
            Manager::getService("ApiCache")->clearForEntity($obj["id"]);
        }

        return $returnArray;
    }

    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy(array $obj, $options = array())
    {
        $returnArray = parent::destroy($obj, $options);
        if ($returnArray["success"]) {
            $this->unIndexContentType($obj);
            Manager::getService("ApiCache")->clearForEntity($obj["id"]);
        }

        return $returnArray;
    }

    public function findById($contentId, $forceReload = false) {
        $contentType = parent::findById($contentId, $forceReload);

        return $this->localizeContentTypeFields($contentType);
    }

    /**
     * Push the content type to Elastic Search
     *
     * @param array $obj
     */
    public function indexContentType($obj)
    {
        $wasFiltered = AbstractCollection::disableUserFilter();

        Manager::getService('ElasticContentTypes')->deleteTypeIndex($obj['id']);

        Manager::getService('ElasticContentTypes')->setMapping($obj['id'], $obj);

        Manager::getService('ElasticContentTypes')->index($obj['id']);

        AbstractCollection::disableUserFilter($wasFiltered);
    }

    /**
     * Remove the content type from Indexed Search
     *
     * @param array $obj
     */
    public function unIndexContentType($obj)
    {
        $wasFiltered = AbstractCollection::disableUserFilter();

        Manager::getService('ElasticContentTypes')->delete($obj['id']);

        AbstractCollection::disableUserFilter($wasFiltered);
    }


    protected function _addReadableProperty($obj)
    {
        if (!self::isUserFilterDisabled()) {
            // Set the workspace for old items in database
            if (!isset($obj['workspaces']) || $obj['workspaces'] == "") {
                $obj['workspaces'] = array(
                    'global'
                );
            }
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();

            if (!Manager::getService('Acl')->hasAccess("write.ui.contentTypes") || (count(array_intersect($obj['workspaces'], $writeWorkspaces)) == 0 && !in_array("all", $writeWorkspaces))) {
                $obj['readOnly'] = true;
            } else {
                $obj['readOnly'] = false;
            }
        }

        return $obj;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Collection\IContentTypes::getReadableContentTypes()
     */
    public function getReadableContentTypes()
    {
        $currentUserService = Manager::getService('CurrentUser');
        $contentTypesList = array();

        $readWorkspaces = $currentUserService->getReadWorkspaces();
        $readWorkspaces[] = null;

        $filters = Filter::factory();
        if (!in_array("all", $readWorkspaces)) {
            $filter = Filter::factory('In')->setName('workspaces')->setValue($readWorkspaces);
            $filters->addFilter($filter);
        }
        $filters->addFilter(
            Filter::factory('Not')
                ->setName('system')
            ->setValue(true)
        );
        $readableContentTypes = $this->getList($filters);

        foreach ($readableContentTypes['data'] as $value) {
            $contentTypesList[$value['type']] = array(
                'type' => $value['type'],
                'id' => $value['id']
            );
        }
        ksort($contentTypesList);
        $contentTypesList = array_values($contentTypesList);

        return $contentTypesList;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Collection\IContentTypes::getGeolocatedContentTypes()
     */
    public function getGeolocatedContentTypes()
    {
        $contentTypesList = $this->getList();
        $geolocatedContentTypes = array();

        foreach ($contentTypesList['data'] as $contentType) {

            $fields = $contentType["fields"];
            foreach ($fields as $field) {
                if ($field['config']['name'] == 'position') {
                    $geolocatedContentTypes[] = $contentType['id'];
                }
            }
        }
        return $geolocatedContentTypes;
    }

    public function getFacetedFields()
    {

        $contentTypesList = $this->getList();
        $facetedFieldsList = array();

        foreach ($contentTypesList['data'] as $contentType) {

            $fields = $contentType["fields"];
            foreach ($fields as $field) {
                if (isset($field['config']['useAsFacet']) && $field['config']['useAsFacet']) {
                    $facetedFieldsList[] = array(
                        "contentTypeId" => $contentType['id'],
                        "name" => $field['config']['name'],
                        "label" => $field['config']['fieldLabel'],
                        "localizable" => $field['config']['localizable'],
                        "facetOperator" => isset($field['config']['facetOperator']) ? strtolower($field['config']['facetOperator']) : "and",
                        "useAsVariation" => isset($field['config']['useAsVariation']) ? $field['config']['useAsVariation'] : false
                    );

                }
            }
        }
        return $facetedFieldsList;
    }

    public function isChangeableContentType($originalType, $newType)
    {
        $result = true;
        $authorizedCtype = array(
            "text" => array(
                "textfield",
                "textareafield",
                "textarea"
            ),
            "number" => array(
                "numberfield",
                "slider",
                "ratingField"
            ),
            "checkbox" => array(
                "checkboxfield",
                "checkbox"
            )
        );
        /*
         * Check for modified fields
         */
        foreach ($originalType as $originalField) {
            if (!$result) {
                break;
            }
            $found = false;

            /*
             * Search for corresponding new field
             */
            foreach ($newType as $newField) {
                if ($newField["config"]["name"] == $originalField["config"]["name"]) {
                    $found = true;
                    break;
                }
            }

            // if no field found
            if (!$found) {
                $result = true;
            } else {
                if ($newField["cType"] != $originalField["cType"]) {
                    // Check if new cType is authorized with same name
                    if (in_array($originalField["cType"], $authorizedCtype["text"])) {
                        $result = in_array($newField["cType"], $authorizedCtype["text"]);
                    } elseif (in_array($originalField["cType"], $authorizedCtype["number"])) {
                        $result = in_array($newField["cType"], $authorizedCtype["number"]);
                    } elseif (in_array($originalField["cType"], $authorizedCtype["checkbox"])) {
                        $result = in_array($newField["cType"], $authorizedCtype["checkbox"]);
                    } else {
                        $result = false;
                    }
                } else {
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * Return localizable fields for content type
     *
     * @param string $cTypeId
     * @return array
     */
    public function getLocalizableFieldForCType($cTypeId)
    {
        $contentType = $this->findById($cTypeId);
        $fieldsDef = $contentType['fields'];

        $localizableFieldArray = array();
        $localizableFieldArray[] = 'text';
        $localizableFieldArray[] = 'urlSegment';
        $localizableFieldArray[] = 'summary';

        foreach ($fieldsDef as $fieldDef) {
            if (isset($fieldDef['config']['localizable']) && $fieldDef['config']['localizable'] == true) {
                $localizableFieldArray[] = $fieldDef['config']['name'];
            }
        }

        if (isset($contentType['CTType']) && in_array($contentType['CTType'], array('richText', 'simpleText'))) {
            $localizableFieldArray[] = 'body';
        }

        return $localizableFieldArray;
    }

    /**
     * Return variation fields for content type
     *
     * @param string $cTypeId
     * @return array
     */
    public function getVariationFieldForCType($cTypeId)
    {
        $contentType = $this->findById($cTypeId);
        $fieldsDef = $contentType['fields'];

        $variationFieldArray = array();

        foreach ($fieldsDef as $fieldDef) {
            if (isset($fieldDef['config']['useAsVariation']) && $fieldDef['config']['useAsVariation'] == true) {
                $variationFieldArray[] = $fieldDef['config']['name'];
            }
        }
        return $variationFieldArray;
    }

    protected function localizeOutput($obj, $alternativeFallBack = null)
    {
        $obj = parent::localizeOutput($obj, $alternativeFallBack);
        if (static::$workingLocale === null) {
            if (!isset($obj['nativeLanguage'])) {
                return $obj;
            } else {
                $locale = $obj['nativeLanguage'];
            }
        } else {
            $locale = static::$workingLocale;
        }
        if (isset($obj['fields'])) {
            foreach ($obj['fields'] as &$field) {
                if (isset($field['config']['i18n'][$locale]['fieldLabel'])) {
                    $field['config']['fieldLabel'] = $field['config']['i18n'][$locale]['fieldLabel'];
                }
            }
        }
        return $obj;
    }

    /**
     * Localize fiels tooltip and label in content type
     *
     * @param $contentTypeObj array Content type object to localize
     * @return array Localized content type
     * @throws Server Throw an exception when the content type object is not well formated
     */
    private function localizeContentTypeFields($contentTypeObj) {
        $almostOneSite = Manager::getService("Sites")->count();

        if($almostOneSite > 0 && self::$_isFrontEnd) {
            $site = Manager::getService("Sites")->getCurrent();

            $localizationStrategy = isset($site["locStrategy"]) ? $site["locStrategy"] : "onlyOne";

            if(!isset($site["defaultLanguage"]) || $site["defaultLanguage"] == "") {
                throw new Server("Missing key 'defaultLanguage' in site object");
            }

            $currentLanguage = Manager::getService("CurrentLocalization")->getCurrentLocalization();
            $fallbackLanguage = $site["defaultLanguage"];

            if(!isset($contentTypeObj["fields"]) || !is_array($contentTypeObj["fields"])) {
                $contentTypeObj["fields"] = [];
            }

            foreach($contentTypeObj["fields"] as &$field) {
                if(!isset($field["config"]["i18n"])) {
                    continue;
                }

                if($localizationStrategy == "onlyOne") {
                    if(!empty($field["config"]["i18n"][$currentLanguage]["fieldLabel"]) && is_string($field["config"]["i18n"][$currentLanguage]["fieldLabel"])) {
                        $field["config"]["fieldLabel"] = $field["config"]["i18n"][$currentLanguage]["fieldLabel"];
                    }

                    if(!empty($field["config"]["i18n"][$currentLanguage]["tooltip"]) && is_string($field["config"]["i18n"][$currentLanguage]["tooltip"])) {
                        $field["config"]["tooltip"] = $field["config"]["i18n"][$currentLanguage]["tooltip"];
                    }
                } elseif($localizationStrategy == "fallback") {
                    if(!empty($field["config"]["i18n"][$currentLanguage]["fieldLabel"]) && is_string($field["config"]["i18n"][$currentLanguage]["fieldLabel"])) {
                        $field["config"]["fieldLabel"] = $field["config"]["i18n"][$currentLanguage]["fieldLabel"];
                    } elseif(!empty($field["config"]["i18n"][$fallbackLanguage]["fieldLabel"]) && is_string($field["config"]["i18n"][$fallbackLanguage]["fieldLabel"])) {
                        $field["config"]["fieldLabel"] = $field["config"]["i18n"][$fallbackLanguage]["fieldLabel"];
                    }

                    if(!empty($field["config"]["i18n"][$currentLanguage]["tooltip"]) && is_string($field["config"]["i18n"][$currentLanguage]["tooltip"])) {
                        $field["config"]["tooltip"] = $field["config"]["i18n"][$currentLanguage]["tooltip"];
                    } elseif(!empty($field["config"]["i18n"][$fallbackLanguage]["tooltip"]) && is_string($field["config"]["i18n"][$fallbackLanguage]["tooltip"])) {
                        $field["config"]["tooltip"] = $field["config"]["i18n"][$fallbackLanguage]["tooltip"];
                    }
                }
            }
        }

        return $contentTypeObj;
    }
}
