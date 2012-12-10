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
 * Tests suite for the service Hash
 *
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class HashTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Test if the hashString function works
	 */
    public function testHashString() {

        $hashedString = '07e007fe5f99ee5851dd519bf6163a0d2dda54d45e6fe0127824f5b45a5ec59183a08aaa270979deb2f048815d05066c306e3694473d84d6aca0825c3dccd559';
        $string = 'test';
        $salt = 'salt';

        $hashService = \Rubedo\Services\Manager::getService('Hash');

        $hash = $hashService->hashString($string, $salt);

        $this->assertEquals($hashedString, $hash);

    }
	
	/**
	 * Test hashString function whith an integer for the string
	 * It should return an exception
	 * 
	 * @expectedException \Rubedo\Exceptions\Hash
	 */
	public function testHashStringWhitoutStringType(){
		
		$hashedString = '07e007fe5f99ee5851dd519bf6163a0d2dda54d45e6fe0127824f5b45a5ec59183a08aaa270979deb2f048815d05066c306e3694473d84d6aca0825c3dccd559';
        $string = 123456;
        $salt = 'salt';

        $hashService = \Rubedo\Services\Manager::getService('Hash');

        $hash = $hashService->hashString($string, $salt);

        $this->assertEquals($hashedString, $hash);
		
	}

	/**
	 * Test if the hashString function works
	 */
    public function testDerivatePassword() {

        $hashedPassword = 'bedbe5ac5a038a468b157b2a4c41dfd0de4ea0efb567fb8e05b71746eb9f3a8ef8e789c3b5129016a905b9f161c1cd331af69009574ed46dbb2b3b7706355167';
        $password = 'test';
        $salt = 'salt';

        $hashService = \Rubedo\Services\Manager::getService('Hash');

        $hash = $hashService->derivatePassword($password, $salt);

        $this->assertEquals($hashedPassword, $hash);

    }
	
	/**
	 * Test derivatePassword whith an array for the apssword
	 * The function should return an exception
	 * 
	 * @expectedException \Rubedo\Exceptions\Hash
	 */
    public function testDerivatePasswordWhitoutStringType() {

        $hashedPassword = 'bedbe5ac5a038a468b157b2a4c41dfd0de4ea0efb567fb8e05b71746eb9f3a8ef8e789c3b5129016a905b9f161c1cd331af69009574ed46dbb2b3b7706355167';
        $password = array('test' => 'salt');
        $salt = 'salt';

        $hashService = \Rubedo\Services\Manager::getService('Hash');

        $hash = $hashService->derivatePassword($password, $salt);

        $this->assertEquals($hashedPassword, $hash);

    }
	
	/**
	 * Test if the checkPassword function works
	 * It should return true
	 */
    public function testCheckPassword() {

        $hashedPassword = 'bedbe5ac5a038a468b157b2a4c41dfd0de4ea0efb567fb8e05b71746eb9f3a8ef8e789c3b5129016a905b9f161c1cd331af69009574ed46dbb2b3b7706355167';
        $password = 'test';
        $salt = 'salt';

        $hashService = \Rubedo\Services\Manager::getService('Hash');

        $result = $hashService->checkPassword($hashedPassword, $password, $salt);

        $this->assertEquals(true, $result);

    }
	
	/**
	 * Test checkPassword with a bas value for the password already hashed
	 * The function should return false
	 */
    public function testBadCheckPassword() {

        $hashedPassword = 'blablabla';
        $password = 'test';
        $salt = 'salt';

        $hashService = \Rubedo\Services\Manager::getService('Hash');

        $result = $hashService->checkPassword($hashedPassword, $password, $salt);

        $this->assertEquals(false, $result);

    }
	
	/**
	 * Test checkPassword with a boolean
	 * The function should return an exception
	 * 
	 * @expectedException \Rubedo\Exceptions\Hash
	 */
	public function testCheckPasswordWhitoutStringType() {

        $hashedPassword = 'bedbe5ac5a038a468b157b2a4c41dfd0de4ea0efb567fb8e05b71746eb9f3a8ef8e789c3b5129016a905b9f161c1cd331af69009574ed46dbb2b3b7706355167';
        $password = true;
        $salt = 'salt';

        $hashService = \Rubedo\Services\Manager::getService('Hash');

        $result = $hashService->checkPassword($hashedPassword, $password, $salt);

        $this->assertEquals(true, $result);

    }
	
	/**
	 * Test the generation of a random string
	 */
	public function testGenerateRandomString() {
		$hashService = \Rubedo\Services\Manager::getService('Hash');
		
		$result = $hashService->generateRandomString();
		
		$this->assertNotEmpty($result);
	}

}
