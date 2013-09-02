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
namespace Rubedo\Collection;

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;

/**
 * Class implementing the API to MongoDB for localizable collections
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
abstract class AbstractLocalizableCollection extends AbstractCollection
{

    protected static $defaultLocale = 'en';

    protected static $labelField = 'text';

    protected static $localizationStrategy = "all";

    protected static $fallbackLocale;

    /**
     * Contain common fields
     */
    protected static $globalNonLocalizableFields = array(
        '_id',
        'id',
        'idLabel',
        'createTime',
        'createUser',
        'lastUpdateTime',
        'lastUpdateUser',
        'lastPendingTime',
        'lastPendingUser',
        'version',
        'online',
        'nativeLanguage',
        'i18n',
        'workspace',
        'orderValue',
        'parentId',
        'text'
    );

    protected static $nonLocalizableFields = array();

    /**
     * Current service locale
     *
     * @var string null
     */
    protected static $workingLocale = null;

    protected static $includeI18n = true;

    protected static $isLocaleFiltered = false;
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::_init()
     */
    protected function _init()
    {
        parent::_init();
        
        $this->initLocaleFilter();
    }
    
    protected function initLocaleFilter(){
        if (static::$isLocaleFiltered) {
            switch (static::$localizationStrategy) {
                case 'onlyOne':
                    $this->_dataService->addFilter(Filter::factory('OperatorToValue')->setName('i18n.' . static::$workingLocale)
                    ->setOperator('$exists')
                    ->setValue(true));
                    break;
                case 'fallback':
                    $fallbackLocale = isset(static::$fallbackLocale) ? static::$fallbackLocale : static::$defaultLocale;
                    if ($fallbackLocale != static::$workingLocale) {
                        $orFilter = Filter::factory('Or');
                        $orFilter->addFilter(Filter::factory('OperatorToValue')->setName('i18n.' . static::$workingLocale)
                            ->setOperator('$exists')
                            ->setValue(true));
                        $orFilter->addFilter(Filter::factory('OperatorToValue')->setName('i18n.' . $fallbackLocale)
                            ->setOperator('$exists')
                            ->setValue(true));
                        $this->_dataService->addFilter($orFilter);
                    } else {
                        $this->_dataService->addFilter(Filter::factory('OperatorToValue')->setName('i18n.' . static::$workingLocale)
                            ->setOperator('$exists')
                            ->setValue(true));
                    }
    
                    break;
                default:
                    $localeArray = Manager::getService('Languages')->getActiveLocales();
                    $orFilter = Filter::factory('Or');
                    foreach($localeArray as $locale){
                        $orFilter->addFilter(Filter::factory('OperatorToValue')->setName('i18n.' . $locale)
                            ->setOperator('$exists')
                            ->setValue(true));
                    }
                    
                    $this->_dataService->addFilter($orFilter);
                    break;
            }
        }
    }

    /**
     * Do a find request on the current collection
     *
     * @param array $filters
     *            filter the list with mongo syntax
     * @param array $sort
     *            sort the list with mongo syntax
     * @return array
     */
    public function getList(\WebTales\MongoFilters\IFilter $filters = null, $sort = null, $start = null, $limit = null)
    {
        $dataValues = parent::getList($filters, $sort, $start, $limit);
        if ($dataValues && is_array($dataValues)) {
            foreach ($dataValues['data'] as &$obj) {
                $obj = $this->localizeOutput($obj);
            }
        }
        
        return $dataValues;
    }

    /**
     * Find an item given by its literral ID
     *
     * @param string $contentId            
     * @param boolean $forceReload
     *            should we ensure reading up-to-date content
     * @return array
     */
    public function findById($contentId, $forceReload = false)
    {
        $obj = parent::findById($contentId, $forceReload);
        return $this->localizeOutput($obj);
    }

    /**
     * Find an item given by its name (find only one if many)
     *
     * @param string $name            
     * @return array
     */
    public function findByName($name)
    {
        $obj = parent::findByName($name);
        return $this->localizeOutput($obj);
    }

    /**
     * Do a findone request
     *
     * @param \WebTales\MongoFilters\IFilter $value
     *            search condition
     * @return array
     */
    public function findOne(\WebTales\MongoFilters\IFilter $value)
    {
        $obj = parent::findOne($value);
        return $this->localizeOutput($obj);
    }

    /**
     * Create an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::create
     * @param array $obj
     *            data object
     * @param array $options            
     * @return array
     */
    public function create(array $obj, $options = array())
    {
        $this->_filterInputData($obj);
        
        unset($obj['readOnly']);
        
        $obj = $this->localizeInput($obj);
        $result = $this->_dataService->create($obj, $options);
        if ($result['success']) {
            $result['data'] = $this->localizeOutput($result['data']);
        }
        return $result;
    }

    /**
     * Update an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::update
     * @param array $obj
     *            data object
     * @param array $options            
     * @return array
     */
    public function update(array $obj, $options = array())
    {
        unset($obj['readOnly']);
        $obj = $this->localizeInput($obj);
        $result = $this->_dataService->update($obj, $options);
        if ($result['success']) {
            $result['data'] = $this->localizeOutput($result['data']);
        }
        return $result;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Interfaces\Collection\IAbstractCollection::count()
     */
    public function count(\WebTales\MongoFilters\IFilter $filters = null)
    {
        return $this->_dataService->count($filters);
    }

    /**
     * Find child of a node tree
     *
     * @param string $parentId
     *            id of the parent node
     * @param \WebTales\MongoFilters\IFilter $filters
     *            array of data filters (mongo syntax)
     * @param array $sort
     *            array of data sorts (mongo syntax)
     * @return array children array
     */
    public function readChild($parentId, \WebTales\MongoFilters\IFilter $filters = null, $sort = null)
    {
        $result = parent::readChild($parentId, $filters, $sort);
        if ($result && is_array($result)) {
            foreach ($result as &$obj) {
                $obj = $this->localizeOutput($obj);
            }
        }
        return $result;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::readTree()
     * @todo add parse for localization
     */
    public function readTree(\WebTales\MongoFilters\IFilter $filters = null)
    {
        $tree = $this->_dataService->readTree($filters);
        $tree = $this->adaptTree($tree);
        return $tree['children'];
    }
    
    protected function adaptTree($tree){
        $children = $tree['children'];
        $tree['children'] = array();
        $tree = $this->localizeOutput($tree);
        foreach ($children as $child){
            $tree['children'][] = $this->adaptTree($child);
        }
        return $tree;
    }

    /**
     *
     *
     *
     * Update item with localized content as fields.
     *
     * @param array $obj
     *            collection item
     * @return array collection item localized
     */
    protected function localizeOutput($obj, $alternativeFallBack = null)
    {
        if ($obj === null) {
            return $obj;
        }
        if (! isset($obj['i18n'])) {
            return $obj;
        }
        if (static::$workingLocale === null) {
            if (! isset($obj['nativeLanguage'])) {
                return $obj;
            } else {
                $locale = $obj['nativeLanguage'];
            }
        } else {
            $locale = static::$workingLocale;
        }
        
        if ($alternativeFallBack === null && static::$isLocaleFiltered && static::$localizationStrategy == 'fallback') {
            $alternativeFallBack = static::$fallbackLocale;
        }
        
        if (! isset($obj['nativeLanguage'])) {
            throw new \Rubedo\Exceptions\Server('No defined native language for this item');
        }
        
        //Choose the good language for the content
        if(isset($locale) && isset($obj['i18n'][$locale])) {
            $obj = $this->merge($obj, $obj['i18n'][$locale]);
            $obj['locale'] = $locale;
        } else {
            if($this->getFallbackLocale() !== null && isset($obj['i18n'][$this->getFallbackLocale()])) {
                $obj = $this->merge($obj, $obj['i18n'][$this->getFallbackLocale()]);
                $obj['locale'] = $this->getFallbackLocale();
            } else {
                $obj['locale'] = $obj["nativeLanguage"];
                return $obj;
            }
        }
        
        if ($locale != $obj['nativeLanguage']) {
            if (isset($obj['i18n'][$locale])) {
                $obj = $this->merge($obj, $obj['i18n'][$locale]);
                $obj['locale'] = $locale;
//                 if(static::$_isFrontEnd){
//                     $obj[static::$labelField]=$obj['i18n'][$locale][static::$labelField];
//                 }
            } elseif (isset($alternativeFallBack) && isset($obj['i18n'][$alternativeFallBack])) {
                $obj = $this->merge($obj, $obj['i18n'][$alternativeFallBack]);
                $obj['locale'] = $alternativeFallBack;
//                 if(static::$_isFrontEnd){
//                     $obj[static::$labelField]=$obj['i18n'][$alternativeFallBack][static::$labelField];
//                 }
            }
        }
        
        if (! static::$includeI18n) {
            unset($obj['i18n']);
        }
        
        return $obj;
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
        foreach ($array2 as $key => $value) {
            if (isset($array1[$key]) && is_array($value) && ! $this->isNumericArray($value)) {
                $array1[$key] = $this->merge($array1[$key], $array2[$key]);
            } else {
                $array1[$key] = $value;
            }
        }
        return $array1;
    }

    /**
     * return true for array
     *
     * @param array $array            
     * @return boolean
     */
    protected function isNumericArray($array)
    {
        return $array === array_values($array);
    }

    /**
     *
     * @param array $obj
     *            collection item
     * @return array collection item localized
     */
    protected function localizeInput($obj)
    {
        $metadataFields = $this->getMetaDataFields();
        // force label to contain only native title in DB
        if (isset($obj['i18n'])) {
            if (isset($obj['i18n'][$obj['nativeLanguage']][static::$labelField])) {
                $obj[static::$labelField] = $obj['i18n'][$obj['nativeLanguage']][static::$labelField];
            }
        }
        
        
        // prevent localizable data to be stored in root level
        foreach ($obj as $key => $field) {
            if (! in_array($key, $metadataFields) && $key !== static::$labelField) {
                unset($obj[$key]);
            }
        }
        
        // prevent non localizable data to be store in localization document
        if (isset($obj['i18n'])) {
            foreach ($obj['i18n'] as $locale => $localization) {
                foreach ($localization as $key => $value) {
                    if (in_array($key, $metadataFields) && $key !== static::$labelField) {
                        unset($obj['i18n'][$locale][$key]);
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
        $nativeContent = $obj;
        
        foreach ($this->getMetaDataFields() as $metaField) {
            if ($metaField !== static::$labelField) {
                unset($nativeContent[$metaField]);
            }
            $nativeContent['locale'] = static::$defaultLocale;
        }
        foreach ($obj as $key => $field) {
            if (! in_array($key, $this->getMetaDataFields())) {
                unset($obj[$key]);
            }
        }
        $obj['nativeLanguage'] = static::$defaultLocale;
        $obj['i18n'] = array(
            static::$defaultLocale => $nativeContent
        );
        return $obj;
    }

    /**
     * Localize not yet localized items of the current collection
     */
    public function addLocalizationForCollection()
    {
        $wasFiltered = parent::disableUserFilter();
        $this->_dataService->clearFilter();
        $items = parent::getList(Filter::factory('OperatorToValue')->setName('nativeLanguage')
            ->setOperator('$exists')
            ->setValue(false));
        if ($items['count'] > 0) {
            foreach ($items['data'] as $item) {
                if (preg_match('/[\dabcdef]{24}/', $item['id']) == 1) {
                    $item = $this->addlocalization($item);
                    // $service->customUpdate($item, Filter::factory('Uid')->setValue($item['id']));
                    $this->update($item);
                }
            }
        }
        
        parent::disableUserFilter($wasFiltered);
    }

    /**
     * Ensure that every localizable collection is fully localized
     */
    public static function localizeAllCollection()
    {
        self::setDefaultLocale(Manager::getService('Languages')->getDefaultLanguage());
        
        $services = \Rubedo\Interfaces\config::getCollectionServices();
        foreach ($services as $serviceName) {
            $service = Manager::getService($serviceName);
            if ($service instanceof AbstractLocalizableCollection) {
                $service->addLocalizationForCollection();
            }
        }
    }

    /**
     *
     * @return the $defaultLocal
     */
    public static function getDefaultLocale()
    {
        return AbstractLocalizableCollection::$defaultLocale;
    }

    /**
     *
     * @param string $defaultLocal            
     */
    public static function setDefaultLocale($defaultLocal)
    {
        AbstractLocalizableCollection::$defaultLocale = $defaultLocal;
    }

    /**
     *
     * @return the $includeI18n
     */
    public static function getIncludeI18n()
    {
        return AbstractLocalizableCollection::$includeI18n;
    }

    /**
     *
     * @param boolean $includeI18n            
     */
    public static function setIncludeI18n($includeI18n)
    {
        AbstractLocalizableCollection::$includeI18n = $includeI18n;
    }

    /**
     *
     * @return the $workingLocale
     */
    public static function getWorkingLocale()
    {
        return AbstractLocalizableCollection::$workingLocale;
    }

    /**
     *
     * @param string $workingLocale            
     */
    public static function setWorkingLocale($workingLocale)
    {
        AbstractLocalizableCollection::$workingLocale = $workingLocale;
    }

    /**
     * Return non localizable fields for the current collection
     *
     * @return array
     */
    protected function getMetaDataFields()
    {
        if (! isset($this->metaDataFields)) {
            $this->metaDataFields = array_merge(self::$globalNonLocalizableFields, static::$nonLocalizableFields);
        }
        return $this->metaDataFields;
    }

    /**
     *
     * @return the $localizationStrategy
     */
    public static function getLocalizationStrategy()
    {
        return AbstractLocalizableCollection::$localizationStrategy;
    }

    /**
     *
     * @param string $localizationStrategy            
     */
    public static function setLocalizationStrategy($localizationStrategy)
    {
        AbstractLocalizableCollection::$localizationStrategy = $localizationStrategy;
    }

    /**
     *
     * @return the $fallbackLocale
     */
    public static function getFallbackLocale()
    {
        return AbstractLocalizableCollection::$fallbackLocale;
    }

    /**
     *
     * @param field_type $fallbackLocale            
     */
    public static function setFallbackLocale($fallbackLocale)
    {
        AbstractLocalizableCollection::$fallbackLocale = $fallbackLocale;
    }
}
	
