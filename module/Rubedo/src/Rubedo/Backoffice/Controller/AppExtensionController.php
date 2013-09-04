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
 * Return the configuration of applications to extends Rubedo Backoffice
 *
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 *         
 */
class AppExtensionController extends AbstractActionController
{
    /**
     * Action that returns config for Back Office extension integration
     *
     * 
     */
    function indexAction()
    {
        $config = array();
        
        return new JsonModel($config);
    }
    
    function getFileAction(){
        $appName = $this->params()->fromRoute('app-name');
        $filePath = $this->params()->fromRoute('filepath');
        
        $basePath = Manager::getService('AppExtension')->getBasePath($appName);
        
        $retour = array('appName'=>$appName,'filePath'=>$filePath);
        return new JsonModel($retour);
    }
}
