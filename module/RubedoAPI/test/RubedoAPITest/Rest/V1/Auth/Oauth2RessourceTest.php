<?php

namespace RubedoAPITest\Rest\V1\Auth;

use Rubedo\Services\Manager;
use RubedoAPI\Rest\V1\Auth\Oauth2Ressource;

class Oauth2RessourceTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \RubedoAPI\Rest\V1\Auth\Oauth2Ressource
     */
    protected $ressource;

    function setUp()
    {
        $this->ressource = new Oauth2Ressource();
        parent::setUp();
    }

    function tearDown()
    {
        Manager::resetMocks();
        parent::tearDown();
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIRequestException
     */
    public function testDefinition()
    {
        $this->assertNotNull($this->ressource->getDefinition()->getVerb('get'));
    }
}