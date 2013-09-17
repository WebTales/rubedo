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
namespace Rubedo\Frontoffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Controller providing css for custom themes
 *
 *
 * @todo need a serious refactoring!
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 *         
 */
class QueueController extends AbstractActionController
{
    function indexAction ()
    {
        $params = $this->params()->fromQuery();
        $vars = array();
        foreach ($params as $key => $value) {
            switch ($key) {
                case "service":
                    if(!in_array($value,array('ElasticDataIndex'))){
                        throw new \Rubedo\Exceptions\Access('can\'t call this service');
                    }
                    $serviceName = $value;
                    break;
                case "class":
                    $methodName = $value;
                    break;
                case "module":
                case "controller":
                case "action":
                    break;
                default:
                    $vars[] = $value;
            }
        }
        $service = \Rubedo\Services\Manager::getService($serviceName);
        $service->init();
 
        $callBack = array($service,$methodName);
        $return = call_user_func_array($callBack, $vars);
        return new JsonModel($return);
    }
}
