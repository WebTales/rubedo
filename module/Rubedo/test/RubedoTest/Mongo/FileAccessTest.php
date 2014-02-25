<?php
namespace RubedoTest\Mongo;
use Rubedo\Services\Manager;

class FileAccessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Cleaning
     */
    public function tearDown()
    {
        Manager::resetMocks();
        parent::tearDown();
    }

    /**
     * init the Zend Application for tests
     */
    public function setUp()
    {
        $mockUserService = $this->getMock('Rubedo\User\CurrentUser');
        Manager::setMockService('CurrentUser', $mockUserService);

        $mockTimeService = $this->getMock('Rubedo\Time\CurrentTime');
        Manager::setMockService('CurrentTime', $mockTimeService);
        parent::setUp();
    }

    public function testRead()
    {

    }


}