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
namespace RubedoTest\Collection;

use Rubedo\Collection\Sites;
use Rubedo\Services\Manager;

/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class SitesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Rubedo\Mongo\DataAccess
     */
    private $mockDataAccessService;

    /**
     * clear the DB of the previous test data
     */
    public function tearDown()
    {
        Manager::resetMocks();
    }

    /**
     * init the Zend Application for tests
     */
    public function setUp()
    {
        $this->mockDataAccessService = $this->getMock('Rubedo\Mongo\DataAccess');
        Manager::setMockService('MongoDataAccess', $this->mockDataAccessService);

        parent::setUp();
    }

    public function testGetHostWithArray()
    {
        $this->mockDataAccessService->expects($this->never())->method('findById');
        Sites::setOverride(array('namesite.test' => 'toto'));
        $site['text'] = "namesite.test";
        $siteService = new Sites();
        $siteService->getHost($site);
    }

    public function testGetHostWithString()
    {
        $findReturn['text'] = "text";
        $this->mockDataAccessService->expects($this->once())->method('findById')
            ->will($this->returnValue($findReturn));
        Sites::setOverride(array('text' => 'toto'));
        $site = "text";
        $siteService = new Sites();
        $siteService->getHost($site);
    }

    public function testFindHostWithBadSite()
    {
        $this->mockDataAccessService->expects($this->once())->method('findByName');
        $this->mockDataAccessService->expects($this->once())->method('findOne');
        Sites::setOverride(array('value' => 'text'));
        $host = "text";
        $siteService = new Sites();
        $siteService->findByHost($host);
    }


}

	