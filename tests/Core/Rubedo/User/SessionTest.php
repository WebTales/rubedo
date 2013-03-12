<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
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
