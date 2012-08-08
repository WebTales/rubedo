<?php
class DataAccessTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }
	
    public function testClassExist()
    {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
    }
}