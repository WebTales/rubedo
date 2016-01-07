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
use WebTales\MongoFilters\Filter;
use Zend\Debug\Debug;
use Zend\View\Model\JsonModel;

/**
 * Controller providing machine learning actions for rubedo
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 *
 */
class MLController extends AbstractActionController
{

    /**
     * The default index Action
     *
     */
    public function indexAction()
    {
    	$api = new \BigML\BigML("webtales", "42b58814745b8a66d043756f10dabb2216a82e5b");
    	var_dump($api);
    	$source = $api::create_source('http://archive.ics.uci.edu/ml/machine-learning-databases/iris/iris.data');
    	$status = $api::status($source->resource);
    	while($status!=5) {
    		echo "Status : $status, Waiting 5 secondes ... ";
    		sleep(5);
    		$status = $api::status($source->resource);
    	}
    	echo "Status completed";
    	//echo $api::status($source['resource']);
    	$dataset = $api::create_dataset($source);
    	var_dump($dataset);
    	$model = $api::create_model($dataset);
    	var_dump($model);
    	$prediction = $api::create_prediction($model, array('sepal length'=> 5, 'sepal width'=> 2.5));
    	var_dump($prediction);
        $returnArray = array();
        $returnArray['success'] = true;
        $returnArray['data'] = $api::pprint($prediction);
        return new JsonModel($returnArray);
    }



}
