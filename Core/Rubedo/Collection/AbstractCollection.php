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

    /**
     * Do a find request on the current collection
     *
     * @return array
     */
    public function getList($filters = null, $sort = null) {
        // init the data access service
        $dataService = new DataAccess();
        $dataService->init('Users');

        if (isset($filters)) {
            foreach ($filters as $value) {
                if ((!(isset($value["operator"]))) || ($value["operator"] == "eq")) {
                    $dataService->addFilter(array($value["property"] => $value["value"]));
                } else if ($value["operator"] == 'like') {
                    $dataService->addFilter(array($value["property"] => array('$regex' => new \MongoRegex('/.*' . $value["value"] . '.*/i'))));
                }

            }
        }
        if (isset($sort)) {
            foreach ($sort as $value) {

                $dataService->addSort(array($value["property"] => strtolower($value["direction"])));

            }
        }
        $dataValues = $dataService->read();
		
		return $dataValues;

    }

    /**
     * Find an item given by its literral ID
     * @param string $contentId
     * @return array
     */
    public function findById($contentId) {
        //return $this->findOne(array('_id' => new \MongoId($contentId)));
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

    }

}
