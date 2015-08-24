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
class SearchController extends AbstractActionController
{

    private $console;

    public function __construct() {
        $this->console = Console::getInstance();
    }

    public function indexAction()
    {
        $request = $this->getRequest();

        if(!$this->getRequest() instanceof ConsoleRequest) {
            throw new \RuntimeException("You can only call this action from the console");
        }

        $request->getParam("type");

        $option = $request->getParam("type") ? $request->getParam("type") : 'all';

        $es = Manager::getService('ElasticDataIndex');
        $es->init();
        $return = $es->indexAll($option);

        return json_encode($return) . "\r\n";
    }

}
