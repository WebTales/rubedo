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

namespace RubedoAPITest\Traits;

use Rubedo\Services\Manager;
use RubedoAPI\Traits\LazyServiceManager;

class LazyServiceManagerTest extends \PHPUnit_Framework_TestCase
{
    use LazyServiceManager;

    public function tearDown()
    {
        Manager::resetMocks();
    }

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testServiceNotFoundException()
    {
        $this->getFooService();
    }

    public function testGetService()
    {
        $mockService = $this->getMock('stdClass');
        Manager::setMockService('Foo', $mockService);
        $this->assertEquals($mockService, $this->getFooService());

        Manager::setMockService('API\\Collection\\Foo', $mockService);
        $this->assertEquals($mockService, $this->getFooAPICollection());

        Manager::setMockService('API\\Services\\Foo', $mockService);
        $this->assertEquals($mockService, $this->getFooAPIService());
    }

    public function testCache()
    {
        $mockService = $this->getMock('stdClass');
        Manager::setMockService('Foo', $mockService);
        $this->getFooService();
        $this->assertArrayHasKey('getFooService', $this->callCache);

    }
} 