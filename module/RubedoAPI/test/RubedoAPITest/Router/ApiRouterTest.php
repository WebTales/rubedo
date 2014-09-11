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

namespace RubedoAPITest\Router;

use RubedoAPI\Router\ApiRouter;
use Zend\Http\PhpEnvironment\Request;

class ApiRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \RubedoAPI\Router\ApiRouter
     */
    protected $router;

    public function setUp()
    {
        $this->router = new ApiRouter();
    }

    public function testMatch()
    {
        $request = new Request();
        $uri = $request->getUri();
        $uri->setPath('/api/v1/pages');
        $request->setUri($uri);
        $match = $this->router->match($request);
        $params = $match->getParams();
        $this->assertEquals('RubedoApi\Frontoffice\Controller\Api', $params['controller']);
        $this->assertEquals('index', $params['action']);
        $this->assertEquals('v1', $params['version']);
        $this->assertArrayHasKey('api', $params);
    }
}