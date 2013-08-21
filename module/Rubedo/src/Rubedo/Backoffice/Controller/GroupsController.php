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

/**
 * Controller providing CRUD API for the Groups JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class GroupsController extends DataAccessController
{

    /**
     * Array with the read only actions
     */
    protected $_readOnlyAction = array(
        'index',
        'find-one',
        'read-child',
        'tree',
        'clear-orphan-groups',
        'count-orphan-groups',
        'model'
    );

    public function __construct ()
    {
        parent::__construct();
        
        // init the data access service
        $this->_dataService = Manager::getService('Groups');
    }

    /**
     * Clear orphan groups in the collection
     *
     * @return array Result of the request
     */
    public function clearOrphanGroupsAction ()
    {
        $result = $this->_dataService->clearOrphanGroups();
        
        return $this->_returnJson($result);
    }

    public function countOrphanGroupsAction ()
    {
        $result = $this->_dataService->countOrphanGroups();
        
        return $this->_returnJson($result);
    }
}