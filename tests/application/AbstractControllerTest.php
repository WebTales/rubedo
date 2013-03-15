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
        testBootstrap();
		//$mockServiceAccess = $this->getMock('Rubedo\Acl\Acl');
        //$mockServiceAccess->expects($this->atLeastOnce())->method('HasAccess')->will($this->returnValue(true));

        //Rubedo\Services\Manager::setMockService('Acl', $mockServiceAccess);
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
