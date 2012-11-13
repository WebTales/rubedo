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

use Rubedo\Interfaces\Collection\IAbstractCollection;
use Rubedo\Mongo\DataAccess;

/**
 * Class implementing the API to MongoDB
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
abstract class AbstractCollection implements IAbstractCollection
{

    protected $_collectionName;

    protected $_dataService;

    protected function _init() {
        // init the data access service
        $this->_dataService = new DataAccess();
        $this->_dataService->init($this->_collectionName);
    }

    public function __construct() {
        $this->_init();
    }

    /**
     * Do a find request on the current collection
     *
     * @return array
     */
    public function getList($filters = null, $sort = null) {
        if (isset($filters)) {
            foreach ($filters as $value) {
                if ((!(isset($value["operator"]))) || ($value["operator"] == "eq")) {
                    $this->_dataService->addFilter(array($value["property"] => $value["value"]));
                } else if ($value["operator"] == 'like') {
                    $this->_dataService->addFilter(array($value["property"] => array('$regex' => $this->_dataService->getRegex('/.*' . $value["value"] . '.*/i'))));
                }

            }
        }
        if (isset($sort)) {
            foreach ($sort as $value) {

                $this->_dataService->addSort(array($value["property"] => strtolower($value["direction"])));

            }
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

    /**
     * Find child of a node tree
     * @param $parentId id of the parent node
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
        }

        return $this->_dataService->readChild($parentId);
    }

}
