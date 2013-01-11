<?php

class FileAccessTest extends PHPUnit_Framework_TestCase
{
	    /**
     * Cleaning
     */
    public function tearDown() {
        Rubedo\Services\Manager::resetMocks();
        parent::tearDown();
    }
   /**
     * init the Zend Application for tests
     */
    public function setUp() {
        testBootstrap();
		
        $mockUserService = $this->getMock('Rubedo\User\CurrentUser');
        Rubedo\Services\Manager::setMockService('CurrentUser', $mockUserService);

        $mockTimeService = $this->getMock('Rubedo\Time\CurrentTime');
        Rubedo\Services\Manager::setMockService('CurrentTime', $mockTimeService);
        parent::setUp();
    }
	public function testRead(){
	   
	}


}