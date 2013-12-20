<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
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
namespace RubedoTest\Collection;

use Rubedo\Collection\Users;
use Rubedo\Services\Manager;

/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class UsersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Rubedo\Mongo\DataAccess
     */
    private $mockDataAccessService;
    /**
     * @var \Rubedo\Security\Hash
     */
    private $mockHash;

    /**
     * clear the DB of the previous test data
     */
    public function tearDown()
    {
        Manager::resetMocks();
    }

    /**
     * init the Zend Application for tests
     */
    public function setUp()
    {
        $this->mockDataAccessService = $this->getMock('Rubedo\Mongo\DataAccess');
        $this->mockHash = $this->getMock('Rubedo\Security\Hash');
        Manager::setMockService('MongoDataAccess', $this->mockDataAccessService);
        Manager::setMockService('Hash', $this->mockHash);

        parent::setUp();
    }

    /**
     * Test if changePassword correctly use the hash service
     */
    public function testChangePassword()
    {
        $this->mockHash->expects($this->once())->method('generateRandomString')->will($this->returnValue('1234567890'));
        $this->mockHash->expects($this->once())->method('derivatePassword')->with($this->equalTo('test', '1234567890'))->will($this->returnValue('0987654321'));
        $this->mockDataAccessService->expects($this->once())
            ->method('update')
            ->with($this->equalTo(array(
                'id' => '123456789',
                'version' => 1,
                'password' => '0987654321',
                'salt' => '1234567890'
            )))
            ->will($this->returnValue(array(
                'success' => true
            )));

        $collection = new Users();
        $collection->changePassword("test", 1, "123456789");
    }
}
