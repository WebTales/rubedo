<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo-Test
 * @package Rubedo-Test
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */

/**
 * Tests suite for the authentication Adapter for Mongo
 *
 *
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class AuthAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Init
     */
    public function setUp() {
        testBootstrap();
        parent::setUp();
    }

    /**
     * Cleaning
     */
    public function tearDown() {
        Rubedo\Services\Manager::resetMocks();
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
        $mockService->expects($this->once())->method('addOrFilter')->with($this->equalTo(array(array('login' => $login),array('email' => $login))));

        $mockService->expects($this->once())->method('read')->will($this->returnValue($dataUser));
        Rubedo\Services\Manager::setMockService('MongoDataAccess', $mockService);

        $mockService = $this->getMock('Rubedo\Security\Hash');
        $mockService->expects($this->once())->method('checkPassword')->with($this->equalTo('expected'), $this->equalTo($password), $this->equalTo('grainDeSel'))->will($this->returnValue(true));
        Rubedo\Services\Manager::setMockService('Hash', $mockService);

        $authAdapter = new Rubedo\User\AuthAdapter($login, $password);
        $result = $authAdapter->authenticate();

        unset($user['password']);
        $this->assertInstanceOf('\Zend_Auth_Result', $result);
        $this->assertEquals($user, $result->getIdentity());
        $this->assertEquals(\Zend_Auth_Result::SUCCESS, $result->getCode());
    }

    /**
     * check the service configuration by getservice method
     */
    public function testInvalidPassword() {
        $user = array('login' => 'johnDoe', 'salt' => 'grainDeSel', 'password' => 'expected');
		$dataUser = array('data'=>array($user));

        $mockService = $this->getMock('Rubedo\Mongo\DataAccess');

        $mockService->expects($this->once())->method('read')->will($this->returnValue($dataUser));
        Rubedo\Services\Manager::setMockService('MongoDataAccess', $mockService);

        $mockService = $this->getMock('Rubedo\Security\Hash');
        $mockService->expects($this->once())->method('checkPassword')->will($this->returnValue(false));
        Rubedo\Services\Manager::setMockService('Hash', $mockService);

        $login = "johnDoe";
        $password = "verySecret";

        $authAdapter = new Rubedo\User\AuthAdapter($login, $password);
        $result = $authAdapter->authenticate();

        $this->assertInstanceOf('\Zend_Auth_Result', $result);
        $this->assertEquals(null, $result->getIdentity());
        $this->assertEquals(\Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $result->getCode());
    }

    /**
     * check the service configuration by getservice method
     */
    public function testInvalidLogin() {
        $user = array('login' => 'johnDoe', 'salt' => 'grainDeSel', 'password' => 'expected');

        $mockService = $this->getMock('Rubedo\Mongo\DataAccess');
        $mockService->expects($this->once())->method('read')->will($this->returnValue(array('data'=>array())));
        Rubedo\Services\Manager::setMockService('MongoDataAccess', $mockService);

        $mockService = $this->getMock('Rubedo\Security\Hash');
        Rubedo\Services\Manager::setMockService('Hash', $mockService);

        $login = "johnDoe";
        $password = "verySecret";

        $authAdapter = new Rubedo\User\AuthAdapter($login, $password);
        $result = $authAdapter->authenticate();

        $this->assertInstanceOf('\Zend_Auth_Result', $result);
        $this->assertEquals(null, $result->getIdentity());
        $this->assertEquals(\Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, $result->getCode());
    }

}
