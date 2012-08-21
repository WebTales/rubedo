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
 * Abstract Controller Test Case
 *
 *
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
abstract class AbstractControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
	/**
	 * Pre test context
	 */
    public function setUp()
    {
        $this->bootstrap = new Zend_Application('testing', APPLICATION_PATH . '/configs/application.ini');
		$mockServiceAccess = $this->getMock('Rubedo\Acl\Acl');
        $mockServiceAccess->expects($this->atLeastOnce())->method('HasAccess')->will($this->returnValue(true));

        Rubedo\Services\Manager::setMockService('Acl', $mockServiceAccess);
        parent::setUp();
    }

	/**
	 * Post test cleaning for isolation
	 */
    public function tearDown()
    {
        $this->resetRequest();
        $this->resetResponse();
        Rubedo\Services\Manager::resetMocks();
        parent::tearDown();
    }

}
