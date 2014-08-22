<?php

namespace RubedoAPITest\Collection;

use Rubedo\Services\Manager;
use RubedoAPI\Collection\UserTokens;

class ExtendedUserTokens extends UserTokens {}

class UserTokensTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var ExtendedUserTokens
     */
    public $userTokens;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataAccessService;

    /**
     * clear the DB of the previous test data
     */
    public function tearDown()
    {
        Manager::resetMocks();
    }

    public function setUp()
    {
        parent::setUp();
        $this->mockDataAccessService = $this->getMock('Rubedo\Mongo\DataAccess');
        Manager::setMockService('MongoDataAccess', $this->mockDataAccessService);
        $this->userTokens = new ExtendedUserTokens();
    }

    public function testExtends() {
        $this->assertInstanceOf('\Rubedo\Collection\AbstractCollection', $this->userTokens);
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIEntityException
     */
    public function testFindOneByRefreshTokenFailed()
    {
        $this->mockDataAccessService->expects($this->once())->method('findOne');
        $this->userTokens->findOneByRefreshToken("My false refresh token");
    }

    public function testFindOneByRefreshToken()
    {
        $tokenReturned = array(
            'access_token' => 'MyAccessToken',
            'refresh_token' => 'MyRefreshToken',
            'lifetime' => 3600,
            'createTime' => time(),
        );
        $this->mockDataAccessService->expects($this->once())->method('findOne')->will($this->returnValue($tokenReturned));
        $this->userTokens->findOneByRefreshToken("My refresh token");
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIEntityException
     */
    public function testFindOneByAccessTokenFailed()
    {
        $this->mockDataAccessService->expects($this->once())->method('findOne');
        $this->userTokens->findOneByAccessToken("My false access token");
    }

    public function testFindOneByAccessToken()
    {
        $tokenReturned = array(
            'access_token' => 'MyAccessToken',
            'refresh_token' => 'MyRefreshToken',
            'lifetime' => 3600,
            'createTime' => time(),
        );
        $this->mockDataAccessService->expects($this->once())->method('findOne')->will($this->returnValue($tokenReturned));
        $this->userTokens->findOneByAccessToken("My access token");
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIEntityException
     */
    public function testFindOneByAccessTokenExpired()
    {
        $tokenReturned = array(
            'access_token' => 'MyAccessToken',
            'refresh_token' => 'MyRefreshToken',
            'lifetime' => 3600,
            'createTime' => time() - 3601,
        );
        $this->mockDataAccessService->expects($this->once())->method('findOne')->will($this->returnValue($tokenReturned));
        $this->userTokens->findOneByAccessToken("My access token");
    }
}