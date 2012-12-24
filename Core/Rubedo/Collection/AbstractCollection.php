<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IAbstractCollection;
use Rubedo\Mongo\DataAccess;
use Rubedo\Services\Manager;

/**
 * Class implementing the API to MongoDB
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
abstract class AbstractCollection implements IAbstractCollection
{
    /**
     * name of the collection
     *
     * @var string
     */
    protected $_collectionName;

    /**
     * data access service
     *
     * @var\Rubedo\Mongo\DataAccess
     */
    protected $_dataService;

    protected function _init() {
        // init the data access service
        $this->_dataService = Manager::getService('MongoDataAccess');
        $this->_dataService->init($this->_collectionName);
    }

    public function __construct() {
        $this->_init();
    }

    /**
     * Do a find request on the current collection
     *
     * @param array $filters filter the list with mongo syntax
     * @param array $sort sort the list with mongo syntax
     * @return array
     */
    public function getList($filters = null, $sort = null, $start = null, $limit = null) {
        if (isset($filters)) {
            foreach ($filters as $value) {
                if ((!(isset($value["operator"]))) || ($value["operator"] == "eq")) {
                    $this->_dataService->addFilter(array($value["property"] => $value["value"]));
                } else if ($value["operator"] == 'like') {
                    $this->_dataService->addFilter(array($value["property"] => array('$regex' => $this->_dataService->getRegex('/.*' . $value["value"] . '.*/i'))));
                } elseif (isset($value["operator"])) {
                    $this->_dataService->addFilter(array($value["property"] => array($value["operator"] => $value["value"])));
                }

            }
        }
        if (isset($sort)) {
            foreach ($sort as $value) {

                $this->_dataService->addSort(array($value["property"] => strtolower($value["direction"])));

            }
        }
        if (isset($start)) {
            $this->_dataService->setFirstResult($start);
        }
        if (isset($limit)) {
            $this->_dataService->setNumberOfResults($limit);
        }

        $dataValues = $this->_dataService->read();

        return $dataValues;

    }

    /**
     * Find an item given by its literral ID
     * @param string $contentId
     * @return array
     */
    public function findById($contentId) {
        return $this->_dataService->findById($contentId);
    }

    /**
     * Find an item given by its name (find only one if many)
     * @param string $name
     * @return array
     */
    public function findByName($name) {
        return $this->_dataService->findByName($name);
    }

    /**
     * Create an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::create
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function create(array $obj, $safe = true) {
        return $this->_dataService->create($obj, $safe);
    }

    /**
     * Update an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::update
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function update(array $obj, $safe = true) {
        return $this->_dataService->update($obj, $safe);
    }

    /**
     * Delete objets in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::destroy
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function destroy(array $obj, $safe = true) {
        return $this->_dataService->destroy($obj, $safe);
    }

    public function customDelete($deleteCond, $safe = true) {
        return $this->_dataService->customDelete($deleteCond, $safe);
    }

    /**
     * Find child of a node tree
     * @param string $parentId id of the parent node
     * @param array $filters array of data filters (mongo syntax)
     * @param array $sort  array of data sorts (mongo syntax)
     * @return array children array
     */
    public function readChild($parentId, $filters = null, $sort = null) {
        if (isset($filters)) {
            foreach ($filters as $value) {
                if ((!(isset($value["operator"]))) || ($value["operator"] == "eq")) {
                    $this->_dataService->addFilter(array($value["property"] => $value["value"]));
                } else if ($value["operator"] == 'like') {
                    $this->_dataService->addFilter(array($value["property"] => array('$regex' => new \MongoRegex('/.*' . $value["value"] . '.*/i'))));
                }
            }
        }

        if (isset($sort)) {
            foreach ($sort as $value) {
                $this->_dataService->addSort(array($value["property"] => strtolower($value["direction"])));
            }
        } else {
            $this->_dataService->addSort(array("orderValue" => 1));
        }

        return $this->_dataService->readChild($parentId);
    }

}
