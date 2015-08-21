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

use Rubedo\Services\Manager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Console;
use Zend\Console\ColorInterface;
use Rubedo\Update\Install;
use Rubedo\Services\Cache;

/**
 * Console cache controller
 *
 * Invoked when calling from console
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
class CacheController extends AbstractActionController
{

    private $console;

    private $installObject;

    public function __construct() {
        $this->console = Console::getInstance();
        $this->installObject = new Install();
    }

    public function countAction()
    {
        $this->console->writeLine("Mongo items in cache: " . Manager::getService('Cache')->count(), ColorInterface::GREEN);
        $this->console->writeLine("URL in cache: " . Manager::getService('UrlCache')->count(), ColorInterface::GREEN);
        $this->console->writeLine("API requests in cache: " . Manager::getService('ApiCache')->count(), ColorInterface::GREEN);

        return;
    }

    public function clearAction()
    {
        $request = $this->getRequest();

        if(!$this->getRequest() instanceof ConsoleRequest) {
            throw new \RuntimeException("You can only call this action from the console");
        }

        switch($request->getParam("name")) {
            case "config":
                $this->clearConfigCache();
                break;
            case "files":
                $this->clearFilesCache();
                break;
            case "mongo":
                $this->clearMongoCache();
                break;
            case "url":
                $this->clearUrlCache();
                break;
            case "api":
                $this->clearApiCache();
                break;
            default:
                $this->clearConfigCache();
                $this->clearFilesCache();
                $this->clearMongoCache();
                $this->clearUrlCache();
                $this->clearApiCache();
                break;
        }

        return;
    }

    private function clearConfigCache() {
        $this->installObject->clearConfigCache();
        $this->console->writeLine("Config cache cleared", ColorInterface::GREEN);
    }

    private function clearFilesCache() {
        $this->installObject->clearFileCaches();
        $this->console->writeLine("Files cache cleared", ColorInterface::GREEN);
    }

    private function clearMongoCache() {
        Cache::getCache()->clean();
        $this->console->writeLine("Mongo cache cleared:\t" . Manager::getService('Cache')->count(), ColorInterface::GREEN);
    }

    private function clearUrlCache() {
        Manager::getService('UrlCache')->drop();
        Manager::getService('UrlCache')->ensureIndexes();
        $this->console->writeLine("URL cache cleared:\t" . Manager::getService('UrlCache')->count(), ColorInterface::GREEN);
    }

    private function clearApiCache() {
        Manager::getService('ApiCache')->drop();
        Manager::getService('ApiCache')->ensureIndexes();
        $this->console->writeLine("API cache cleared:\t" . Manager::getService('ApiCache')->count(), ColorInterface::GREEN);
    }
}
