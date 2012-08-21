<?php

class Backoffice_DataAccessControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
        $this->bootstrap = new Zend_Application('testing', APPLICATION_PATH . '/configs/application.ini');
		
        parent::setUp();
    }
	
	public function tearDown(){
		$this->resetRequest();
		$this->resetResponse();
		parent::tearDown();
	}
	
	public function testDispatchJson(){
		$front = Zend_Controller_Front::getInstance();
		$front->setParam('noErrorHandler', true);
		$this->dispatch('/backoffice/data/Blocs.json?_dc=1345541033897&page=1&start=0&limit=25');
		$this->assertModule('backoffice');
		$this->assertController('data-access');
		$this->assertAction('index');
	}
	
	public function testDispatchDelete(){
		$front = Zend_Controller_Front::getInstance();
		$front->setParam('noErrorHandler', true);
		$this->dispatch('/backoffice/data-access/delete');
		$this->assertModule('backoffice');
		$this->assertController('data-access');
		$this->assertAction('delete');		
	}
	
	public function testDispatchUpdate(){
		$front = Zend_Controller_Front::getInstance();
		$front->setParam('noErrorHandler', true);
		$this->dispatch('/backoffice/data-access/update');
		$this->assertModule('backoffice');
		$this->assertController('data-access');
		$this->assertAction('update');		
	}
	
	public function testDispatchCreate(){
		$front = Zend_Controller_Front::getInstance();
		$front->setParam('noErrorHandler', true);
		$this->dispatch('/backoffice/data-access/create');
		$this->assertModule('backoffice');
		$this->assertController('data-access');
		$this->assertAction('create');		
	}


}

