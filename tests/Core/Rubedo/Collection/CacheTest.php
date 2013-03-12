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

Use Rubedo\Collection\Cache;

/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class CacheTest extends PHPUnit_Framework_TestCase {
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
		 $this->_mockCurrentTimeService = $this->getMock('Rubedo\\Time\\CurrentTime');
        Rubedo\Services\Manager::setMockService('CurrentTime', $this->_mockCurrentTimeService);
		

        parent::setUp();
    }
	
	public function testDeleteByCacheId(){
		$this->_mockDataAccessService->expects($this->once())->method('customDelete');
		$id="testId";
		$cacheService=new Cache();
		$cacheService->deleteByCacheId($id);
	}
	public function testDeleteExpired(){
		$this->_mockCurrentTimeService->expects($this->once())->method('getCurrentTime');
		$this->_mockDataAccessService->expects($this->once())->method('customDelete');
		$cacheService=new Cache();
		$cacheService->deleteExpired();
	}

	public function testUpsertByCacheId()
	{

		$this->_mockDataAccessService->expects($this->once())->method('customUpdate');
		$obj=array('value'=>'test', 'data' => 'test', 'cacheId' => 'test');
		$cacheId="testChache";
		$cacheService=new Cache();
		$cacheService->upsertByCacheId($obj, $cacheId);
	}
	public function testFindByCacheId(){
		$this->_mockDataAccessService->expects($this->once())->method('findOne');
		$cacheId="testChache";
		$cacheService=new Cache();
		$cacheService->findByCacheId($cacheId,"time");
	}
	
}

	