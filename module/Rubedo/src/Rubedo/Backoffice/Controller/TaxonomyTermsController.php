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
use Zend\Json\Json;

/**
 * Controller providing CRUD API for the taxonomyTerms JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin, aDobre
 * @category Rubedo
 * @package Rubedo
 *         
 */
class TaxonomyTermsController extends DataAccessController
{

    public function __construct()
    {
        parent::__construct();
        
        // init the data access service
        $this->_dataService = Manager::getService('TaxonomyTerms');
    }

    public function indexAction()
    {
        $receivedID=$this->params()->fromQuery('id', null);
        $postFilter=false;
        $response = array();
        $response['total']=0;
        $response['data']=array();
        if (!empty($receivedID)){
            $receivedIDArray=explode(", ",$receivedID);
            $preFilter=Filter::factory('InUid')->setValue($receivedIDArray);
            $postFilter=Filter::factory('NotInUid')->setValue($receivedIDArray);
            $preValues = $this->_dataService->getList($preFilter, null, null, null);
            $response['total'] =$preValues['count'];
            $response['data'] = $preValues['data'];
        }

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
        if ($postFilter){
            $mongoFilters->addFilter($postFilter);
        }
        $dataValues = $this->_dataService->getList($mongoFilters, $sort, $start, $limit);


        $response['total'] =$response['total']+$dataValues['count'];
        $response['data'] = array_merge($response['data'],$dataValues['data']);
        $response['success'] = TRUE;
        $response['message'] = 'OK';

        return $this->_returnJson($response);
    }
    /**
     * Clear orphan terms in the collection
     *
     * @return array Result of the request
     */
    public function clearOrphanTermsAction()
    {
        $result = $this->_dataService->clearOrphanTerms();
        
        return $this->_returnJson($result);
    }

    public function countOrphanTermsAction()
    {
        $result = $this->_dataService->countOrphanTerms();
        
        return $this->_returnJson($result);
    }

    public function navigationTreeAction()
    {
        $withCurrentPage = $this->params()->fromQuery('add-current-page', false);
        $result = $this->_dataService->getNavigationTree($withCurrentPage);
        $resultArray = array();
        $resultArray['success'] = true;
        $resultArray['children'] = $result;
        return $this->_returnJson($resultArray);
    }
}