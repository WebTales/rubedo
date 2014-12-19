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
namespace Rubedo\Backoffice\Controller;

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\Debug\Debug;
use Zend\Json\Json;

/**
 * Controller providing CRUD API for the Directories JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 *
 */
class DirectoriesController extends DataAccessController
{

    public function __construct()
    {
        parent::__construct();

        // init the data access service
        $this->_dataService = Manager::getService('Directories');
    }

    /**
     * Clear orphan terms in the collection
     *
     * @return array Result of the request
     */
    public function clearOrphanDirectoriesAction()
    {
        $result = $this->_dataService->clearOrphanPages();

        return $this->_returnJson($result);
    }

    public function countOrphanDirectoriesAction()
    {
        $result = $this->_dataService->countOrphanPages();

        return $this->_returnJson($result);
    }

    public function classifyAction()
    {
        $encodedArray = $this->params()->fromPost("mediaArray", "[ ]");
        $decodedArray = Json::decode($encodedArray, Json::TYPE_ARRAY);
        $directoryId = $this->params()->fromPost("directoryId");
        $result = $this->_dataService->classify($decodedArray, $directoryId);
        return $this->_returnJson($result);
    }

    public function readChildAction()
    {
        $filterJson = $this->params()->fromQuery('filter');
        if (isset($filterJson)) {
            $filters = Json::decode($filterJson, Json::TYPE_ARRAY);
        } else {
            $filters = null;
        }
        $sortJson = $this->params()->fromQuery('sort');
        if (isset($sortJson)) {
            $sort = Json::decode($sortJson, Json::TYPE_ARRAY);
        } else {
            $sort = null;
        }

        $parentId = $this->params()->fromQuery('node', 'root');
        $themeMode = $this->params()->fromQuery('themeMode', false);
        $mongoFilters = $this->_buildFilter($filters);
        $allowVirtual = true;
        if ($parentId == "root") {
            if ($themeMode) {
                $mongoFilters->addFilter(Filter::factory("Value")->setName("text")->setValue("theme"));
                $allowVirtual = false;
            } else {
                $mongoFilters->addFilter(Filter::factory("OperatorToValue")->setName("text")->setOperator('$ne')->setValue("theme"));
            }
        }
        $dataValues = $this->_dataService->readChild($parentId, $mongoFilters, $sort, $allowVirtual);

        $response = array();
        $response['children'] = array_values($dataValues);
        $response['total'] = count($response['children']);
        $response['success'] = TRUE;
        $response['message'] = 'OK';

        return $this->_returnJson($response);
    }

}