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
 * Controller providing the list of available Roles
 *
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class RolesController extends AbstractActionController
{

    /**
     * The default read Action
     *
     * Return the content of the collection, get filters from the request
     * params, get sort from request params
     */
    public function indexAction()
    {
        $response = Manager::getService('Acl')->getAvailaibleRoles();
        
        return new JsonModel($response);
    }

    /**
     * @todo is this action in the correct controller ?
     * @return \Zend\View\Model\JsonModel
     */
    public function getThemeInfosAction()
    {
        $themeName = $this->getParam('theme', 'default');
        $response = Manager::getService('FrontOfficeTemplates')->getThemeInfos($themeName);
        return new JsonModel($response);
    }
}
