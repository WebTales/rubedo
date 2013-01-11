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
 * Tests suite for the service current user
 *
 *
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class SessionTest extends PHPUnit_Framework_TestCase {
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
        parent::tearDown();
    }

    /**
     * Test to add a value in session
     */
    public function testSet() {
    	$session = new \Rubedo\User\Session();
		
		$session->set('user', 'test');
    }
	
	/**
     * Test to add a value in session and get it after
     */
    public function testGet() {
    	$session = new \Rubedo\User\Session();
		
		$session->set('user', 'test');
		$result = $session->get('user');
		
		$this->assertEquals('test', $result);
    }
	
	/**
     * Test to get a value without having defined it before
	 * 
	 * Should return default value
     */
    public function testGetWhitoutSet() {
    	$session = new \Rubedo\User\Session();
		
		$result = $session->get('user', 'defaultValue');
		
		$this->assertEquals('defaultValue', $result);
    }

}
