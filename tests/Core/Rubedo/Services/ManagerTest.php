<?php
use \Rubedo\Services\Manager;
class ManagerTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }
	
    public function testConformOptions()
    {
        $options = array('fakeService'=>array('fakeOptions1'=>true,'fakeOptions2'=>'value2'));
		Manager::setOptions($options);
		$this->assertEquals($options, Manager::getOptions());
    }
	
	/**
	 * @expectedException \Rubedo\Exceptions\ServiceManager
	 */
	public function testMalformedOptions()
    {
        $options = 'nonArrayInput';
		Manager::setOptions($options);
		//$this->assertEquals($options, Manager::getOptions());
    }
    
    public function testMongoGetService(){
        $classname = $this->getMockClass('Rubedo\Mongo\DataAccess');
        $options = array('MongoDataAccess'=>array('class'=>$classname));
        Manager::setOptions($options);
        $service = Manager::getService('MongoDataAccess');
        $this->assertInstanceOf('\\Rubedo\\Services\\Manager', $service);
        $this->assertInstanceOf($classname, $service->getServiceObj());
    }
    
     /**
     * @expectedException \Rubedo\Exceptions\ServiceManager
     */
    public function testNonStringGetService(){

        $service = Manager::getService(666);
    }
   
    /**
     * @expectedException \Rubedo\Exceptions\ServiceManager
     */
    public function testNonDeclaredServiceGetService(){

        $service = Manager::getService('TestService');
    }
    
     /**
     * @expectedException \Rubedo\Exceptions\ServiceManager
     */
    public function testNonDeclaredInterfaceGetService(){
        $classname = $this->getMockClass('TestService');
        $options = array('TestService'=>array('class'=>$classname));
        Manager::setOptions($options);
        $service = Manager::getService('TestService');
    }
}