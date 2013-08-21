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
require_once ('DataAccessController.php');

/**
 * Controller providing CRUD API for the Workspaces JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class Backoffice_WorkspacesController extends DataAccessController
{

    public function init ()
    {
        parent::init();
        
        // init the data access service
        $this->_dataService = Rubedo\Services\Manager::getService('Workspaces');
    }
    
    /*
     * (non-PHPdoc) @see DataAccessController::indexAction()
     */
    public function indexAction ()
    {
        $filterJson = $this->getRequest()->getParam('filter');
        if (isset($filterJson)) {
            $filters = Zend_Json::decode($filterJson);
        } else {
            $filters = null;
        }
        $sortJson = $this->getRequest()->getParam('sort');
        if (isset($sortJson)) {
            $sort = Zend_Json::decode($sortJson);
        } else {
            $sort = null;
        }
        $startJson = $this->getRequest()->getParam('start');
        if (isset($startJson)) {
            $start = Zend_Json::decode($startJson);
        } else {
            $start = null;
        }
        $limitJson = $this->getRequest()->getParam('limit');
        if (isset($limitJson)) {
            $limit = Zend_Json::decode($limitJson);
        } else {
            $limit = null;
        }
        
        $mongoFilters = $this->_buildFilter($filters);
        
        $notAll = $this->getParam('notAll', false);
        if ($notAll) {
            $mongoFilters->addFilter(new \Rubedo\Mongo\NotAllWorkspacesFilter());
        }
        
        $dataValues = $this->_dataService->getList($mongoFilters, $sort, $start, $limit);
        
        $response = array();
        $response['total'] = $dataValues['count'];
        $response['data'] = $dataValues['data'];
        $response['success'] = TRUE;
        $response['message'] = 'OK';
        
        return $this->_returnJson($response);
    }
}