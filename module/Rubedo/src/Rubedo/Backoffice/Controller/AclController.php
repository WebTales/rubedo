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
use Zend\Json\Json;
use Zend\View\Model\JsonModel;


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

    function indexAction()
    {
        $AclArray = array();
        $dataJson = $this->params()->fromPost('data');
        if (isset($dataJson)) {
            $dataArray = Json::decode($dataJson,Json::TYPE_ARRAY);
            if (is_array($dataArray)) {
                $aclService = Manager::getService('Acl');
                $AclArray = $aclService->accessList(array_keys($dataArray));
            }
        }
        
        return new JsonModel($AclArray);
    }
}
