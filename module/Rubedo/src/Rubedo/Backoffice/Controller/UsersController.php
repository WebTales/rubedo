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
 * Controller providing CRUD API for the users JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class UsersController extends DataAccessController
{

    public function __construct ()
    {
        parent::__construct();
        
        // init the data access service
        $this->_dataService = Manager::getService('Users');
    }

    public function changePasswordAction ()
    {
        $password = $this->getRequest()->getParam('password');
        $id = $this->getRequest()->getParam('id');
        $version = $this->getRequest()->getParam('version');
        
        if (! empty($password) && ! empty($id) && ! empty($version)) {
            
            $result = $this->_dataService->changePassword($password, $version, $id);
            
            if ($result == true) {
                $message['success'] = true;
            } else {
                $message['success'] = false;
            }
            
            return $this->_helper->json($message);
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => 'No Data'
            );
        }
        
        if (! $returnArray['success']) {
            $this->getResponse()->setHttpResponseCode(500);
        }
        
        return $this->_helper->json($returnArray);
    }
}
