<?php

namespace RubedoAPITest\Entities\API\Definition;


use RubedoAPI\Entities\API\Definition\DefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;

class ExtendedDefinitionEntity extends DefinitionEntity
{
    public $verbList = [];
}

class DefinitionEntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \RubedoAPI\Entities\API\Definition\DefinitionEntity
     */
    public $definitionEntity;

    public function setUp()
    {
        $this->definitionEntity = new ExtendedDefinitionEntity();
        parent::setUp();
    }

    public function testName()
    {
        $this->definitionEntity->setName('Foo');
        $this->assertEquals('Foo', $this->definitionEntity->getName());
    }

    public function testDefinition()
    {
        $this->definitionEntity->setDescription('Bar');
        $this->assertEquals('Bar', $this->definitionEntity->getDescription());
    }

    /**
     * @expectedException        \RubedoAPI\Exceptions\APIRequestException
     */
    public function testGetVerbNotExist()
    {
        $this->definitionEntity->getVerb('foo');
    }

    public function testIsJsonSerializable()
    {
        $this->assertArrayHasKey('JsonSerializable', class_implements($this->definitionEntity));
    }

    public function testJsonSerialize()
    {
        $this->definitionEntity
            ->setDescription('Desc')
            ->setName('Name')
            ->editVerb('verb', function (VerbDefinitionEntity &$entity) {
            });
        $serialized = $this->definitionEntity->jsonSerialize();
        $this->assertArrayHasKey('name', $serialized);
        $this->assertArrayHasKey('description', $serialized);
        $this->assertArrayHasKey('verbs', $serialized);
    }

    public function testGetVerb()
    {
        $this->definitionEntity->editVerb('bar', function (VerbDefinitionEntity &$entity) {
        });
        $this->assertEquals('RubedoAPI\Entities\API\Definition\VerbDefinitionEntity', get_class($this->definitionEntity->getVerb('bar')));
    }
} 