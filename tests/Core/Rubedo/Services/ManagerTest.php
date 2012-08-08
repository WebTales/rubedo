<?php

/**
 * Mock Service Interface for unit test
 */
interface ITestService
{
    public function fakeMethod();

}

/**
 * Mock Valid Service Class
 */
class TestService implements ITestService
{
    public function fakeMethod()
    {
        return 42;
    }

}

/**
 * Mock Service Class Without Correct Interface
 */
class TestServiceNoInterface
{
}

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

class ManagerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this -> bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }

    /**
     * Test if setOptions normal result
     */
    public function testConformOptions()
    {
        $options = array('fakeService' => array('fakeOptions1' => true, 'fakeOptions2' => 'value2'));
        \Rubedo\Services\Manager::setOptions($options);
        $this -> assertEquals($options, \Rubedo\Services\Manager::getOptions());
    }

    /**
     * Test if setOptions correctly throw exception if params isn't an array
     *
     * @expectedException \Rubedo\Exceptions\ServiceManager
     */
    public function testMalformedOptions()
    {
        $options = 'nonArrayInput';
        Rubedo\Services\Manager::setOptions($options);
    }

    /**
     * Normal getService Result
     */
    public function testValidGetService()
    {
        $options = array('TestService' => array('class' => 'TestService'));
        Rubedo\Services\Manager::setOptions($options);
        Rubedo\Interfaces\config::addInterface('TestService', 'ITestService');

        $service = \Rubedo\Services\Manager::getService('TestService');
        $this -> assertInstanceOf('\\Rubedo\\Services\\Manager', $service);
        $this -> assertInstanceOf('TestService', $service -> getServiceObj());
    }

    /**
     * GetService Exception if called without a string param
     *
     * @expectedException \Rubedo\Exceptions\ServiceManager
     */
    public function testNonStringGetService()
    {

        $service = \Rubedo\Services\Manager::getService(666);
    }

    /**
     *GetService Exception if called with an unknown serviceName
     *
     * @expectedException \Rubedo\Exceptions\ServiceManager
     */
    public function testNonDeclaredServiceGetService()
    {

        $service = \Rubedo\Services\Manager::getService('TestService');
    }

    /**
     * GetService Exception if called without an undeclared interface
     *
     * @expectedException \Rubedo\Exceptions\ServiceManager
     */
    public function testNonDeclaredInterfaceGetService()
    {
        $classname = $this -> getMockClass('TestService');
        $options = array('TestService' => array('class' => $classname));
        \Rubedo\Services\Manager::setOptions($options);
        $service = \Rubedo\Services\Manager::getService('TestService');
    }

    /**
     * GetService Exception if the service class do not implement the service class
     *
     * @expectedException \Rubedo\Exceptions\ServiceManager
     */
    public function testDontImplementdInterfaceGetService()
    {
        $options = array('TestService' => array('class' => 'TestServiceNoInterface'));
        Rubedo\Services\Manager::setOptions($options);
        Rubedo\Interfaces\config::addInterface('TestServiceNoInterface', 'ITestService');

        $service = \Rubedo\Services\Manager::getService('TestService');
    }

    /**
     * Failed method call : non existent method in nested object
     *
     * @expectedException \Rubedo\Exceptions\ServiceManager
     *
     */
    public function testInvalidMethodCall()
    {
        $options = array('TestService' => array('class' => 'TestService'));
        Rubedo\Services\Manager::setOptions($options);
        Rubedo\Interfaces\config::addInterface('TestService', 'ITestService');

        $service = \Rubedo\Services\Manager::getService('TestService');
        $this -> assertInstanceOf('\\Rubedo\\Services\\Manager', $service);
        $this -> assertInstanceOf('TestService', $service -> getServiceObj());
        $service -> otherFakeMethod();
    }

    /**
     * Valid nested method call
     */
    public function testValidMethodCall()
    {
        $options = array('TestService' => array('class' => 'TestService'));
        Rubedo\Services\Manager::setOptions($options);
        Rubedo\Interfaces\config::addInterface('TestService', 'ITestService');
        Rubedo\Interfaces\config::clearConcerns();

        $service = \Rubedo\Services\Manager::getService('TestService');
        $this -> assertInstanceOf('\\Rubedo\\Services\\Manager', $service);
        $this -> assertInstanceOf('TestService', $service -> getServiceObj());
        $this -> assertEquals(42, $service -> fakeMethod());
    }

    /**
     * Valid nested method call with concerns
     */
    public function testValidMethodCallWithConcerns()
    {
        $options = array('TestService' => array('class' => 'TestService'));
        Rubedo\Services\Manager::setOptions($options);
        Rubedo\Interfaces\config::addInterface('TestService', 'ITestService');
        Rubedo\Interfaces\config::clearConcerns();
        Rubedo\Interfaces\config::addConcern('testConcern');

        $service = \Rubedo\Services\Manager::getService('TestService');
        $this -> assertInstanceOf('\\Rubedo\\Services\\Manager', $service);
        $this -> assertInstanceOf('TestService', $service -> getServiceObj());
        $this -> assertEquals(42, $service -> fakeMethod());
    }

}
