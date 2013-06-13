<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

Use Rubedo\Collection\Icons, WebTales\MongoFilters\Filter;
 
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
        testBootstrap();
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
		$filters = Filter::factory('Value')->setName('userId')->setValue('123456789');
		$this->_mockDataAccessService->expects($this->once())->method('addFilter')->with($this->equalTo($filters));
		$this->_mockDataAccessService->expects($this->once())->method('read');
		
		$collection = new Icons();
		$collection->getList();
	}
	
	/**
	 * Test if the filter is set with the id given by the current user service in update method
	 */
	public function testNormalUpdate(){
		$this->_mockCurrentUser->expects($this->once())->method('getCurrentUserSummary')->will($this->returnValue(array('id' => '123456789')));
		$filters = Filter::factory('Value')->setName('userId')->setValue('123456789');
		$this->_mockDataAccessService->expects($this->once())->method('addFilter')->with($this->equalTo($filters));
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
		$filters = Filter::factory('Value')->setName('userId')->setValue('123456789');
		$this->_mockDataAccessService->expects($this->once())->method('addFilter')->with($this->equalTo($filters));
		$this->_mockDataAccessService->expects($this->once())->method('destroy');
		
		$obj = array('key' => 'value');
		
		$collection = new Icons();
		$collection->destroy($obj);
	}
}
