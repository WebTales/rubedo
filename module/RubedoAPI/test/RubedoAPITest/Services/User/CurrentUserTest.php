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