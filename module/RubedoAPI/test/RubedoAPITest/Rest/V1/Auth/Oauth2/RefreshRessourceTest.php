<?php

namespace RubedoAPITest\Rest\V1\Auth\Oauth2;

use Rubedo\Services\Manager;
use RubedoAPI\Rest\V1\Auth\Oauth2\RefreshRessource;

class RefreshRessourceTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \RubedoAPI\Rest\V1\Auth\Oauth2\RefreshRessource
     */
    protected $ressource;

    function setUp()
    {
        $this->ressource = new RefreshRessource();
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