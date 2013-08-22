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

/**
 * Controller providing the list of available Front Office Theme
 *
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class FoThemesController extends AbstractActionController
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
        
        $returnValue = Json::encode($data);
        if ($this->_prettyJson) {
            $returnValue = Json::prettyPrint($returnValue);
        }
        $this->getResponse()->setBody($returnValue);
    }

    /**
     * The default read Action
     *
     * Return the content of the collection, get filters from the request
     * params, get sort from request params
     */
    public function indexAction ()
    {
        
        $response = Manager::getService('FrontOfficeTemplates')->getAvailableThemes();
        
        return $this->_returnJson($response);
    }

    /**
     */
    public function getThemeInfosAction ()
    {
        $themeName = $this->getParam('theme', 'default');
        $response = Manager::getService('FrontOfficeTemplates')->getThemeInfos($themeName);
        return $this->_returnJson($response);
    }
}
