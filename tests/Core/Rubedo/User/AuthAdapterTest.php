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
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		$this->bootstrap->bootstrap();
        parent::setUp();
    }

    /**
     * Cleaning
     */
    public function tearDown()
    {
		Rubedo\Services\Manager::resetMocks();
        parent::tearDown();
    }
	
	/**
	 * check the service configuration by getservice method
	 */
	public function testValidLogin(){
		
		$mockService = $this->getMock('Rubedo\Mongo\DataAccess');
        //$mockService->expects($this->once())->method('getCurrentUserSummary')->will($this->returnValue($this->_fakeUser));
        Rubedo\Services\Manager::setMockService('MongoDataAccess', $mockService);
		
		$login = "johnDoe";
		$password = "verySecret";
		
		$authAdapter = new Rubedo\User\AuthAdapter($login,$password);
		$result = $authAdapter->authenticate();
	}

   

}
