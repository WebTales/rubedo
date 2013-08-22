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
use Zend\Json\Json;
use Zend\View\Model\JsonModel;


/**
 * Controller providing CRUD API for the field types JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class ContentsController extends DataAccessController
{

    public function __construct ()
    {
        parent::__construct();
        
        // init the data access service
        $this->_dataService = Manager::getService('Contents');
    }

    /**
     * The default read Action
     *
     * Return the content of the collection, get filters from the request
     * params, get sort from request params
     */
    public function indexAction ()
    {
        // merge filter and tFilter
        $jsonFilter = $this->params()->fromQuery('filter', '[]');
        $jsonTFilter = $this->params()->fromQuery('tFilter', '[]');
        $filterArray = Json::decode($jsonFilter,Json::TYPE_ARRAY);
        $tFilterArray = Json::decode($jsonTFilter,Json::TYPE_ARRAY);
        
        $filters = array_merge($tFilterArray, $filterArray);
        $mongoFilters = $this->_buildFilter($filters);
                
        $sort = Json::decode($this->params()->fromQuery('sort', null),Json::TYPE_ARRAY);
        $start = Json::decode($this->params()->fromQuery('start', null),Json::TYPE_ARRAY);
        $limit = Json::decode($this->params()->fromQuery('limit', null),Json::TYPE_ARRAY);
        
        $dataValues = $this->_dataService->getList($mongoFilters, $sort, $start, $limit, false);
        $response = array();
        $response['total'] = $dataValues['count'];
        $response['data'] = $dataValues['data'];
        $response['success'] = TRUE;
        $response['message'] = 'OK';
        
        return $this->_returnJson($response);
    }

    /**
     * read child action
     *
     * Return the children of a node
     */
    public function readChildAction ()
    {
        $filterJson = $this->params()->fromQuery('filter');
        if (isset($filterJson)) {
            $filters = Json::decode($filterJson,Json::TYPE_ARRAY);
        } else {
            $filters = null;
        }
        $sortJson = $this->params()->fromQuery('sort');
        if (isset($sortJson)) {
            $sort = Json::decode($sortJson,Json::TYPE_ARRAY);
        } else {
            $sort = null;
        }
        
        $parentId = $this->params()->fromQuery('node', 'root');
        $mongoFilters = $this->_buildFilter($filters);
        $dataValues = $this->_dataService->readChild($parentId, $mongoFilters, $sort, false);
        
        $response = array();
        $response['children'] = array_values($dataValues);
        $response['total'] = count($response['children']);
        $response['success'] = TRUE;
        $response['message'] = 'OK';
        
        return $this->_returnJson($response);
    }

    /**
     * The create action of the CRUD API
     */
    public function createAction ()
    {
        $data = $this->params()->fromPost('data');
        
        if (! is_null($data)) {
            $insertData = Json::decode($data,Json::TYPE_ARRAY);
            if (is_array($insertData)) {
                $insertData["target"] = isset($insertData["target"]) ? $insertData["target"] : array();
                $returnArray = $this->_dataService->create($insertData, array(), false);
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
    public function updateAction ()
    {
        $data = $this->params()->fromPost('data');
        
        if (! is_null($data)) {
            $updateData = Json::decode($data,Json::TYPE_ARRAY);
            if (is_array($updateData)) {
                
                $returnArray = $this->_dataService->update($updateData, array(), false);
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
     * Do a findOneAction
     */
    public function findOneAction ()
    {
        $contentId = $this->params()->fromQuery('id');
        
        if (! is_null($contentId)) {
            
            $return = $this->_dataService->findById($contentId, false, false);
            
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

    /**
     * Return a list of ordered objects
     */
    public function getOrderedListAction ()
    {
        // merge filter and tFilter
        $jsonFilter = $this->params()->fromQuery('filter', '[]');
        $jsonTFilter = $this->params()->fromQuery('tFilter', '[]');
        $filterArray = Json::decode($jsonFilter,Json::TYPE_ARRAY);
        $tFilterArray = Json::decode($jsonTFilter,Json::TYPE_ARRAY);
        
        $filters = array_merge($tFilterArray, $filterArray);
        $sort = Json::decode($this->params()->fromQuery('sort', null),Json::TYPE_ARRAY);
        $start = Json::decode($this->params()->fromQuery('start', null),Json::TYPE_ARRAY);
        $limit = Json::decode($this->params()->fromQuery('limit', null),Json::TYPE_ARRAY);
        
        $mongoFilters = $this->_buildFilter($filters);
        return new JsonModel($this->_dataService->getOrderedList($mongoFilters, $sort, $start, $limit, false));
    }

    public function clearOrphanContentsAction ()
    {
        $result = $this->_dataService->clearOrphanContents();
        
        return $this->_returnJson($result);
    }

    public function countOrphanContentsAction ()
    {
        $result = $this->_dataService->countOrphanContents();
        
        return $this->_returnJson($result);
    }

    public function deleteByContentTypeIdAction ()
    {
        $typeId = $this->params()->fromPost('type-id');
        if (! $typeId) {
            throw new \Rubedo\Exceptions\User('This action needs a type-id as argument.', 'Exception3');
        }
        $deleteResult = $this->_dataService->deleteByContentType($typeId);
        
        return $this->_returnJson($deleteResult);
    }
}
