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
use Rubedo\Services\Manager;

/**
 * Controller providing css for custom themes
 *
 *
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 *         
 */
class QueueController extends Zend_Controller_Action
{
    function indexAction ()
    {
        $params = $this->getRequest()->getParams();
        $vars = array();
        foreach ($params as $key => $value) {
            switch ($key) {
                case "service":
                    $serviceName = $value;
                    break;
                case "class":
                    $className = $value;
                    break;
                case "module":
                    break;
                case "controller":
                    break;
                case "action":
                    break;
                default:
                    $vars[] = $value;
            }
        }
        $service = \Rubedo\Services\Manager::getService($serviceName);
        $service->init();
        switch (count($vars)) {
            case 0:
                $return = $service->$className();
                break;
            case 1:
                $return = $service->$className($vars[0]);
                break;
            case 2:
                $return = $service->$className($vars[0],$vars[1]);
                break;
            case 3:
                $return = $service->$className($vars[0],$vars[1],$vars[2]);
                break;
            case 4:
                $return = $service->$className($vars[0],$vars[1],$vars[2],$vars[3]);
                break;
            case 5:
                $return = $service->$className($vars[0],$vars[1],$vars[2],$vars[3],$vars[4]);
                break;
        }
        echo $return;
        exit;
        

    }
}
