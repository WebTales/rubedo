<?php

namespace RubedoAPITest\Services\User;

use RubedoAPI\Services\User\CurrentUser;

class CurrentUserTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var CurrentUser
     */
    protected $currentUser;

    function setUp()
    {
        $this->currentUser = new CurrentUser();
        parent::setUp();
    }

    function testLazyLoader()
    {
        $this->assertArrayHasKey('RubedoAPI\Traits\LazyServiceManager', class_uses($this->currentUser));
    }
} 