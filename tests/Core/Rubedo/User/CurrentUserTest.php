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
class CurrentUserTest extends PHPUnit_Framework_TestCase {
    
	 
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
     * Test isAuthenticated method with correct login
	 * 
	 * Should return true
     */
    public function testIsAuthenticatedWithCorrectLogin() {
    	$auth = new \Rubedo\User\Authentication();
		$currentUser = new \Rubedo\User\CurrentUser();
		
		$auth->authenticate('admin', 'admin');
		$result = $currentUser->isAuthenticated();
		
		$this->assertTrue($result);
    }
	
	/**
     * Test isAuthenticated method with correct login
	 * 
	 * Should return false
     */
    public function testIsAuthenticatedWithBadLogin() {
    	$auth = new \Rubedo\User\Authentication();
		$currentUser = new \Rubedo\User\CurrentUser();
		
		$auth->authenticate('admin', 'test');
		$result = $currentUser->isAuthenticated();
		
		$this->assertFalse($result);
    }
	
	/**
     * Test if getCurrentUser return good values
     */
    public function testGetCurrentUser() {
    	$auth = new \Rubedo\User\Authentication();
		$currentUser = new \Rubedo\User\CurrentUser();
		
		$auth->authenticate('admin', 'admin');
		$result = $currentUser->getCurrentUser();
		
		$this->assertEquals($result['login'], 'admin');
		$this->assertEquals($result['name'], 'Admin');
		$this->assertEquals($result['salt'], 'bc8LdoqHGE');
    }
	
	/**
     * Test if getCurrentUserSummary return good values
     */
    public function testGetCurrentUserSummary() {
    	$auth = new \Rubedo\User\Authentication();
		$currentUser = new \Rubedo\User\CurrentUser();
		
		$auth->authenticate('admin', 'admin');
		$result = $currentUser->getCurrentUserSummary();
		
		$this->assertEquals($result['login'], 'admin');
		$this->assertEquals($result['fullName'], 'Admin');
    }
	
	/**
     * Test if getGroups return good values
     */
    public function testGetGroups() {
    	$mockGroupsService = $this->getMock('Rubedo\\Collection\\Groups');
        Rubedo\Services\Manager::setMockService('Groups', $mockGroupsService);
		
		$currentUser = new \Rubedo\User\CurrentUser();
		
		$result = $currentUser->getGroups();
    }

}
