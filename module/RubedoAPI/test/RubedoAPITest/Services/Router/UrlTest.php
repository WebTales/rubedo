<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
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

namespace RubedoAPITest\Services\Router;

use Rubedo\Services\Manager;

class Url extends \RubedoAPI\Services\Router\Url {
    public function url(array $urlOptions = array(), $name = NULL, $reset = false, $encode = true) {
        return 'bar';
    }
}
class UrlTest extends \PHPUnit_Framework_TestCase {
    protected $mockPageContentService;
    protected $mockSitesService;
    protected $mockPagesService;
    protected $mockRouterService;

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
    public function tearDown()
    {
        Manager::resetMocks();
        parent::tearDown();
    }

    public function testDoSingleUrlNotCallGetCurrentSite()
    {
        $this->mockPageContentService->expects($this->never())
            ->method('getCurrentSite');

        $urlService = new Url();
        $urlService->displayUrlApi(array('id' => 'bar'), 'default', '123456789012345678901234', array('id' => 'foo'), 'fr');
    }
} 