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


class ExtendedAbstractResource extends \RubedoAPI\Rest\V1\AbstractResource
{

}

;

class AbstractResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExtendedAbstractResource
     */
    protected $resource;

    function setUp()
    {
        $this->resource = new ExtendedAbstractResource();
    }

    function testLazyLoader()
    {
        $this->assertArrayHasKey('RubedoAPI\Traits\LazyServiceManager', $this->class_uses_deep($this->resource));
    }

    function testConstructor()
    {
        $this->assertInstanceOf('RubedoAPI\Entities\API\Definition\DefinitionEntity', $this->resource->getDefinition());
        $this->assertInstanceOf('RubedoAPI\Entities\API\Definition\DefinitionEntity', $this->resource->getEntityDefinition());
    }

    function class_uses_deep($class, $autoload = true)
    {
        $traits = [];
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));
        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }
        return array_unique($traits);
    }

    function testOptionsAction()
    {
        $this->assertTrue(is_array($this->resource->optionsAction()));
    }

    function testOptionsEntityAction()
    {
        $this->assertTrue(is_array($this->resource->optionsEntityAction()));
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIRequestException
     */
    function testHandlerNotFound()
    {
        $this->resource->handler('get', array());
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIRequestException
     */
    function testHandlerEntityNotFound()
    {
        $this->resource->handlerEntity(null, 'get', array());
    }
} 