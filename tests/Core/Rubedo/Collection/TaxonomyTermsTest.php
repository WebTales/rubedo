<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

Use Rubedo\Collection\TaxonomyTerms;

/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class TaxonomyTermsTest extends PHPUnit_Framework_TestCase {
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
        parent::setUp();
    }
	/*
	 * test if Destroy function works fine
	 */
	public function testDestroy(){
		$customReturn["ok"]=1;
		$customReturn['n']=0;
	
		$this->_mockDataAccessService->expects($this->once())->method('customDelete')
						->will($this->returnValue($customReturn));
		$obj["id"]="id";
		$obj['vocabularyId'] = "test";
		$taxonomyTermsService=new TaxonomyTerms();
		$result=$taxonomyTermsService->destroy($obj);
		$isArray=is_array($result);
		$this->assertTrue($isArray);
	}
		/*
	 * test if Destroy function works fine  when customDelete function return "n">0 
	 */
	public function testDestroyWhenGreaterThanZero(){
		$customReturn["ok"]=1;
		$customReturn['n']=5;
	
		$this->_mockDataAccessService->expects($this->once())->method('customDelete')
						->will($this->returnValue($customReturn));
		
		$obj["id"]="id";
		$obj['vocabularyId'] = "test";
		$taxonomyTermsService=new TaxonomyTerms();
		$result=$taxonomyTermsService->destroy($obj);
		$isArray=is_array($result);
		$this->assertTrue($isArray);
	}
			/*
	 * test if Destroy function works fine  when customDelete function fail
	 */
	public function testDestroyWhencustomDeleteFail(){
		$customReturn["ok"]=0;
		$customReturn['n']=0;
		$customReturn["err"]="error test";
	
		$this->_mockDataAccessService->expects($this->once())->method('customDelete')
						->will($this->returnValue($customReturn));
		
		$obj["id"]="id";
		$obj['vocabularyId'] = "test";
		$taxonomyTermsService=new TaxonomyTerms();
		$result=$taxonomyTermsService->destroy($obj);
		$isArray=is_array($result);
		$this->assertTrue($isArray);
	}
	/*
	 * test if function getTerm works fine
	 */
	public function testGetTerm(){
		$findReturn["text"]="termTest";
		$this->_mockDataAccessService->expects($this->once())->method('findById')
								->will($this->returnValue($findReturn));
		
		$id="id";
		$taxonomyTermsService=new TaxonomyTerms();
		$result=$taxonomyTermsService->getTerm($id);
		$isString=is_string($result);
		$this->assertTrue($isString);
		
	}
	
	public function testFindByVocabularyId()
	{
		$this->_mockDataAccessService->expects($this->once())->method('read');
		$this->_mockDataAccessService->expects($this->any())->method('addFilter');
		
		$id="vocabularyId";
		$taxonomyTermsService=new TaxonomyTerms();
		$result=$taxonomyTermsService->findByVocabulary($id);
	}
	
	public function testDeleteByVocabulary()
	{
		$this->_mockDataAccessService->expects($this->once())->method('customDelete');
		$id="vocabularyId";
		$taxonomyTermsService=new TaxonomyTerms();
		$result=$taxonomyTermsService->deleteByVocabularyId($id);
	}
	
}

	