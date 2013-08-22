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
use Zend\View\Model\JsonModel;

/**
 * Controller providing Elastic Search indexation
 *
 *
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 *         
 */
class ElasticIndexerController extends AbstractActionController
{

    public function indexAction()
    {
        
        // get params
        $params = $this->params()->fromQuery();
        
        // get option : all, dam, content
        
        $option = isset($params['option']) ? $params['option'] : 'all';
        
        $es = Manager::getService('ElasticDataIndex');
        $es->init();
        $return = $es->indexAll($option);
        return new JsonModel($return);
    }
}
