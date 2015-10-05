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
namespace Rubedo\Console\Controller;

use Rubedo\Interfaces\config;
use Rubedo\Services\Manager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Console;
use Zend\Console\ColorInterface;
use Rubedo\Update\Install;

/**
 * Console cache controller
 *
 * Invoked when calling from console
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
class ConfigController extends AbstractActionController
{

    private $console;

    private $installObject;


    public function __construct() {
        $this->console = Console::getInstance();
        $this->installObject = new Install();
        $this->installObject->loadLocalConfig();
        $this->config = $this->installObject->getLocalConfig();
        if (!isset($this->config['installed'])) {
            $this->config['installed'] = array(
                'status' => 'begin',
                'action' => 'start-wizard',
            );
        }
    }


    public function setdbAction()
    {
        $request = $this->getRequest();
        if(!$this->getRequest() instanceof ConsoleRequest) {
            throw new \RuntimeException("You can only call this action from the console");
        }
        $mongoParams=array(
            "server"=>$request->getParam("server"),
            "port"=>$request->getParam("port"),
            "db"=>$request->getParam("db"),
        );
        foreach(array("replicaSetName","adminLogin","adminPassword","login","password") as $configOption){
            if ($request->getParam($configOption)){
                $mongoParams[$configOption]=$request->getParam($configOption);
            }
        }
        if (!isset($this->config["datastream"])){
            $this->config["datastream"]=array();
        }
        $this->config["datastream"]["mongo"] = $mongoParams;

        if($this->config["installed"]["status"]!="finished"&&($this->config["installed"]["action"]=="start-wizard"||$this->config["installed"]["action"]=="set-db")){
            $this->config["installed"]["action"]='set-elastic-search';
        }

        $this->installObject->saveLocalConfig($this->config);
        $this->console->writeLine("Database connection configured", ColorInterface::GREEN);
        return;
    }

    public function setesAction()
    {
        $request = $this->getRequest();
        if(!$this->getRequest() instanceof ConsoleRequest) {
            throw new \RuntimeException("You can only call this action from the console");
        }
        $esParams=array(
            "host"=>$request->getParam("host"),
            "port"=>$request->getParam("port"),
            "contentIndex"=>$request->getParam("contentIndex"),
            "damIndex"=>$request->getParam("damIndex"),
            "userIndex"=>$request->getParam("userIndex"),
        );

        if (!isset($this->config["elastic"])){
            $this->config["elastic"]=array();
        }
        if($this->config["installed"]["status"]!="finished"&&$this->config["installed"]["action"]=="set-elastic-search"){
            $this->config["installed"]["action"]='define-languages';
        }
        $this->config["elastic"] = $esParams;
        $this->installObject->saveLocalConfig($this->config);
        $this->console->writeLine("Elastisearch connection configured", ColorInterface::GREEN);
        return;
    }

    public function setlangAction()
    {
        $request = $this->getRequest();
        if(!$this->getRequest() instanceof ConsoleRequest) {
            throw new \RuntimeException("You can only call this action from the console");
        }
        $languageService = Manager::getService('Languages');
        $languageListResult = $languageService->getList(null, array(
            array(
                'property' => 'label',
                'direction' => 'asc'
            )
        ));
        if ($languageListResult['count'] == 0) {
            Install::importLanguages();
            $languageListResult = $languageService->getList(null, array(
                array(
                    'property' => 'label',
                    'direction' => 'asc'
                )
            ));
        }
        $update = $this->installObject->setDefaultRubedoLanguage($request->getParam("lang"));
        if($this->config["installed"]["status"]!="finished"&&$this->config["installed"]["action"]=="define-languages"){
            $this->config["installed"]["action"]='set-db-contents';
        }
        $this->installObject->saveLocalConfig($this->config);
        $this->console->writeLine("Default language set", ColorInterface::GREEN);
        return;
    }

    public function resetAction(){
        $request = $this->getRequest();
        if(!$this->getRequest() instanceof ConsoleRequest) {
            throw new \RuntimeException("You can only call this action from the console");
        }
        $this->config=array(
            "installed"=>array(
                'status' => 'begin',
                'action' => 'start-wizard',
            )
        );
        $this->installObject->saveLocalConfig($this->config);
        $this->console->writeLine("Config reset", ColorInterface::GREEN);
        return;
    }


}
