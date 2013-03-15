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

Use Rubedo\Collection\UrlCache;

/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class UrlCacheTest extends PHPUnit_Framework_TestCase {
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


        parent::setUp();
    }
	/*
	 * test if findByPageId function start findOne funtion once.
	 */
	public function testNormalfindByPageId(){
		$this->_mockDataAccessService->expects($this->once())->method('findOne');
		
		$pageId="testId";
		$urlCacheService=new UrlCache();
		$urlCacheService->findByPageId($pageId);
	}
		/*
	 * test if findByUrl function start findOne funtion once.
	 */
	public function testNormalfindByUrl(){
		$this->_mockDataAccessService->expects($this->once())->method('findOne');
		
		$url="testId";
		$siteId="testSiteId";
		$urlCacheService=new UrlCache();
		$urlCacheService->findByUrl($url, $siteId);
	}
	/*
	 * test if create fuction works fine.
	 */
	public function testNormalCreate(){
			$this->_mockDataAccessService->expects($this->once())->method('getMongoDate');
			$this->_mockDataAccessService->expects($this->once())->method('create');
		
		$obj["test"]="test";
		$urlCacheService=new UrlCache();
		$urlCacheService->create($obj);
	}
	/*
	 * test if verifyIndexes function start ensureIndex twice.
	 */
	public function testVerifyIndexes(){
		$this->_mockDataAccessService->expects($this->exactly(2))->method('ensureIndex');
		
		$obj["test"]="test";
		$urlCacheService=new UrlCache();
		$urlCacheService->verifyIndexes();
		
	}
	
	
	
	
}
