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
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		$this->bootstrap->bootstrap();
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
		$taxonomyTermsService=new Rubedo\Collection\TaxonomyTerms();
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
		$taxonomyTermsService=new Rubedo\Collection\TaxonomyTerms();
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
		$taxonomyTermsService=new Rubedo\Collection\TaxonomyTerms();
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
		$taxonomyTermsService=new Rubedo\Collection\TaxonomyTerms();
		$result=$taxonomyTermsService->getTerm($id);
		$isString=is_string($result);
		$this->assertTrue($isString);
		
	}
	
	public function testFindByVocabularyId()
	{
		$this->_mockDataAccessService->expects($this->once())->method('read');
		$this->_mockDataAccessService->expects($this->any())->method('addFilter');
		
		$id="vocabularyId";
		$taxonomyTermsService=new Rubedo\Collection\TaxonomyTerms();
		$result=$taxonomyTermsService->findByVocabulary($id);
	}
	
	public function testDeleteByVocabulary()
	{
		$this->_mockDataAccessService->expects($this->once())->method('customDelete');
		$id="vocabularyId";
		$taxonomyTermsService=new Rubedo\Collection\TaxonomyTerms();
		$result=$taxonomyTermsService->deleteByVocabularyId($id);
	}
	
}

	