<?php

namespace RubedoAPITest\Rest\V1;

use Rubedo\Services\Manager;
use RubedoAPI\Rest\V1\MenuRessource;

class MenuRessourceTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \RubedoAPI\Rest\V1\MenuRessource
     */
    protected $ressource;

    function setUp()
    {
        $this->ressource = new MenuRessource();
        parent::setUp();
    }

    function tearDown()
    {
        Manager::resetMocks();
        parent::tearDown();
    }

    public function testDefinition()
    {
        $this->assertNotNull($this->ressource->getDefinition()->getVerb('get'));
    }
} 