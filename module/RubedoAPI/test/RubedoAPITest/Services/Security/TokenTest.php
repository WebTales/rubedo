<?php

namespace RubedoAPITest\Services\Security;

use RubedoAPI\Services\Security\Token;

class ExtendedSecurityToken extends Token {
    public function newToken($userId) {
        return parent::newToken($userId);
    }
}

class TokenTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var ExtendedSecurityToken
     */
    protected $tokenService;

    function setUp()
    {
        $this->tokenService = new ExtendedSecurityToken();
        parent::setUp();
    }
    public function testNewToken()
    {
        $this->assertNotEquals($this->tokenService->newToken('foo'), $this->tokenService->newToken('foo'));
    }
}