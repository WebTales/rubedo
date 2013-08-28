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
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Backoffice asynchroneous authentication Controller
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class XhrAuthenticationController extends AbstractActionController
{

    public function __construct()
    {
        // init the data access service
        $this->_dataService = Manager::getService('Authentication');
    }

    /**
     * Login or not the user and return a boolean
     *
     * @return bool
     */
    public function loginAction()
    {
        $login = $this->params()->fromPost('login');
        $password = $this->params()->fromPost('password');
        Manager::getService('Session')->getSessionObject()
            ->getManager()
            ->regenerateId(true);
        
        $loginResult = $this->_dataService->authenticate($login, $password);
        
        if ($loginResult) {
            $response['success'] = true;
        } else {
            $response['success'] = false;
        }
        return new JsonModel($response);
    }

    /**
     * Logout the user and return a boolean
     *
     * @return bool
     */
    public function logoutAction()
    {
        $this->_dataService->clearIdentity();
        
        $response['success'] = true;
        return new JsonModel($response);
    }

    /**
     * Check session without renewing its lifetime
     *
     * @todo use config cookiename
     * @return \Zend\View\Model\JsonModel
     */
    public function isSessionExpiringAction()
    {
        $sessionDataService = Manager::getService('SessionData');
        $sessionName = $sessionDataService->getSessionName();
        $cookie = $this->getRequest()->getCookie();
        if (isset($cookie->$sessionName)) {
            
            // get data from sessions collection without using session handler : do not renew lifetime
            $sessionData = $sessionDataService->findById($cookie->$sessionName);
            $modified = $sessionData["modified"];
            $modifiedTstamp = $modified->sec;
            $lifetime = $sessionData["lifetime"];
            $time = max(0, $lifetime + $modifiedTstamp - time());
            $status = $time > 0;
            
            // check if a user is stored
            if ($status) {
                $decodedSessionData = $sessionDataService->decode($sessionData["data"]);
                if (isset($decodedSessionData['Zend_Auth']) && ! empty($decodedSessionData['Zend_Auth']->storage)) {
                    $status = true;
                } else {
                    $status = false;
                }
            }
        } else {
            // no cookie, no chocolate
            $status = false;
            $time = 0;
        }
        
        return new JsonModel(array(
            'time' => $time,
            'status' => $status
        ));
    }
}
