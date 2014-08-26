<?php

namespace RubedoAPITest\Rest\V1;

use Rubedo\Services\Manager;
use RubedoAPI\Rest\V1\PagesRessource;

class PagesRessourceTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \RubedoAPI\Rest\V1\PagesRessource
     */
    protected $ressource;

    function setUp()
    {
        $this->ressource = new PagesRessource();
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