<?php


class ProxyTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this -> bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }


	public function testOverrideProxyClass(){
		$mock = $this->getMock('Rubedo\Mongo\DataAccess');
		$service = new Rubedo\Services\Proxy('Rubedo\Mongo\DataAccess','MongoDataAccess',$mock);
		$this->assertInstanceOf('Rubedo\Mongo\DataAccess', $service->getServiceObj());
		$this->assertEquals($mock, $service->getServiceObj());
	}

}
