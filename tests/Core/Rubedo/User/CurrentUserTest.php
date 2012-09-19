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
class CurrentUserTest extends PHPUnit_Framework_TestCase
{
    /**
     * Init
     */
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }

    /**
     * Cleaning
     */
    public function tearDown()
    {
        parent::tearDown();
    }
	
	/**
	 * check the service configuration by getservice method
	 */
	public function testConfiguredService(){
		$currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
    	//$currentUser = $currentUserService->getCurrentUserSummary();
	}

   

}
