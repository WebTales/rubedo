<?php

class testConcern
{
    public function __construct($injecteurArray, $options)
    {

    }

    public function process($object, $name, $arguments)
    {
        return call_user_func_array(array($object, $name), $arguments);
    }

}

class ProxyTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		$this->bootstrap->bootstrap();
        parent::setUp();
    }

    /**
     * Test Service Override
     */
    public function testOverrideProxyClass()
    {
        $mock = $this->getMock('Rubedo\Mongo\DataAccess');
        $service = new Rubedo\Services\Proxy('Rubedo\Mongo\DataAccess', 'MongoDataAccess', $mock);
        $this->assertInstanceOf('Rubedo\Mongo\DataAccess', $service->getServiceObj());
        $this->assertEquals($mock, $service->getServiceObj());
    }

    /**
     * Failed method call : non existent method in nested object
     *
     * @expectedException \Rubedo\Exceptions\ServiceManager
     */
    public function testInvalidMethodCall()
    {
        $mock = new stdClass();

        $service = new Rubedo\Services\Proxy('stdClass', 'TestService', $mock);
        $this->assertInstanceOf('\\Rubedo\\Services\\Proxy', $service);
        $this->assertAttributeInstanceOf('stdClass', '_object', $service);
        $this->assertTrue(!method_exists($service->getServiceObj(), 'FakeMethod'));
        $service->FakeMethod();
    }

    /**
     * Valid nested method call
     */
    public function testValidMethodCall()
    {
        $mock = $this->getMock('TestService');
        $mock->expects($this->once())->method('fakeMethod')->will($this->returnValue(42));

        $service = new Rubedo\Services\Proxy('TestService', 'TestService', $mock);
        $this->assertInstanceOf('\\Rubedo\\Services\\Proxy', $service);
        $this->assertAttributeInstanceOf('TestService', '_object', $service);

        $this->assertEquals(42, $service->fakeMethod());
    }

    /**
     * Valid nested method call with concerns
     */
    public function testValidMethodCallWithConcerns()
    {
        $mock = $this->getMock('TestService');
        $mock->expects($this->once())->method('fakeMethod')->will($this->returnValue(42));

        Rubedo\Interfaces\config::clearConcerns();
        Rubedo\Interfaces\config::addConcern('testConcern');

        $service = new Rubedo\Services\Proxy('TestService', 'TestService', $mock);
        $this->assertInstanceOf('\\Rubedo\\Services\\Proxy', $service);
        $this->assertAttributeInstanceOf('TestService', '_object', $service);
        $this->assertEquals(42, $service->fakeMethod());
    }

}
