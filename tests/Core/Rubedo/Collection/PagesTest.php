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

Use Rubedo\Collection\Pages;

/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class PagesTest extends PHPUnit_Framework_TestCase {
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
        Rubedo\Services\Manager::setMockService('MongoDataAccess', $this->_mockDataAccessService);
		$this->_mockUrlCacheService = $this->getMock('Rubedo\\Collection\\UrlCache');
        Rubedo\Services\Manager::setMockService('UrlCache', $this->_mockUrlCacheService);
        parent::setUp();
    }

	public function testNormalMatchSegment(){
		$this->_mockDataAccessService->expects($this->once())->method('findOne');
		$urlSegment="segment";
		$parentId="parent";
		$siteId="site";
		$PageService=new Pages();
		$PageService->matchSegment($urlSegment, $parentId, $siteId);
	}
	
	public function testNormalDestroy()
	{
		$this->_mockDataAccessService->expects($this->once())->method('customDelete');
		$this->_mockDataAccessService->expects($this->once())->method('readChild')->will($this->returnValue(array()));
		$this->_mockUrlCacheService->expects($this->once())->method('customDelete');
		$obj['id']='test';
		$PageService=new Pages();
		$PageService->destroy($obj);
	}
	public function testNormalUpdate(){
		$this->_mockDataAccessService->expects($this->once())->method('update');
		$this->_mockDataAccessService->expects($this->once())->method('readChild')->will($this->returnValue(array()));
		$this->_mockUrlCacheService->expects($this->once())->method('customDelete');
		$obj['id'] = 'test';
		$obj['site'] = 'test';
		$obj['title'] = 'test';
		$PageService=new Pages();
		$PageService->update($obj);
	}
	
	public function testNormalFindByNameAndSite()
	{
		$this->_mockDataAccessService->expects($this->once())->method('findOne');
		$name="name";
		$siteId="site";
		$PageService=new Pages();
		$PageService->findByNameAndSite($name, $siteId);
	}
}
