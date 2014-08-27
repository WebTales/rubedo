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

namespace RubedoAPITest\Services\Security;

use RubedoAPI\Services\Security\Token;

class ExtendedSecurityToken extends Token
{
    public function newToken($userId)
    {
        return parent::newToken($userId);
    }
}

class TokenTest extends \PHPUnit_Framework_TestCase
{
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