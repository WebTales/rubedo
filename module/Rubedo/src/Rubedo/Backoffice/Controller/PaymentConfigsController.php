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

use Rubedo\Services\Manager;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;

/**
 * Controller providing CRUD API for the PaymentConfigs JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 *         
 */
class PaymentConfigsController extends DataAccessController
{

    public function __construct()
    {
        parent::__construct();
        
        // init the data access service
        $this->_dataService = Manager::getService('PaymentConfigs');
    }

    public function getConfigForPaymentMeansAction(){
        $paymentMeans=$this->params()->fromQuery("paymentMeans", null);
        if (!$paymentMeans){
            return new JsonModel(array(
                "success"=>false,
                "msg"=>"Missing required parameter"
            ));
        }
        $result=$this->_dataService->getConfigForPM($paymentMeans);
        return new JsonModel($result);
    }

}