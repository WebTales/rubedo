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
 * Controller providing CRUD API for the masks JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class Backoffice_MasksController extends Backoffice_DataAccessController
{

    /**
     * Array with the read only actions
     */
    protected $_readOnlyAction = array(
        'index',
        'find-one',
        'read-child',
        'tree',
        'model',
        'is-used'
    );

    public function init ()
    {
        parent::init();
        
        // init the data access service
        $this->_dataService = Rubedo\Services\Manager::getService('Masks');
    }

    public function isUsedAction ()
    {
        $id = $this->getRequest()->getParam('id');
        $wasFiltered = Rubedo\Collection\AbstractCollection::disableUserFilter();
        $result = Rubedo\Services\Manager::getService('Pages')->isMaskUsed($id);
        Rubedo\Collection\AbstractCollection::disableUserFilter($wasFiltered);
        // $resultArray = (is_array($listResult) && $listResult['count']>0) ? array("used"=>true) : array("used"=>false);
        $this->_returnJson($result);
    }
}