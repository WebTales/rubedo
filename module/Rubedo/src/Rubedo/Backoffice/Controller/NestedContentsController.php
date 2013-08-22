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

use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;
use Zend\Json\Json;


/**
 * Controller providing CRUD API for the nested contents
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class NestedContentsController extends AbstractActionController
{

    /**
     * Parent content Id
     *
     * @var string
     */
    protected $_parentId;

    /**
     * should json be prettified
     *
     * @var bool
     */
    protected $_prettyJson = true;

    /**
     * Array with the read only actions
     */
    protected $_readOnlyAction = array(
        'index'
    );

    /**
     * Disable layout & rendering, set content type to json
     * init the store parameter if transmitted
     *
     * @see AbstractActionController::init()
     */
    public function __construct ()
    {
        // init the data access service
        $this->_dataService = Manager::getService('NestedContents');
        
        $this->_parentId = $this->getRequest()->getParam('parentId');
        
        if (! isset($this->_parentId)) {
            $response = array();
            $response['success'] = false;
            $response['message'] = 'no parentId Given';
            $this->getResponse()->setHttpResponseCode(500);
            return $this->_returnJson($response);
        }
        
        $sessionService = Manager::getService('Session');
        
        // refuse write action not send by POST
        if (! $this->getRequest()->isPost() && ! in_array($this->getRequest()->getActionName(), $this->_readOnlyAction)) {
            throw new \Rubedo\Exceptions\Access("You can't call a write action with a GET request", "Exception5");
        } else {
            if (! in_array($this->getRequest()->getActionName(), $this->_readOnlyAction)) {
                $user = $sessionService->get('user');
                $token = $this->getRequest()->getParam('token');
                
                if ($token !== $user['token']) {
                    throw new \Rubedo\Exceptions\Access("The token given in the request doesn't match with the token in session", "Exception6");
                }
            }
        }
    }

    /**
     * Set the response body with Json content
     * Option : json is made human readable
     * 
     * @param mixed $data
     *            data to be json encoded
     */
    protected function _returnJson ($data)
    {
        // disable layout and set content type
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-Type', "application/json", true);
        
        $returnValue = Json::encode($data);
        if ($this->_prettyJson) {
            $returnValue = Json::prettyPrint($returnValue);
        }
        $this->getResponse()->setBody($returnValue);
    }

    /**
     * The default read Action
     *
     * Return the content of the collection, get filters from the request
     * params, get sort from request params
     */
    public function indexAction ()
    {
        if (! isset($this->_parentId)) {
            return;
        }
        
        $filterJson = $this->getRequest()->getParam('filter');
        if (isset($filterJson)) {
            $filters = Json::decode($filterJson,Json::TYPE_ARRAY);
        } else {
            $filters = null;
        }
        $sortJson = $this->getRequest()->getParam('sort');
        if (isset($sortJson)) {
            $sort = Json::decode($sortJson,Json::TYPE_ARRAY);
        } else {
            $sort = null;
        }
        
        $mongoFilters = $this->_buildFilter($filters);
        $dataValues = $this->_dataService->getList($this->_parentId, $mongoFilters, $sort);
        
        $response = array();
        $response['total'] = count($dataValues);
        $response['data'] = $dataValues;
        $response['success'] = TRUE;
        $response['message'] = 'OK';
        
        return $this->_returnJson($response);
    }

    /**
     * The destroy action of the CRUD API
     */
    public function deleteAction ()
    {
        if (! isset($this->_parentId)) {
            return;
        }
        
        $data = $this->getRequest()->getParam('data');
        
        if (! is_null($data)) {
            $data = Json::decode($data,Json::TYPE_ARRAY);
            if (is_array($data)) {
                
                $returnArray = $this->_dataService->destroy($this->_parentId, $data, true);
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
            $this->getResponse()->setHttpResponseCode(500);
        }
        return $this->_returnJson($returnArray);
    }

    /**
     * The create action of the CRUD API
     */
    public function createAction ()
    {
        if (! isset($this->_parentId)) {
            return;
        }
        
        $data = $this->getRequest()->getParam('data');
        
        if (! is_null($data)) {
            $insertData = Json::decode($data,Json::TYPE_ARRAY);
            if (is_array($insertData)) {
                $returnArray = $this->_dataService->create($this->_parentId, $insertData, true);
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
            $this->getResponse()->setHttpResponseCode(500);
        }
        return $this->_returnJson($returnArray);
    }

    /**
     * The update action of the CRUD API
     */
    public function updateAction ()
    {
        if (! isset($this->_parentId)) {
            return;
        }
        
        $data = $this->getRequest()->getParam('data');
        
        if (! is_null($data)) {
            $updateData = Json::decode($data,Json::TYPE_ARRAY);
            if (is_array($updateData)) {
                
                $returnArray = $this->_dataService->update($this->_parentId, $updateData, true);
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
            $this->getResponse()->setHttpResponseCode(500);
        }
        return $this->_returnJson($returnArray);
    }
}
