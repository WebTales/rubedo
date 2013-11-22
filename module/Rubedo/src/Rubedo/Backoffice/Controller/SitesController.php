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

use WebTales\MongoFilters\Filter;
use Rubedo\Services\Manager;
use Zend\Json\Json;

/**
 * Controller providing CRUD API for the sitesController JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class SitesController extends DataAccessController
{

    public function __construct ()
    {
        parent::__construct();
        
        // init the data access service
        $this->_dataService = Manager::getService('Sites');
    }

    public function deleteAction ()
    {
        $data = $this->params()->fromPost('data');
        
        if (! is_null($data)) {
            $data = Json::decode($data,Json::TYPE_ARRAY);
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

    public function wizardCreateAction ()
    {
        $data = $this->params()->fromPost('data');
        $returnArray = array(
            'success' => false,
            "msg" => 'no data recieved'
        );
        if (! is_null($data)) {
            $insertData = Json::decode($data,Json::TYPE_ARRAY);
            if ((isset($insertData['builtOnEmptySite'])) && ($insertData['builtOnEmptySite'])) {
                $returnArray = $this->createFromEmpty($insertData);
            } else 
                if ((isset($insertData['builtOnModelSiteId'])) && (! empty($insertData['builtOnModelSiteId']))) {
                    $returnArray = $this->createFromModel($insertData);
                } else {
                    $returnArray = array(
                        'success' => false,
                        "msg" => 'no site model provided'
                    );
                }
        }
        if (! $returnArray['success']) {
            $this->getResponse()->setStatusCode(500);
        }
        return $this->_returnJson($returnArray);
    }
    
	/**
     * @param unknown $insertData
     * @return multitype:boolean string |unknown
     */
    protected function createFromModel ($insertData)
    {
        
        $returnArray = Manager::getService('Sites')->createFromModel($insertData);
        return ($returnArray);
    }

    /**
     * @param unknown $insertData
     * @return multitype:boolean string multitype:boolean string  NULL
     */
    protected function createFromEmpty ($insertData)
    {
        $returnArray = Manager::getService('Sites')->createFromEmpty($insertData);
        return ($returnArray);
    }
    
}