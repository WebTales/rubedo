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

use Rubedo\Collection\AbstractCollection;
use Rubedo\Collection\Pages;
use Rubedo\Services\Manager;

/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class PagesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Rubedo\Mongo\DataAccess
     */
    private $mockDataAccessService;
    /**
     * @var \Rubedo\Collection\UrlCache
     */
    private $mockUrlCacheService;

    /**
     * @var \Rubedo\Mongo\WorkflowDataAccess
     */
    private $mockWorkflowDataAccessService;

    /**
     * @var \Rubedo\User\CurrentUser
     */
    private $mockCurrentUserService;

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
        $this->mockUrlCacheService = $this->getMock('Rubedo\Collection\UrlCache');
        Manager::setMockService('UrlCache', $this->mockUrlCacheService);
        $this->mockWorkflowDataAccessService = $this->getMock('Rubedo\Mongo\WorkflowDataAccess');
        Manager::setMockService('MongoWorkflowDataAccess', $this->mockWorkflowDataAccessService);
        $this->mockCurrentUserService = $this->getMock('Rubedo\User\CurrentUser');
        Manager::setMockService('CurrentUser', $this->mockCurrentUserService);
        $this->mockCurrentUserService->expects($this->any())
            ->method("getWriteWorkspaces")
            ->will($this->returnValue(array(
                "test"
            )));
        $this->mockCurrentUserService->expects($this->any())
            ->method("getReadWorkspaces")
            ->will($this->returnValue(array(
                "test"
            )));
        parent::setUp();
    }

    public function testNormalMatchSegment()
    {
        $this->mockDataAccessService->expects($this->once())->method('findOne');

        $urlSegment = "segment";
        $parentId = "parent";
        $siteId = "site";
        $PageService = new Pages();
        $PageService->matchSegment($urlSegment, $parentId, $siteId);
    }

    public function testNormalDestroy()
    {
        $this->mockDataAccessService->expects($this->once())->method('customDelete');
        $this->mockDataAccessService->expects($this->once())->method('readChild')->will($this->returnValue(array()));
        $this->mockUrlCacheService->expects($this->once())->method('customDelete');
        $obj['id'] = 'test';
        $PageService = new Pages();
        $PageService->destroy($obj);
    }

    public function testNormalUpdate()
    {
        $this->mockDataAccessService->expects($this->once())->method('update');
        $this->mockDataAccessService->expects($this->once())->method('readChild')->will($this->returnValue(array()));
        $this->mockUrlCacheService->expects($this->once())->method('customDelete');
        $obj['id'] = 'test';
        $obj['site'] = 'test';
        $obj['title'] = 'test';
        $obj['i18n'] = array('en' => array('title' => $obj['title']));
        $obj['nativeLanguage'] = 'en';
        $obj['writeWorkspace'] = 'test';
        $PageService = new Pages();
        AbstractCollection::disableUserFilter(true);
        $PageService->update($obj);
        AbstractCollection::disableUserFilter(false);
    }

    public function testNormalFindByNameAndSite()
    {
        $this->mockDataAccessService->expects($this->once())->method('findOne');
        $name = "name";
        $siteId = "site";
        $PageService = new Pages();
        $PageService->findByNameAndSite($name, $siteId);
    }
}
