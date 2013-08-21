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
 * Controller providing CRUD API for the PersonalPrefs JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class PersonalPrefsController extends DataAccessController
{

    /**
     * Array with the read only actions
     */
    protected $_readOnlyAction = array(
        'index',
        'find-one',
        'read-child',
        'tree',
        'clear-orphan-prefs',
        'count-orphan-prefs'
    );

    /**
     * Initialise the controller
     */
    public function __construct ()
    {
        parent::__construct();
        
        $this->_dataService = Manager::getService('PersonalPrefs');
    }

    public function clearOrphanPrefsAction ()
    {
        $result = $this->_dataService->clearOrphanPrefs();
        
        return $this->_returnJson($result);
    }

    public function countOrphanPrefsAction ()
    {
        $result = $this->_dataService->countOrphanPrefs();
        
        return $this->_returnJson($result);
    }
}
