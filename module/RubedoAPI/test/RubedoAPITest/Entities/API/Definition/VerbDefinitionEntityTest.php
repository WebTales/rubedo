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
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;

class ExtendedVerbDefinitionEntity extends VerbDefinitionEntity {
    public function hasIdentityRequired()
    {
        return parent::hasIdentityRequired();
    }
    public function identityRequired($bool = true)
    {
        parent::identityRequired($bool);
        return $this;
    }
}

class VerbDefinitionEntityTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var ExtendedVerbDefinitionEntity
     */
    public $verbDefinitionEntity;
    public function setUp()
    {
        $this->verbDefinitionEntity = new ExtendedVerbDefinitionEntity('foo');
        parent::setUp();
    }

    public function testIsJsonSerializable()
    {
        $this->assertArrayHasKey('JsonSerializable', class_implements($this->verbDefinitionEntity));
    }

    public function testVerbNameFromConstructor()
    {
        $this->assertEquals('foo', $this->verbDefinitionEntity->getVerb());
    }

    public function testDefaultInputFilters()
    {
        $inputFilters = $this->verbDefinitionEntity->getInputFilters();

        $this->assertArrayHasKey('access_token', $inputFilters);

        $accessTokenFilter = $this->verbDefinitionEntity->getInputFilter('access_token');
        $this->assertEquals(false, $accessTokenFilter->isRequired());
        $this->assertEquals(false, $accessTokenFilter->isMultivalued());
        $this->assertEquals('\RubedoAPI\Entities\API\Identity', $accessTokenFilter->getFilter());

        $this->assertArrayHasKey('lang', $inputFilters);

        $langFilter = $this->verbDefinitionEntity->getInputFilter('lang');
        $this->assertEquals(false, $langFilter->isRequired());
        $this->assertEquals(false, $langFilter->isMultivalued());
        $this->assertEquals('\RubedoAPI\Entities\API\Language', $langFilter->getFilter());
    }

    public function testDefaultOutputFilters()
    {
        $outputFilters = $this->verbDefinitionEntity->getOutputFilters();

        $this->assertArrayHasKey('success', $outputFilters);

        $successFilter = $this->verbDefinitionEntity->getOutputFilter('success');
        $this->assertEquals(true, $successFilter->isRequired());
        $this->assertEquals(false, $successFilter->isMultivalued());
        $this->assertEquals('boolean', $successFilter->getFilter());

        $messageFilter = $this->verbDefinitionEntity->getOutputFilter('message');
        $this->assertEquals(false, $messageFilter->isRequired());
        $this->assertEquals(false, $messageFilter->isMultivalued());
        $this->assertEquals('string', $messageFilter->getFilter());
    }

    public function testDescription()
    {
        $this->verbDefinitionEntity->setDescription('bar');
        $this->assertEquals('bar', $this->verbDefinitionEntity->getDescription());
    }

    public function testIdentityRequiredHelper()
    {
        $this->verbDefinitionEntity->identityRequired(false);
        $this->assertEquals(false, $this->verbDefinitionEntity->hasIdentityRequired());
        $this->verbDefinitionEntity->identityRequired(true);
        $this->assertEquals(true, $this->verbDefinitionEntity->hasIdentityRequired());
    }

    public function testAddFilters()
    {
        $filter = new FilterDefinitionEntity();
        $filter
            ->setKey('foo')
            ;
        $this->verbDefinitionEntity->addInputFilter($filter);
        $this->verbDefinitionEntity->addOutputFilter($filter);
        $this->assertArrayHasKey('foo', $this->verbDefinitionEntity->getInputFilters());
        $this->assertArrayHasKey('foo', $this->verbDefinitionEntity->getOutputFilters());
    }

    public function testEditFilter() {
        $newFilter = new FilterDefinitionEntity();
        $newFilter->setKey('foo');
        $this->verbDefinitionEntity->addInputFilter($newFilter);
        $this->assertEquals(false, $this->verbDefinitionEntity->getInputFilter('foo')->isRequired());
        $this->verbDefinitionEntity->editInputFilter('foo', function(FilterDefinitionEntity &$filter) {
            $filter->setRequired();
        });
        $this->assertEquals(true, $this->verbDefinitionEntity->getInputFilter('foo')->isRequired());


        $newFilter = new FilterDefinitionEntity();
        $newFilter->setKey('foo');
        $this->verbDefinitionEntity->addOutputFilter($newFilter);
        $this->assertEquals(false, $this->verbDefinitionEntity->getOutputFilter('foo')->isRequired());
        $this->verbDefinitionEntity->editOutputFilter('foo', function(FilterDefinitionEntity &$filter) {
            $filter->setRequired();
        });
        $this->assertEquals(true, $this->verbDefinitionEntity->getOutputFilter('foo')->isRequired());
    }

    public function testFilterPHP()
    {
        $toFilter = array(
            'boolean' => 'true',
            'string' => '<b>value</b>',
            'success' => true,
        );
        $filterBoolean = new FilterDefinitionEntity();
        $filterBoolean
            ->setKey('boolean')
            ->setFilter('boolean')
        ;
        $filterString = new FilterDefinitionEntity();
        $filterString
            ->setKey('string')
            ->setFilter('string')
        ;
        $this->verbDefinitionEntity->addInputFilter($filterBoolean);
        $this->verbDefinitionEntity->addOutputFilter($filterBoolean);

        $this->verbDefinitionEntity->addInputFilter($filterString);
        $this->verbDefinitionEntity->addOutputFilter($filterString);

        $inputFiltered = $this->verbDefinitionEntity->filterInput($toFilter);
        $outputFiltered = $this->verbDefinitionEntity->filterOutput($toFilter);

        $this->assertArrayNotHasKey('success', $inputFiltered);
        $this->assertEquals(true, $inputFiltered['boolean']);
        $this->assertEquals(true, $outputFiltered['boolean']);
        $this->assertEquals('value', $inputFiltered['string']);
        $this->assertEquals('value', $outputFiltered['string']);
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIFilterException
     */
    public function testFilterRequired()
    {
        //assume success is required
        $this->verbDefinitionEntity->filterOutput(array());
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIRequestException
     */
    public function testFilterBadInput()
    {
        //assume success is required
        $this->verbDefinitionEntity->filterOutput(null);
    }

    public function jsonSerialize()
    {
        $verbDefinitionArray = $this->verbDefinitionEntity->jsonSerialize();
        $this->assertArrayHasKey('input', $verbDefinitionArray);
        $this->assertArrayHasKey('output', $verbDefinitionArray);
        $this->assertArrayHasKey('description', $verbDefinitionArray);
        $this->assertArrayHasKey('verb', $verbDefinitionArray);
    }

} 