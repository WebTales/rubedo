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

namespace RubedoAPITest\Entities\API\Definition;

use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;

class ExtendedFilterDefinition extends FilterDefinitionEntity {}

class FilterDefinitionEntityTest extends \PHPUnit_Framework_TestCase {
    /** @var  ExtendedFilterDefinition */
    public $filterDefinition;

    public function setUp()
    {
        $this->filterDefinition = new ExtendedFilterDefinition();
        parent::setUp();
    }

    public function testIsJsonSerializable()
    {
        $this->assertArrayHasKey('JsonSerializable', class_implements($this->filterDefinition));
    }

    public function testDescription()
    {
        $this->filterDefinition->setDescription('Bar');
        $this->assertEquals('Bar', $this->filterDefinition->getDescription());
    }

    public function testFilter()
    {
        $this->filterDefinition->setFilter('boolean');
        $this->assertEquals('boolean', $this->filterDefinition->getFilter());
    }

    public function testKey()
    {
        $this->filterDefinition->setKey('Foo');
        $this->assertEquals('Foo', $this->filterDefinition->getKey());
    }

    public function testRename()
    {
        $this->assertEquals(false, $this->filterDefinition->hasRename());
        $this->filterDefinition->setRename('NewFoo');
        $this->assertEquals(true, $this->filterDefinition->hasRename());
        $this->assertEquals('NewFoo', $this->filterDefinition->getRename());
    }

    public function testRequired()
    {
        $this->assertEquals(false, $this->filterDefinition->isRequired());
        $this->filterDefinition->setRequired();
        $this->assertEquals(true, $this->filterDefinition->isRequired());
    }

    public function testMultivalued()
    {
        $this->assertEquals(false, $this->filterDefinition->isMultivalued());
        $this->filterDefinition->setMultivalued();
        $this->assertEquals(true, $this->filterDefinition->isMultivalued());
    }

    public function testOptionsFilter()
    {
        $this->assertEquals(array(), $this->filterDefinition->getOptionsFilter());
        $this->filterDefinition->setOptionsFilter(array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), $this->filterDefinition->getOptionsFilter());
    }

    public function testFiltering()
    {
        $this->filterDefinition->setKey('Foo');
        $this->assertEquals('WithoutFilter', $this->filterDefinition->filter('WithoutFilter'));
        $this->filterDefinition->setFilter('string');
        $this->assertEquals('WithoutFilter', $this->filterDefinition->filter('<b>WithoutFilter</b>'));
        $this->filterDefinition->setOptionsFilter(FILTER_FLAG_ENCODE_AMP);
        $this->assertEquals('&#38;foo', $this->filterDefinition->filter('<b>&foo</b>'));
        $this->filterDefinition->setMultivalued();
        $this->assertEquals(array('foo'), $this->filterDefinition->filter(array('<b>foo</b>')));
        $this->assertEquals(array('bar' => 'foo'), $this->filterDefinition->filter(array('bar' => '<b>foo</b>')));

        $this->filterDefinition->setFilter('\MongoId');
        $this->filterDefinition->setMultivalued(false);
        $this->assertInstanceOf('\MongoId', $this->filterDefinition->filter('53f60a5dbdc683e053bf7d33'));
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIFilterException
     */
    public function testUnexpectedFilter()
    {
        $this->filterDefinition->setKey('Foo');
        $this->filterDefinition->setFilter('Foo');
        $this->filterDefinition->filter('toFilter');
    }

    public function testJsonSerialize()
    {
        $this->filterDefinition
            ->setFilter('string')
            ->setMultivalued()
            ->setRequired()
        ;
        $array = $this->filterDefinition->jsonSerialize();
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('filter', $array);
        $this->assertArrayHasKey('options', $array);
        $this->assertEquals(array('required', 'multivalued'), $array['options']);
    }
} 