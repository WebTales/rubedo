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

namespace RubedoAPI\Collection;

use Rubedo\Collection\AbstractCollection;
use RubedoAPI\Exceptions\APIEntityException;
use WebTales\MongoFilters\Filter;
use Rubedo\Services\Manager;

/**
 * Class ApiCache
 * @package RubedoAPI\Collection
 */
class ApiCache extends AbstractCollection
{
    protected $_indexes = array(
        array(
            'keys' => array(
                'cacheId' => 1
            ),
            'options' => array(
                'unique' => true
            )
        ),
        array(
            'keys' => array(
                'entity' => 1
            )
        ),
        array(
            'keys' => array(
                'expireAt' => 1
            ),
            'options' => array(
                'expireAfterSeconds' => 0
            )
        )
    );
    /**
     * Complete collection properties
     */
    public function __construct()
    {
        $this->_collectionName = 'ApiCache';
        parent::__construct();


    }

    public function findByCacheId($cacheId)
    {

        $Filters = Filter::factory('And');
        $Filter = Filter::factory('Value')->setName('cacheId')->setValue($cacheId);
        $Filters->addFilter($Filter);
        return $this->_dataService->findOne($Filters);
    }

    public function upsertByCacheId($obj, $cacheId)
    {
        $options = array();
        $options['upsert'] = true;
        $options["w"]=0;
        $updateCond = Filter::factory('Value');
        $updateCond->setName('cacheId')->setValue($cacheId);
        $result = $this->_dataService->customUpdate($obj, $updateCond, $options);
        if ($result['success']) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteByCacheId($id)
    {
        $updateCond = Filter::factory('Value');
        $updateCond->setName('cacheId')->setValue($id);
        $options = array();
        $result = $this->_dataService->customDelete($updateCond, $options);
        if (!empty($result['ok'])) {
            return true;
        } else {
            return false;
        }
    }

    public function clearForEntity ($id){
        if (!empty($id)&&$id!=""){
            $updateCond = Filter::factory('Value');
            $updateCond->setName('entity')->setValue($id);
            $options = array("w"=>0);
            $this->_dataService->customDelete($updateCond, $options);
        }
    }


}
