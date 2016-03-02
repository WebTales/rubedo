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
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace RubedoAPITest\Rest\V1;

use Rubedo\Services\Manager;
use RubedoAPI\Rest\V1\MediatypesResource;

class MediatypesResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \RubedoAPI\Rest\V1\MediatypesResource
     */
    protected $resource;
    protected $mediaTypes;

    function setUp()
    {
        $this->resource = new MediatypesResource();
        $this->mediaTypes = $this->getMock('Rubedo\Collection\DamTypes');
        Manager::setMockService('DamTypes', $this->mediaTypes);
        parent::setUp();
    }

    function tearDown()
    {
        Manager::resetMocks();
        parent::tearDown();
    }

    public function testDefinition()
    {
        $this->assertNotNull($this->resource->getDefinition()->getVerb('get'));
        $this->assertNotNull($this->resource->getEntityDefinition()->getVerb('get'));
    }

    public function testGetEntity()
    {
        $this->mediaTypes
            ->expects($this->once())
            ->method('findById')
            ->will($this->returnValue(array()));
        $result = $this->resource->getEntityAction(new \MongoDB\BSON\ObjectId(), array());
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('mediaType', $result);
    }

    public function testGet()
    {
        $this->mediaTypes
            ->expects($this->once())
            ->method('getList')
            ->will($this->returnValue(array('data' => array())));
        $result = $this->resource->getAction(array());
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('mediaTypes', $result);
    }
} 