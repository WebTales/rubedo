<?php

namespace RubedoAPITest\Rest\V1\Auth\Oauth2;

use Rubedo\Services\Manager;
use RubedoAPI\Rest\V1\Auth\Oauth2\GenerateRessource;

class GenerateRessourceTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \RubedoAPI\Rest\V1\Auth\Oauth2\GenerateRessource
     */
    protected $ressource;

    function setUp()
    {
        $this->ressource = new GenerateRessource();
        parent::setUp();
    }

    function tearDown()
    {
        Manager::resetMocks();
        parent::tearDown();
    }

    public function testDefinition()
    {
        $this->assertNotNull($this->ressource->getDefinition()->getVerb('post'));
    }
}