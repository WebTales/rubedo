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

Use Rubedo\Collection\Icons;
 
/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class IconsTest extends PHPUnit_Framework_TestCase {
	/**
     * clear the DB of the previous test data
     */
    public function tearDown() {
        Rubedo\Services\Manager::resetMocks();
    }

    /**
     * init the Zend Application for tests
     */
    public function setUp() {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		$this->bootstrap->bootstrap();
        $this->_mockDataAccessService = $this->getMock('Rubedo\\Mongo\\DataAccess');
		$this->_mockCurrentUser = $this->getMock('Rubedo\\User\\CurrentUser');
        Rubedo\Services\Manager::setMockService('MongoDataAccess', $this->_mockDataAccessService);
		Rubedo\Services\Manager::setMockService('CurrentUser', $this->_mockCurrentUser);

        parent::setUp();
    }
	
	/**
	 * Test if the create method use the id given by the current user service
	 */
	public function testNormalCreate(){
		$this->_mockCurrentUser->expects($this->once())->method('getCurrentUserSummary')->will($this->returnValue(array('id' => '123456789')));
		$this->_mockDataAccessService->expects($this->once())->method('create');
		
		$obj = array('key' => 'value');
		
		$collection = new Icons();
		$collection->create($obj, true);
	}
	
	/**
	 * Test if the filter is set with the id given by the current user service in getList method
	 */
	public function testNormalGetList(){
		$this->_mockCurrentUser->expects($this->once())->method('getCurrentUserSummary')->will($this->returnValue(array('id' => '123456789')));
		$this->_mockDataAccessService->expects($this->once())->method('addFilter')->with($this->equalTo(array('userId' => '123456789')));
		$this->_mockDataAccessService->expects($this->once())->method('read');
		
		$collection = new Icons();
		$collection->getList();
	}
	
	/**
	 * Test if the filter is set with the id given by the current user service in update method
	 */
	public function testNormalUpdate(){
		$this->_mockCurrentUser->expects($this->once())->method('getCurrentUserSummary')->will($this->returnValue(array('id' => '123456789')));
		$this->_mockDataAccessService->expects($this->once())->method('addFilter')->with($this->equalTo(array('userId' => '123456789')));
		$this->_mockDataAccessService->expects($this->once())->method('update');
		
		$obj = array('key' => 'value');
		
		$collection = new Icons();
		$collection->update($obj);
	}
	
	/**
	 * Test if the filter is set with the id given by the current user service in destroy method
	 */
	public function testNormalDestroy(){
		$this->_mockCurrentUser->expects($this->once())->method('getCurrentUserSummary')->will($this->returnValue(array('id' => '123456789')));
		$this->_mockDataAccessService->expects($this->once())->method('addFilter')->with($this->equalTo(array('userId' => '123456789')));
		$this->_mockDataAccessService->expects($this->once())->method('destroy');
		
		$obj = array('key' => 'value');
		
		$collection = new Icons();
		$collection->destroy($obj);
	}
}
