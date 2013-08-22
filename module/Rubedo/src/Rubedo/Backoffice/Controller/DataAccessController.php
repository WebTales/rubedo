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
namespace Rubedo\Backoffice\Controller;

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Rubedo\Collection\AbstractLocalizableCollection;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

/**
 * Abstract Controller providing CRUD API and dealing with the data access
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
abstract class DataAccessController extends AbstractActionController
{

    /**
     * Name of the store which is also to the collection name
     *
     * @var string
     */
    protected $_store;

    /**
     * Data Access Service
     *
     * @var IAbstractCollection
     */
    protected $_dataService;

    /**
     * should json be prettified
     *
     * @var bool
     */
    protected $_prettyJson = true;

    public function __construct()
    {}

    /**
     * Set the response body with Json content
     * Option : json is made human readable
     *
     * @param mixed $data
     *            data to be json encoded
     */
    protected function _returnJson($data)
    {
        $answer = new JsonModel($data);
        return $answer;
    }

    /**
     * The default read Action
     *
     * Return the content of the collection, get filters from the request
     * params, get sort from request params
     */
    public function indexAction()
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
        $startJson = $this->params()->fromQuery('start');
        if (isset($startJson)) {
            $start = Json::decode($startJson, Json::TYPE_ARRAY);
        } else {
            $start = null;
        }
        $limitJson = $this->params()->fromQuery('limit');
        if (isset($limitJson)) {
            $limit = Json::decode($limitJson, Json::TYPE_ARRAY);
        } else {
            $limit = null;
        }
        
        $mongoFilters = $this->_buildFilter($filters);
        
        $dataValues = $this->_dataService->getList($mongoFilters, $sort, $start, $limit);
        
        $response = array();
        $response['total'] = $dataValues['count'];
        $response['data'] = $dataValues['data'];
        $response['success'] = TRUE;
        $response['message'] = 'OK';
        
        return $this->_returnJson($response);
    }

    protected function _buildFilter($filters = null)
    {
        if (! $filters) {
            $filters = array();
        }
        $mongoFilters = Filter::factory();
        
        foreach ($filters as $filter) {
            if (isset($filter['operator']) && $filter['operator'] == 'like') {
                $mongoFilter = Filter::factory('Regex')->setName($filter['property'])->setValue('/.*' . $filter["value"] . '.*/i');
            } elseif (isset($filter['operator']) && $filter['operator'] == '$in' && $filter['property'] == 'id') {
                $mongoFilter = Filter::factory('InUid')->setValue($filter['value']);
            } elseif (isset($filter['operator']) && $filter['operator'] == '$nin' && $filter['property'] == 'id') {
                if (count($filter['value']) == 0) {
                    continue;
                }
                $mongoFilter = Filter::factory('NotInUid')->setValue($filter['value']);
            } elseif (isset($filter['operator']) && $filter['operator'] != 'eq') {
                $mongoFilter = Filter::factory('OperatorToValue')->setName($filter['property'])
                    ->setValue($filter['value'])
                    ->setOperator($filter['operator']);
            } elseif ($filter['property'] == 'id') {
                $mongoFilter = Filter::factory('Uid')->setValue($filter['value']);
            } else {
                $mongoFilter = Filter::factory('Value')->setName($filter['property'])->setValue($filter['value']);
            }
            $mongoFilters->addFilter($mongoFilter);
        }
        return $mongoFilters;
    }

    /**
     * read child action
     *
     * Return the children of a node
     */
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
        
        $mongoFilters = $this->_buildFilter($filters);
        $dataValues = $this->_dataService->readChild($parentId, $mongoFilters, $sort);
        
        $response = array();
        $response['children'] = array_values($dataValues);
        $response['total'] = count($response['children']);
        $response['success'] = TRUE;
        $response['message'] = 'OK';
        
        return $this->_returnJson($response);
    }

    /**
     * Delete all the children of the parent given in paremeter
     *
     * @return array
     */
    public function deleteChildAction()
    {
        $data = $this->params()->fromPost('data');
        
        if (! is_null($data)) {
            $data = Json::decode($data, Json::TYPE_ARRAY);
            
            if (is_array($data)) {
                
                $returnArray = $this->_dataService->deleteChild($data);
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'Not an array'
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => 'Invalid Data'
            );
        }
        if (! $returnArray['success']) {
            $this->getResponse()->setStatusCode(500);
        }
        
        return $this->_returnJson($returnArray);
    }

    /**
     * The read as tree Action
     *
     * Return the content of the collection, get filters from the request
     * params
     *
     * @todo remove the temp hack when database starter is ready
     */
    public function treeAction()
    {
        $filterJson = $this->params()->fromQuery('filter');
        if (isset($filterJson)) {
            $filters = Json::decode($filterJson, Json::TYPE_ARRAY);
        } else {
            $filters = null;
        }
        $mongoFilters = $this->_buildFilter($filters);
        $dataValues = $this->_dataService->readTree($mongoFilters);
        
        $response = array();
        $response["expanded"] = true;
        $response['children'] = $dataValues;
        $response['success'] = TRUE;
        $response['message'] = 'OK';
        
        return $this->_returnJson($response);
    }

    /**
     * The destroy action of the CRUD API
     */
    public function deleteAction()
    {
        $data = $this->params()->fromPost('data');
        
        if (! is_null($data)) {
            $data = Json::decode($data, Json::TYPE_ARRAY);
            if (is_array($data)) {
                
                $returnArray = $this->_dataService->destroy($data);
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'Not an array'
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => 'Invalid Data'
            );
        }
        if (! $returnArray['success']) {
            $this->getResponse()->setStatusCode(500);
        }
        return $this->_returnJson($returnArray);
    }

    /**
     * The create action of the CRUD API
     */
    public function createAction()
    {
        $data = $this->params()->fromPost('data');
        
        if (! is_null($data)) {
            $insertData = Json::decode($data, Json::TYPE_ARRAY);
            if (is_array($insertData)) {
                $returnArray = $this->_dataService->create($insertData);
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'Not an array'
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => 'No Data'
            );
        }
        if (! $returnArray['success']) {
            $this->getResponse()->setStatusCode(500);
        }
        return $this->_returnJson($returnArray);
    }

    /**
     * The update action of the CRUD API
     */
    public function updateAction()
    {
        $data = $this->params()->fromPost('data');
        
        if (! is_null($data)) {
            $updateData = Json::decode($data, Json::TYPE_ARRAY);
            if (is_array($updateData)) {
                
                $returnArray = $this->_dataService->update($updateData);
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'Not an array'
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => 'No Data'
            );
        }
        if (! $returnArray['success']) {
            $this->getResponse()->setStatusCode(500);
        }
        return $this->_returnJson($returnArray);
    }

    /**
     * Find an item given by its id
     *
     * @return Json_object
     */
    public function findOneAction()
    {
        $contentId = $this->params()->fromQuery('id');
        
        if (! is_null($contentId)) {
            
            $return = $this->_dataService->findById($contentId);
            
            if (empty($return['id'])) {
                
                $returnArray = array(
                    'success' => false,
                    "msg" => 'Object not found'
                );
            } else {
                
                $returnArray = array(
                    'succes' => true,
                    'data' => $return
                );
            }
        } else {
            
            $returnArray = array(
                'success' => false,
                "msg" => 'Missing param'
            );
        }
        
        return $this->_returnJson($returnArray);
    }

    public function modelAction()
    {
        $model = $this->_dataService->getModel();
        return $this->_returnJson($model);
    }
}
