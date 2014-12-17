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
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace RubedoTest\User;
use Rubedo\Services\Manager;
use Rubedo\User\Authentication\Adapter\CoreAdapter;
use Zend\Authentication\Result;


/**
 * Tests suite for the authentication Adapter for Mongo
 *
 *
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class AuthAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Init
     */
    public function setUp() {
        parent::setUp();
    }

    /**
     * Cleaning
     */
    public function tearDown() {
        Manager::resetMocks();
        parent::tearDown();
    }

    /**
     * check the service configuration by getservice method
     */
    public function testValidLogin() {
        $login = "johnDoe";
        $password = "verySecret";

        $user = array('login' => 'johnDoe', 'salt' => 'grainDeSel', 'password' => 'expected','id'=>'69');
		$dataUser = array('data'=>array($user));

        $mockService = $this->getMock('Rubedo\Mongo\DataAccess');
		$mockService->expects($this->once())->method('init')->with($this->equalTo('Users'));

        $mockService->expects($this->once())->method('read')->will($this->returnValue($dataUser));
        Manager::setMockService('MongoDataAccess', $mockService);

        $mockService = $this->getMock('Rubedo\Security\Hash');
        $mockService->expects($this->once())->method('checkPassword')->with($this->equalTo('expected'), $this->equalTo($password), $this->equalTo('grainDeSel'))->will($this->returnValue(true));
        Manager::setMockService('Hash', $mockService);

        $authAdapter = new CoreAdapter($login, $password);
        $result = $authAdapter->authenticate();

        unset($user['password']);
        $this->assertInstanceOf('\Zend\Authentication\Result', $result);
        $this->assertEquals($user, $result->getIdentity());
        $this->assertEquals(Result::SUCCESS, $result->getCode());
    }

    /**
     * check the service configuration by getservice method
     */
    public function testInvalidPassword() {
        $user = array('login' => 'johnDoe', 'salt' => 'grainDeSel', 'password' => 'expected');
		$dataUser = array('data'=>array($user));

        $mockService = $this->getMock('Rubedo\Mongo\DataAccess');

        $mockService->expects($this->once())->method('read')->will($this->returnValue($dataUser));
        Manager::setMockService('MongoDataAccess', $mockService);

        $mockService = $this->getMock('Rubedo\Security\Hash');
        $mockService->expects($this->once())->method('checkPassword')->will($this->returnValue(false));
        Manager::setMockService('Hash', $mockService);

        $login = "johnDoe";
        $password = "verySecret";

        $authAdapter = new CoreAdapter($login, $password);
        $result = $authAdapter->authenticate();

        $this->assertInstanceOf('\Zend\Authentication\Result', $result);
        $this->assertEquals(null, $result->getIdentity());
        $this->assertEquals(Result::FAILURE_CREDENTIAL_INVALID, $result->getCode());
    }

    /**
     * check the service configuration by getservice method
     */
    public function testInvalidLogin() {
        $user = array('login' => 'johnDoe', 'salt' => 'grainDeSel', 'password' => 'expected');

        $mockService = $this->getMock('Rubedo\Mongo\DataAccess');
        $mockService->expects($this->once())->method('read')->will($this->returnValue(array('data'=>array())));
        Manager::setMockService('MongoDataAccess', $mockService);

        $mockService = $this->getMock('Rubedo\Security\Hash');
        Manager::setMockService('Hash', $mockService);

        $login = "johnDoe";
        $password = "verySecret";

        $authAdapter = new CoreAdapter($login, $password);
        $result = $authAdapter->authenticate();
        $this->assertInstanceOf('\Zend\Authentication\Result', $result);
        $this->assertEquals(null, $result->getIdentity());
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getCode());
    }

}
