<?php

namespace RubedoAPITest\Services\Security;

use Rubedo\Services\Manager;
use RubedoAPI\Services\Security\Authentication;

class AuthenticationTest extends \PHPUnit_Framework_TestCase {
    protected $mockDataAccessService;
    /**
     * @var \RubedoAPI\Services\Security\Authentication
     */
    protected $auth;

    public function tearDown()
    {
        Manager::resetMocks();
    }

    public function setUp()
    {
        parent::setUp();
        $this->mockDataAccessService = $this->getMock('Rubedo\Mongo\DataAccess');
        Manager::setMockService('MongoDataAccess', $this->mockDataAccessService);
        $this->auth = new Authentication();
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIAuthException
     */
    public function testAPIAuthFailed()
    {
        $this->auth->APIAuth('foo', 'bar');
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIEntityException
     */
    public function testAPIRefreshFailed()
    {
        $this->auth->APIRefreshAuth('foo');
    }
}