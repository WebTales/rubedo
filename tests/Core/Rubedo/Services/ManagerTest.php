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
	
	public function testMalformedOptions()
    {
        $options = 'nonArrayInput';
		Manager::setOptions($options);
		$this->assertEquals($options, Manager::getOptions());
    }
}