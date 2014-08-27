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