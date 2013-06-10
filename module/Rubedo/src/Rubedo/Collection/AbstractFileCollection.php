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

use Rubedo\Interfaces\Collection\IAbstractFileCollection, Rubedo\Services\Manager;

/**
 * Class implementing the API to MongoDB GridFS
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
abstract class AbstractFileCollection implements IAbstractFileCollection
{

    /**
     * data access service
     *
     * @var \Rubedo\Mongo\FileAccess
     */
    protected $_dataService;

    protected function _init ()
    {
        // init the data access service
        $this->_dataService = Manager::getService('MongoFileAccess');
        $this->_dataService->init();
    }

    public function __construct ()
    {
        $this->_init();
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
    public function getList ($filters = null, $sort = null, $start = null, $limit = null)
    {
        if (isset($sort)) {
            foreach ($sort as $value) {
                
                $this->_dataService->addSort(array(
                    $value["property"] => strtolower($value["direction"])
                ));
            }
        }
        if (isset($start)) {
            $this->_dataService->setFirstResult($start);
        }
        if (isset($limit)) {
            $this->_dataService->setNumberOfResults($limit);
        }
        
        $dataValues = $this->_dataService->read($filters);
        
        return $dataValues;
    }

    /**
     * Find an item given by its literral ID
     *
     * @param string $contentId            
     * @return array
     */
    public function findById ($contentId)
    {
        return $this->_dataService->findById($contentId);
    }

    /**
     * Find an item given by its name (find only one if many)
     *
     * @param string $name            
     * @return array
     */
    public function findByName ($name)
    {
        return $this->_dataService->findByName($name);
    }

    /**
     * Create an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::create
     * @param array $obj
     *            data object
     * @return array
     */
    public function create (array $obj, $options = array())
    {
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
    public function update (array $obj, $options = array())
    {
        return $this->_dataService->update($obj, $options);
    }

    /**
     * Delete objets in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::destroy
     * @param array $obj
     *            data object
     * @param array $options            
     * @return array
     */
    public function destroy (array $obj, $options = array())
    {
        return $this->_dataService->destroy($obj, $options);
    }
}
