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
        $isMultiNode=false;
        $config=Manager::getService("config");
        if (isset($config["webCluster"])&&is_array($config["webCluster"])){
            $isMultiNode=true;
        }
        $isReplicated=$this->params()->fromPost("isReplicated",null);
        if ($isMultiNode&&!$isReplicated){
            $mainRequest=$this->getRequest();
            $path=$mainRequest->getUri()->getPath();
            $post=$mainRequest->getPost()->toArray();
            $post["isReplicated"]=true;
            $cookie=$mainRequest->getCookie()->__toString();
            $lastResult=[];
            $allResults=[];
            $protocol=isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS'] ? "https://" : "http://";
            foreach($config["webCluster"] as $clusterHost){
                $curlUrl = $protocol . $clusterHost . $path;
                $curly = curl_init();
                curl_setopt($curly,CURLOPT_URL, $curlUrl);
                curl_setopt($curly,CURLOPT_POST, true);
                curl_setopt($curly,CURLOPT_POSTFIELDS, $post);
                curl_setopt($curly,CURLOPT_COOKIE, $cookie);
                curl_setopt($curly, CURLOPT_RETURNTRANSFER, true);
                $result=Json::decode(curl_exec($curly),Json::TYPE_ARRAY);
                $lastResult=$result;
                $allResults[]=$result;
                curl_close($curly);
            }
            $lastResult["clusterResults"]=$allResults;
            return new JsonModel($lastResult);
        } else {
            $data = $this->params()->fromPost('data');
            if (empty($data)) {
                $this->getResponse()->setStatusCode(500);
                return new JsonModel(array("success" => false));
            }
            $updateData = Json::decode($data, Json::TYPE_ARRAY);
            if (!isset($this->config["rubedo_config"])) {
                $this->config["rubedo_config"] = [];
            }
            if (!isset($this->config["swiftmail"]["smtp"])) {
                $this->config["swiftmail"] = [
                    "smtp" => []
                ];
            }
            if (isset($updateData["rubedo_config"]) && is_array($updateData["rubedo_config"])) {
                $this->config["rubedo_config"] = array_merge($this->config["rubedo_config"], $updateData["rubedo_config"]);
            }
            if (isset($updateData["swiftmail"]["smtp"]) && is_array($updateData["swiftmail"]["smtp"])) {
                $this->config["swiftmail"]["smtp"] = array_merge($this->config["swiftmail"]["smtp"], $updateData["swiftmail"]["smtp"]);
            }
            $this->installObject->saveLocalConfig($this->config);
            Manager::getService("MongoConf")->setRubedoConf($this->config);
            return new JsonModel(array("success" => true));
        }
    }
}
