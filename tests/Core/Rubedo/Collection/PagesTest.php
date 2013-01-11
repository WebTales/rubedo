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
		$PageService=new Rubedo\Collection\Pages();
		$PageService->matchSegment($urlSegment, $parentId, $siteId);
	}
	
	public function testNormalDestroy()
	{
		$this->_mockDataAccessService->expects($this->once())->method('destroy');
		$this->_mockUrlCacheService->expects($this->once())->method('customDelete');
		$obj['id']='test';
		$PageService=new Rubedo\Collection\Pages();
		$PageService->destroy($obj);
	}
	public function testNormalUpdate(){
		$this->_mockDataAccessService->expects($this->once())->method('update');
		$this->_mockUrlCacheService->expects($this->once())->method('customDelete');
		$obj['id']='test';
		$PageService=new Rubedo\Collection\Pages();
		$PageService->update($obj);
	}
	
	public function testNormalFindByNameAndSite()
	{
		$this->_mockDataAccessService->expects($this->once())->method('findOne');
		$name="name";
		$siteId="site";
		$PageService=new Rubedo\Collection\Pages();
		$PageService->findByNameAndSite($name, $siteId);
	}
}
