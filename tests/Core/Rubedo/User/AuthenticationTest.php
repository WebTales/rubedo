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

/**
 * Tests suite for the authentication Adapter for Mongo
 *
 *
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class AuthenticationTest extends PHPUnit_Framework_TestCase
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
	 * Test to clear the identity of the current user
	 * 
	 * Should return false
	 */
	public function testClearIdentity(){
		$auth = new \Rubedo\User\Authentication();
		$currentUser = new \Rubedo\User\CurrentUser();
		
		$auth->authenticate('admin', 'admin');
		$auth->clearIdentity();
		
		$result = $currentUser->isAuthenticated();
		
		$this->assertFalse($result);
	}

}
