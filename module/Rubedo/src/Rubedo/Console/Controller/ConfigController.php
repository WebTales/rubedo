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

use Rubedo\Collection\AbstractCollection;
use Rubedo\Update\Update;
use Rubedo\Collection\Pages;
use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
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
    public function initdbAction()
    {
        $request = $this->getRequest();
        if(!$this->getRequest() instanceof ConsoleRequest) {
            throw new \RuntimeException("You can only call this action from the console");
        }
        $wasFiltered = AbstractCollection::disableUserFilter();
        $success=$this->installObject->doEnsureIndexes();
        if ($success){
            $success=$this->installObject->doCreateDefaultsGroups();
        }
        if ($success){
            $success = Install::doInsertContents();
        }
        if ($success) {
            Update::update();
            Pages::localizeAllCollection();
        }
        AbstractCollection::disableUserFilter($wasFiltered);
        if ($success){
            if($this->config["installed"]["status"]!="finished"&&$this->config["installed"]["action"]=='set-db-contents'){
                $this->config["installed"]["action"]='set-admin';
            }
            $this->installObject->saveLocalConfig($this->config);
            $this->console->writeLine("DB init completed", ColorInterface::GREEN);

        } else {
            $this->console->writeLine("DB init failed", ColorInterface::RED);
        }
        return;

    }
    public function setadminAction()
    {
        $request = $this->getRequest();
        if (!$this->getRequest() instanceof ConsoleRequest) {
            throw new \RuntimeException("You can only call this action from the console");
        }
        $wasFiltered = AbstractCollection::disableUserFilter();
        $hashService = Manager::getService('Hash');
        $salt=$hashService->generateRandomString();
        $adminGroup = Manager::getService('Groups')->findByName('admin');
        $filters = Filter::factory();
        $filters->addFilter(Filter::factory('Value')->setName('UTType')
            ->setValue("default"));
        $defaultUserType = Manager::getService("UserTypes")->findOne($filters);
        $admin=[
            "name"=>$request->getParam("name"),
            "email"=>$request->getParam("email"),
            "login"=>$request->getParam("login"),
            "status"=>"approved",
            "taxonomy"=>[],
            "fields"=>[],
            "salt"=>$salt,
            "defaultGroup"=>$adminGroup["id"],
            "typeId"=>$defaultUserType["id"],
            "password"=>$hashService->derivatePassword($request->getParam("password"), $salt),
        ];
        $userService = Manager::getService('Users');
        $response = $userService->create($admin);
        if ($response['success']){
            $groupService = Manager::getService('Groups');
            $adminGroup['members'][] = $response['data']['id'];
            $groupService->update($adminGroup);
            if($this->config["installed"]["status"]!="finished"&&$this->config["installed"]["action"]=='set-admin'){
                $this->config["installed"]=array(
                    'status' => 'finished',
                    'action' => 'set-php-setting',
                );
            }
            $this->installObject->saveLocalConfig($this->config);
            $this->console->writeLine("Admin user created", ColorInterface::GREEN);

        } else {
            $this->console->writeLine("Admin user creation failed", ColorInterface::RED);
        }
        AbstractCollection::disableUserFilter($wasFiltered);
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

    public function setfinishedtAction(){
        $request = $this->getRequest();
        if(!$this->getRequest() instanceof ConsoleRequest) {
            throw new \RuntimeException("You can only call this action from the console");
        }
        $this->config["installed"]=array(
            'status' => 'finished',
            'action' => 'set-php-setting',
        );
        $this->installObject->saveLocalConfig($this->config);
        $this->console->writeLine("Install status set to finish", ColorInterface::GREEN);
        return;
    }

    public function setdefaultAction(){
        $request = $this->getRequest();
        if(!$this->getRequest() instanceof ConsoleRequest) {
            throw new \RuntimeException("You can only call this action from the console");
        }
        if (!isset($this->config["rubedo_config"])){
            $this->config["rubedo_config"]=[];
        }
        $this->config["rubedo_config"]['minify']="1";
        $this->config["rubedo_config"]['cachePage']="1";
        $this->config["rubedo_config"]['apiCache']="1";
        $this->config["rubedo_config"]['addECommerce']="1";
        $this->installObject->saveLocalConfig($this->config);
        $this->console->writeLine("Default settings applied", ColorInterface::GREEN);
        return;
    }

    public function createsiteAction()
    {
        $request = $this->getRequest();
        if (!$this->getRequest() instanceof ConsoleRequest) {
            throw new \RuntimeException("You can only call this action from the console");
        }
        $wasFiltered = AbstractCollection::disableUserFilter();
        $theme=$request->getParam("theme") ? $request->getParam("lang") : "default";
        $newSite=[
            "text"=>$request->getParam("domain"),
            "defaultLanguage"=>$request->getParam("lang"),
            "languages"=>[$request->getParam("lang")],
            "protocol"=>['HTTP'],
            "theme"=>$theme,
            "author"=> "Powered by Rubedo",
            "workspace"=> "global",
            "builtOnEmptySite"=> true,
            "locStrategy"=> "onlyOne",
            "useBrowserLanguage"=> false,
            "enableECommerceFeatures"=> false,
            "locale"=> "en",
            "nativeLanguage"=> "en",
            "title"=> "",
            "description"=> "",
            "i18n"=>[]
        ];
        $newSite["i18n"][$request->getParam("lang")]=[
            "title"=> "",
            "description"=> "",
            "author"=> "Powered by Rubedo"
        ];
        $result=Manager::getService('Sites')->createFromEmpty($newSite);
        if ($result["success"]){
            $this->console->writeLine("Site created", ColorInterface::GREEN);

        } else {
            $this->console->writeLine("Site creation failed", ColorInterface::RED);
        }
        AbstractCollection::disableUserFilter($wasFiltered);
        return;
    }


}
