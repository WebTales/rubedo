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
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace RubedoAPI\Rest\V1;
class Foo extends AbstractRessource {
    function __construct()
    {
        echo 'MY FOO';
    }
}

namespace RubedoAPITest\Frontoffice\Controller;


use RubedoAPI\Frontoffice\Controller\ApiController;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;

class ApiControllerTest extends \PHPUnit_Framework_TestCase {
    protected $controller;
    protected $request;
    protected $response;
    protected $event;

    public function setUp()
    {
        $this->controller = new ApiController();
        $this->request = new Request();
        $this->event = new MvcEvent();
        $this->controller->setEvent($this->event);
        $this->routeMatch();
    }

    private function routeMatch(array $array = array()) {
        $array = array_merge(array(
            'controller' => 'RubedoApi\\Frontoffice\\Controller\\Api',
            'action' => 'index',
            'version' => 'v1',
        ), $array);
        $routeMatch = new RouteMatch($array);
        $this->event->setRouteMatch($routeMatch);

    }

    function testIndexActionRessourceNotExist()
    {
        $this->routeMatch(array(
            'api' => array('TestEntryPoint', 'Foo'),
        ));
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $vars = $result->getVariables();

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
        $this->assertArrayHasKey('success', $vars);
        $this->assertArrayHasKey('message', $vars);
        $this->assertFalse($vars['success']);
    }
}