<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
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
        testBootstrap();
        $this->_mockDataAccessService = $this->getMock('Rubedo\\Mongo\\DataAccess');
        Rubedo\Services\Manager::setMockService('MongoDataAccess', $this->_mockDataAccessService);


        parent::setUp();
    }
	public function testGetHostWithArray()	
	{
		$this->_mockDataAccessService->expects($this->never())->method('findById');
		Sites::setOverride(array('namesite.test'=>'toto'));
		$site['text']="namesite.test";
		$siteService=new Sites();
		$siteService->getHost($site);
	}
	public function testGetHostWithString()	
	{
		$findReturn['text']="text";
		$this->_mockDataAccessService->expects($this->once())->method('findById')
		 		->will($this->returnValue($findReturn));
		Sites::setOverride(array('text'=>'toto'));
		$site="text";
		$siteService=new Sites();
		$siteService->getHost($site);
	}
	public function testFindHostWithBadSite()
	{
		$this->_mockDataAccessService->expects($this->once())->method('findByName');
		$this->_mockDataAccessService->expects($this->once())->method('findOne');
		Sites::setOverride(array('value'=>'text'));
		$host="text";
		$siteService=new Sites();
		$siteService->findByHost($host);
	}
	
	
	
	
	
}

	