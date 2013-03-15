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

Use Rubedo\Collection\Taxonomy;

/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class TaxonomyTest extends PHPUnit_Framework_TestCase {
		/**
     * clear the DB of the previous test data
     */
    public function tearDown() {
        Rubedo\Services\Manager::resetMocks();
    }

    /**
     * init the Zend Application for tests
     */
    public function setUp() {
        testBootstrap();
        $this->_mockDataAccessService = $this->getMock('Rubedo\\Mongo\\DataAccess');
        Rubedo\Services\Manager::setMockService('MongoDataAccess', $this->_mockDataAccessService);
		 $this->_mockTaxonomyTermsService = $this->getMock('Rubedo\\Collection\\TaxonomyTerms');
        Rubedo\Services\Manager::setMockService('TaxonomyTerms', $this->_mockTaxonomyTermsService);
		

        parent::setUp();
    }
	
	public function testFindByName(){
		$this->_mockDataAccessService->expects($this->once())->method('findOne');
		$name="text";
		$taxonomyService=new Taxonomy();
		$taxonomyService->findByName($name);
	}
	
	public function testDestroyWhenDeleteByVocabularyOk(){
		$this->_mockTaxonomyTermsService->expects($this->once())->method('findByVocabulary')
										->will($this->returnValue(array('data' => array(array('test')))));
		$this->_mockDataAccessService->expects($this->once())->method('destroy');
		
		$obj["id"]="testId";
		$taxonomyService=new Taxonomy();
		$taxonomyService->destroy($obj);
	}
	
}

	