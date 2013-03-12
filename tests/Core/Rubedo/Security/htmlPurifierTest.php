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
 * Tests suite for the session HTML cleaner Service
 *
 *
 * @author nduvollet
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class HtmlPurifierTest extends PHPUnit_Framework_TestCase
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
     * check the service configuration by getservice method
	 * 
	 * @dataProvider providerGoodCleaning
     */
    public function testCleanCases($hazardousHtml,$inoffensiveHtml) {
    	$cleaner = new \Rubedo\Security\HtmlPurifier();
		
		$outputHtml = $cleaner->clean($hazardousHtml);
		
		$this->assertEquals($inoffensiveHtml,$outputHtml);
	
	}

	
	public function providerGoodCleaning()
    {
        return array(
          array('<p>1 paragraph</p><script>some js</script><p>another paragraph</p>', '<p>1 paragraph</p><p>another paragraph</p>'),
          array('<p>1 paragraph</p><img src="someurl" />', '<p>1 paragraph</p><img src="someurl" alt="someurl" />'),
        );
    }

}
