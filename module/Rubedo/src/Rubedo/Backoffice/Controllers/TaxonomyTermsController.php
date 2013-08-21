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
 * Controller providing CRUD API for the taxonomyTerms JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class Backoffice_TaxonomyTermsController extends Backoffice_DataAccessController
{

    /**
     * Array with the read only actions
     */
    protected $_readOnlyAction = array(
        'index',
        'find-one',
        'read-child',
        'navigation-tree',
        'clear-orphan-terms',
        'count-orphan-terms',
        'model',
        'tree'
    );

    public function init ()
    {
        parent::init();
        
        // init the data access service
        $this->_dataService = Rubedo\Services\Manager::getService('TaxonomyTerms');
    }

    /**
     * Clear orphan terms in the collection
     *
     * @return array Result of the request
     */
    public function clearOrphanTermsAction ()
    {
        $result = $this->_dataService->clearOrphanTerms();
        
        $this->_returnJson($result);
    }

    public function countOrphanTermsAction ()
    {
        $result = $this->_dataService->countOrphanTerms();
        
        $this->_returnJson($result);
    }

    public function navigationTreeAction ()
    {
        $withCurrentPage = $this->getParam('add-current-page', false);
        $result = $this->_dataService->getNavigationTree($withCurrentPage);
        $resultArray = array();
        $resultArray['success'] = true;
        $resultArray['children'] = $result;
        $this->_returnJson($resultArray);
    }
}