<?php

/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Backoffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;
use Zend\View\Model\JsonModel;

/**
 * Controller providing Click Stream info for pickers
 *
 *
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 *
 */
class ClickStreamController extends AbstractActionController
{

    public function eventListAction()
    {
        $data=Manager::getService("ElasticClickStream")->getEventList();
        $eventArray=[];
        foreach($data as $event){
            $eventArray[]=[
                "value"=>$event,
                "label"=>$event
            ];
        }
        return new JsonModel([
            "success"=>true,
            "message"=>"OK",
            "data"=>$eventArray,
            "total"=>count($eventArray)
        ]);
    }

    public function facetListAction()
    {
        $data=Manager::getService("ElasticClickStream")->getFacetList();
        $facetArray=[];
        foreach($data as $key=>$value){
            $facetArray[]=[
                "value"=>$value,
                "label"=>$key
            ];
        }
        return new JsonModel([
            "success"=>true,
            "message"=>"OK",
            "data"=>$facetArray,
            "total"=>count($facetArray)
        ]);
    }
}
