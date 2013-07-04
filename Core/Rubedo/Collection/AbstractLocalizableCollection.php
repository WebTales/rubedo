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

use Rubedo\Interfaces\Collection\IAbstractCollection, Rubedo\Services\Manager, WebTales\MongoFilters\Filter;

/**
 * Class implementing the API to MongoDB for localizable collections
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
abstract class AbstractLocalizableCollection extends AbstractCollection
{

    /**
     * Current service locale
     * 
     * @var string null
     */
    protected $locale = null;

    /**
     * Do a find request on the current collection
     *
     * @param array $filters
     *            filter the list with mongo syntax
     * @param array $sort
     *            sort the list with mongo syntax
     * @return array
     */
    public function getList(\WebTales\MongoFilters\IFilter $filters = null, $sort = null, $start = null, $limit = null, $locale = null)
    {
        if ($locale) {
            $this->locale = $locale;
        }
        
        $dataValues = parent::getList($filters, $sort, $start, $limit);
        if ($dataValues && is_array($dataValues)) {
            foreach ($dataValues['data'] as &$obj) {
                $obj = $this->localizeOutput($obj, $this->locale);
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
    public function findById($contentId, $forceReload = false, $locale = null)
    {
        if ($locale) {
            $this->locale = $locale;
        }
        $obj = parent::findById($contentId, $forceReload);
        return $this->localizeOutput($obj, $this->locale);
    }

    /**
     * Find an item given by its name (find only one if many)
     *
     * @param string $name            
     * @return array
     */
    public function findByName($name, $locale = null)
    {
        if($locale){
            $this->locale = $locale;
        }
        $obj = parent::findByName($name);
        return $this->localizeOutput($obj, $this->locale);
    }

    /**
     * Do a findone request
     *
     * @param \WebTales\MongoFilters\IFilter $value
     *            search condition
     * @return array
     */
    public function findOne(\WebTales\MongoFilters\IFilter $value, $locale = null)
    {
        if($locale){
            $this->locale = $locale;
        }
        $obj = parent::findOne($value);
        return $this->localizeOutput($obj, $this->locale);
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
        return $this->_dataService->create($obj, $options);
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
        return $this->_dataService->update($obj, $options);
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
    public function readChild($parentId,\WebTales\MongoFilters\IFilter $filters = null, $sort = null, $locale = null)
    {
        if($locale){
            $this->locale = $locale;
        }
        $result = parent::readChild($parentId, $filters, $sort);
        if ($result && is_array($result)) {
            foreach ($result as &$obj) {
                $obj = $this->localizeOutput($obj, $this->locale);
            }
        }
        return $result;
    }

    public function readTree(\WebTales\MongoFilters\IFilter $filters = null, $locale = null)
    {
        if($locale){
            $this->locale = $locale;
        }
        $tree = $this->_dataService->readTree($filters);
        return $tree['children'];
    }

    /**
     *
     * @param array $obj
     *            collection item
     * @param string $locale
     *            locale code for translation
     * @return array collection item localized
     */
    protected function localizeOutput($obj, $locale = null)
    {
        return $obj;
    }

    /**
     *
     * @param array $obj
     *            collection item
     * @param string $locale
     *            locale code for translation
     * @return array collection item localized
     */
    protected function localizeInput($obj, $locale = null)
    {
        return $obj;
    }
}
	
