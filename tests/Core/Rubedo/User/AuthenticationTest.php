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
class AuthenticationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Init
     */
    public function setUp() {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $this->bootstrap->bootstrap();
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
