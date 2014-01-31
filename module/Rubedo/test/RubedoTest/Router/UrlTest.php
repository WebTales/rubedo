<?php
namespace RubedoTest\Router;

use Rubedo\Router\Url;
use Rubedo\Services\Manager;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPageContentService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSitesService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPagesService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRouterService;

    /**
     * Cleaning
     */
    public function tearDown()
    {
        Manager::resetMocks();
        parent::tearDown();
    }


    /**
     * init the Zend Application for tests
     */
    public function setUp()
    {
        $this->mockPageContentService = $this->getMock('Rubedo\Content\Page');
        Manager::setMockService('PageContent', $this->mockPageContentService);

        $this->mockSitesService = $this->getMock('Rubedo\Collection\Sites');
        Manager::setMockService('Sites', $this->mockSitesService);

        $this->mockPagesService = $this->getMock('Rubedo\Collection\Pages');
        Manager::setMockService('Pages', $this->mockPagesService);

        $this->mockRouterService = $this->getMock('Rubedo\Router\Route');
        Manager::setMockService('Route', $this->mockRouterService);
        parent::setUp();
    }

    /**
     * Check if "currentSite" is called when invoking singleUrl without a siteId
     */
    public function testDoSingleUrlCallCurrentSiteIfNoSiteGiven()
    {
        $this->mockPageContentService->expects($this->once())
            ->method('getCurrentSite');


        $urlService = new Url();
        $urlService->displayUrl('123456789012345678901234');


    }

    /**
     * Check if "currentSite" is not called when invoking singleUrl with a siteId
     */
    public function testDoSingleUrlNotCallCurrentSiteIfSiteGiven()
    {
        $this->mockPageContentService->expects($this->never())
            ->method('getCurrentSite');


        $urlService = new Url();
        $urlService->displayUrl('123456789012345678901234', "default", '123456789012345678901234');


    }

    /**
     * Check if "getHost" is called once when invoking singleUrl with a siteId
     */
    public function testDoSingleUrlNotCallGetHostIfNoSiteGiven()
    {
        $this->mockSitesService->expects($this->never())
            ->method('getHost');

        $urlService = new Url();
        $urlService->displayUrl('123456789012345678901234', "default", '123456789012345678901234');
    }
}