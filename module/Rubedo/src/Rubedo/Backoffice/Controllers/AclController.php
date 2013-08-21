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

use Rubedo\Services\Manager;

/**
 * Controller providing access control list
 *
 * Receveive Ajax Calls with needed ressources, send true or false for each of them
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class AclController extends AbstractActionController
{

    /**
     * should json be prettified
     *
     * @var bool
     */
    protected $_prettyJson = true;

    /**
     * Set the response body with Json content
     * Option : json is made human readable
     * 
     * @param mixed $data
     *            data to be json encoded
     */
    protected function _returnJson ($data)
    {
        // disable layout and set content type
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-Type', "application/json", true);
        
        $returnValue = Zend_Json::encode($data);
        if ($this->_prettyJson) {
            $returnValue = Zend_Json::prettyPrint($returnValue);
        }
        $this->getResponse()->setBody($returnValue);
    }

    function indexAction ()
    {
        $AclArray = array();
        $dataJson = $this->getRequest()->getParam('data');
        if (isset($dataJson)) {
            $dataArray = Zend_Json::decode($dataJson);
            if (is_array($dataArray)) {
                $aclService = Manager::getService('Acl');
                $AclArray = $aclService->accessList(array_keys($dataArray));
            }
        }
        
        return $this->_returnJson($AclArray);
    }
}
