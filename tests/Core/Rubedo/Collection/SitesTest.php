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

Use Rubedo\Collection\Sites;

/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class SitesTest extends PHPUnit_Framework_TestCase {
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
        Rubedo\Services\Manager::setMockService('MongoDataAccess', $this->_mockDataAccessService);


        parent::setUp();
    }
	public function testGetHostWithArray()	
	{
		$this->_mockDataAccessService->expects($this->never())->method('findById');
		Rubedo\Collection\Sites::setOverride(array('namesite.test'=>'toto'));
		$site['text']="namesite.test";
		$siteService=new Rubedo\Collection\Sites();
		$siteService->getHost($site);
	}
	public function testGetHostWithString()	
	{
		$findReturn['text']="text";
		$this->_mockDataAccessService->expects($this->once())->method('findById')
		 		->will($this->returnValue($findReturn));
		Rubedo\Collection\Sites::setOverride(array('text'=>'toto'));
		$site="text";
		$siteService=new Rubedo\Collection\Sites();
		$siteService->getHost($site);
	}
	public function testFindHostWithBadSite()
	{
		$this->_mockDataAccessService->expects($this->once())->method('findByName');
		$this->_mockDataAccessService->expects($this->once())->method('findOne');
		Rubedo\Collection\Sites::setOverride(array('value'=>'text'));
		$host="text";
		$siteService=new Rubedo\Collection\Sites();
		$siteService->findByHost($host);
	}
	
	
	
	
	
}

	