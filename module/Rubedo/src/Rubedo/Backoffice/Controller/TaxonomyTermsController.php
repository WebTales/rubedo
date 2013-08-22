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
class TaxonomyTermsController extends DataAccessController
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

    public function __construct ()
    {
        parent::__construct();
        
        // init the data access service
        $this->_dataService = Manager::getService('TaxonomyTerms');
    }

    /**
     * Clear orphan terms in the collection
     *
     * @return array Result of the request
     */
    public function clearOrphanTermsAction ()
    {
        $result = $this->_dataService->clearOrphanTerms();
        
        return $this->_returnJson($result);
    }

    public function countOrphanTermsAction ()
    {
        $result = $this->_dataService->countOrphanTerms();
        
        return $this->_returnJson($result);
    }

    public function navigationTreeAction ()
    {
        $withCurrentPage = $this->params()->fromQuery('add-current-page', false);
        $result = $this->_dataService->getNavigationTree($withCurrentPage);
        $resultArray = array();
        $resultArray['success'] = true;
        $resultArray['children'] = $result;
        return $this->_returnJson($resultArray);
    }
}