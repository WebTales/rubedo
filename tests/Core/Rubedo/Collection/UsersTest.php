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

Use Rubedo\Collection\Users;
 
/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class UsersTest extends PHPUnit_Framework_TestCase {
	/**
     * clear the DB of the previous test data
     */
    public function tearDown() {
        Rubedo\Services\Manager::resetMocks();
    }

    /**
     * init the Zend Application for tests
     */
    public function setUp() {
        testBootstrap();
        $this->_mockDataAccessService = $this->getMock('Rubedo\\Mongo\\DataAccess');
		$this->_mockHash = $this->getMock('Rubedo\\Security\\Hash');
        Rubedo\Services\Manager::setMockService('MongoDataAccess', $this->_mockDataAccessService);
		Rubedo\Services\Manager::setMockService('Hash', $this->_mockHash);

        parent::setUp();
    }
	
	/**
	 * Test if changePassword correctly use the hash service
	 */
	public function testChangePassword(){
		$this->_mockHash->expects($this->once())->method('generateRandomString')->will($this->returnValue('1234567890'));
		$this->_mockHash->expects($this->once())->method('derivatePassword')->with($this->equalTo('test', '1234567890'))->will($this->returnValue('0987654321'));
		$this->_mockDataAccessService->expects($this->once())->method('update')->with($this->equalTo(array('id' => '123456789', 'version' => 1, 'password' => '0987654321', 'salt' => '1234567890')));
			
		$collection = new Users();
		$collection->changePassword("test", 1, "123456789");
	}
}
