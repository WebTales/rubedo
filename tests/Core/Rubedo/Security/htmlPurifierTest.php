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
