<?php

class Backoffice_DataAccessControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
        $this->bootstrap = new Zend_Application('testing', APPLICATION_PATH . '/configs/application.ini');

        parent::setUp();
    }

    public function tearDown()
    {
        $this->resetRequest();
        $this->resetResponse();
        Rubedo\Services\Manager::resetMocks();
        parent::tearDown();
    }

    public function testDispatchJson()
    {
        $mockService = $this->getMock('Rubedo\Mongo\DataAccess');
        $mockService->expects($this->once())->method('Read')->will($this->returnValue(array('id' => 1)));

        Rubedo\Services\Manager::setMockService('MongoDataAccess', $mockService);

        $front = Zend_Controller_Front::getInstance();
        $front->setParam('noErrorHandler', true);
		
        $this->dispatch('/backoffice/data/Blocs.json?_dc=1345541033897&page=1&start=0&limit=25');
		
        $this->assertModule('backoffice');
        $this->assertController('data-access');
        $this->assertAction('index');
    }
	
	public function testDispatchRead()
    {
        $mockService = $this->getMock('Rubedo\Mongo\DataAccess');
        $mockService->expects($this->once())->method('Read')->will($this->returnValue(array('id' => 1)));

        Rubedo\Services\Manager::setMockService('MongoDataAccess', $mockService);

        $front = Zend_Controller_Front::getInstance();
        $front->setParam('noErrorHandler', true);
		
        $this->dispatch('/backoffice/data-access/index/store/fake');
		
        $this->assertModule('backoffice');
        $this->assertController('data-access');
        $this->assertAction('index');
    }

    public function testDispatchDelete()
    {
        $mockService = $this->getMock('Rubedo\Mongo\DataAccess');
        $mockService->expects($this->once())->method('destroy')->will($this->returnValue(array('ok' => 1)));

        $this->getRequest()->setParam('data', json_encode(array('id' => 1)));

        Rubedo\Services\Manager::setMockService('MongoDataAccess', $mockService);
		
        $front = Zend_Controller_Front::getInstance();
        $front->setParam('noErrorHandler', true);
		
        $this->dispatch('/backoffice/data-access/delete/store/fake');
		
        $this->assertModule('backoffice');
        $this->assertController('data-access');
        $this->assertAction('delete');
    }

    public function testDispatchUpdate()
    {
        $mockService = $this->getMock('Rubedo\Mongo\DataAccess');
        $mockService->expects($this->once())->method('update')->will($this->returnValue(array('ok' => 1)));

        $this->getRequest()->setParam('data', json_encode(array('id' => 1, 'content' => 'content')));

		Rubedo\Services\Manager::setMockService('MongoDataAccess', $mockService);
		
        $front = Zend_Controller_Front::getInstance();
        $front->setParam('noErrorHandler', true);
		
        $this->dispatch('/backoffice/data-access/update/store/fake');
		
        $this->assertModule('backoffice');
        $this->assertController('data-access');
        $this->assertAction('update');
    }

    public function testDispatchCreate()
    {
    	$mockService = $this->getMock('Rubedo\Mongo\DataAccess');
        $mockService->expects($this->once())->method('create')->will($this->returnValue(array('ok' => 1)));

        $this->getRequest()->setParam('data', json_encode(array('content' => 'content')));

		Rubedo\Services\Manager::setMockService('MongoDataAccess', $mockService);
		
        $front = Zend_Controller_Front::getInstance();
        $front->setParam('noErrorHandler', true);
		
        $this->dispatch('/backoffice/data-access/create/store/fake');
		
        $this->assertModule('backoffice');
        $this->assertController('data-access');
        $this->assertAction('create');
    }

}
