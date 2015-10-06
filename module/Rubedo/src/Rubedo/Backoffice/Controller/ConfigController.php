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

use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;

use Zend\View\Model\JsonModel;
use Rubedo\Update\Install;

/**
 * Controller providing control over the cached contents
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class ConfigController extends AbstractActionController
{

    private $installObject;


    public function __construct() {
        $this->installObject = new Install();
        $this->installObject->loadLocalConfig();
        $this->config = $this->installObject->getLocalConfig();
    }


    public function indexAction()
    {
        $returnedArray =  array_intersect_key($this->config, array_flip(array('swiftmail','rubedo_config')));

        return new JsonModel($returnedArray);
    }

    public function updateAction()
    {
        $data = $this->params()->fromPost('data');
        if (empty($data)){
            $this->getResponse()->setStatusCode(500);
            return new JsonModel(array("success"=>false));
        }
        $updateData = Json::decode($data, Json::TYPE_ARRAY);
        if (!isset($this->config["rubedo_config"])){
            $this->config["rubedo_config"]=[];
        }
        if (isset($updateData["rubedo_config"])&&is_array($updateData["rubedo_config"])){
            $this->config["rubedo_config"] = array_merge($this->config["rubedo_config"], $updateData["rubedo_config"]);
        }
        $this->installObject->saveLocalConfig($this->config);
        return new JsonModel(array("success"=>true));
    }




}
