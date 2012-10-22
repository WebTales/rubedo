<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo-Test
 * @package Rubedo-Test
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */

/**
 * Back Office / Data Access Test Suite
 *
 *
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class Backoffice_DataAccessControllerTest extends AbstractControllerTest
{

    /**
     * Acceptance : Normal call of the read method
     */
    public function testDispatchRead()
    {
        $mockService = $this->getMock('Rubedo\Mongo\DataAccess');
        $mockService->expects($this->once())->method('Read')->will($this->returnValue(array('id' => 1)));
		//$mockService->expects($this->once())->method('addSort')->with($this->equalTo(array('name'=>'desc')));

        Rubedo\Services\Manager::setMockService('MongoDataAccess', $mockService);

        $front = Zend_Controller_Front::getInstance();
        $front->setParam('noErrorHandler', true);
		
		$this->getRequest()->setParam('sort', Zend_Json::encode(array('name'=>'desc')));

        $this->dispatch('/backoffice/data-access/index/store/fake');

        $this->assertModule('backoffice');
        $this->assertController('data-access');
        $this->assertAction('index');
		$this->assertEquals('fake',$this->getRequest()->getParam('store'));
    }

    /**
     *  Acceptance : Legacy Call : redirect xxx.json call for store reading
     */
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
        $this->assertEquals('Blocs', $this->getRequest()->getParam('store'));

    }

    /**
     * Acceptance : Normal call of the Delete method
     */
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

    /**
     * Acceptance : Normal call of the Update method
     */
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

    /**
     * Acceptance : Normal call of the Create method
     */
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
