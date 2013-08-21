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
class Backoffice_DirectoriesController extends Backoffice_DataAccessController
{

    /**
     * Array with the read only actions
     */
    protected $_readOnlyAction = array(
        'index',
        'find-one',
        'read-child',
        'tree',
        'clear-orphan-pages',
        'count-orphan-pages',
        'model'
    );

    public function init ()
    {
        parent::init();
        
        // init the data access service
        $this->_dataService = Rubedo\Services\Manager::getService('Directories');
    }

    /**
     * Clear orphan terms in the collection
     *
     * @return array Result of the request
     */
    public function clearOrphanDirectoriesAction ()
    {
        $result = $this->_dataService->clearOrphanPages();
        
        $this->_returnJson($result);
    }

    public function countOrphanDirectoriesAction ()
    {
        $result = $this->_dataService->countOrphanPages();
        
        $this->_returnJson($result);
    }
    public function classifyAction ()
    {
        $encodedArray = $this->getRequest()->getParam("mediaArray","[ ]");
        $decodedArray=Zend_Json::decode($encodedArray);
        $directoryId=$this->getRequest()->getParam("directoryId");
        $result = $this->_dataService->classify($decodedArray,$directoryId);
        $this->_returnJson($result);
    }
   
}