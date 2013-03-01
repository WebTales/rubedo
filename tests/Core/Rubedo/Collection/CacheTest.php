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

	