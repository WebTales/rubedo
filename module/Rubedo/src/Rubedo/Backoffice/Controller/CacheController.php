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
use Rubedo\Services\Manager;
use Rubedo\Services\Cache;
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
class CacheController extends AbstractActionController
{

    /**
     * cache object
     *
     * @var Zend_Cache
     */
    protected $_cache;

    /**
     * The default read Action
     *
     * Return the content of the collection, get filters from the request
     * params, get sort from request params
     */
    public function indexAction()
    {
        $countArray = array();
        $countArray['cachedItems'] = Manager::getService('Cache')->count();
        $countArray['cachedUrl'] = Manager::getService('UrlCache')->count();
        $countArray['apiCache'] = Manager::getService('ApiCache')->count();
        return new JsonModel($countArray);
    }

    public function clearAction()
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
            $installObject = new Install();
            $installObject->clearConfigCache();
            $installObject->clearFileCaches();
            $countArray = array();
            $countArray['Cached items'] = Cache::getCache()->clean();
            if (Manager::getService('UrlCache')->count() > 0) {
                $countArray['Cached Url'] = Manager::getService('UrlCache')->drop();
                Manager::getService('UrlCache')->ensureIndexes();
            } else {
                $countArray['Cached Url'] = true;
            }
            Manager::getService('ApiCache')->drop();
            Manager::getService('ApiCache')->ensureIndexes();
            return new JsonModel($countArray);
        }
    }

    public function clearConfigAction()
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
            $installObject = new Install();
            $installObject->clearConfigCache();
            return new JsonModel(array("success" => true));
        }
    }

    public function clearFilesAction()
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
            $installObject = new Install();
            $installObject->clearFileCaches();
            return new JsonModel(array("success" => true));
        }
    }

    public function clearApiAction(){
        Manager::getService('ApiCache')->drop();
        Manager::getService('ApiCache')->ensureIndexes();
        return new JsonModel(array("success"=>true));
    }

    public function clearUrlAction(){
        Manager::getService('UrlCache')->drop();
        Manager::getService('UrlCache')->ensureIndexes();
        return new JsonModel(array("success"=>true));
    }

    public function clearObjectsAction(){
        Cache::getCache()->clean();
        return new JsonModel(array("success"=>true));
    }


}
