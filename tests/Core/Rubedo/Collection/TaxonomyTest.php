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
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		$this->bootstrap->bootstrap();
        $this->_mockDataAccessService = $this->getMock('Rubedo\\Mongo\\DataAccess');
        Rubedo\Services\Manager::setMockService('MongoDataAccess', $this->_mockDataAccessService);
		 $this->_mockTaxonomyTermsService = $this->getMock('Rubedo\\Collection\\TaxonomyTerms');
        Rubedo\Services\Manager::setMockService('TaxonomyTerms', $this->_mockTaxonomyTermsService);
		

        parent::setUp();
    }
	
	public function testFindByName(){
		$this->_mockDataAccessService->expects($this->once())->method('findOne');
		$name="text";
		$taxonomyService=new Rubedo\Collection\Taxonomy();
		$taxonomyService->findByName($name);
	}
	
	public function testDestroyWhenDeleteByVocabularyOk(){
		$deleteReturn["ok"]=1;
		$this->_mockTaxonomyTermsService->expects($this->once())->method('deleteByVocabularyId')
										->will($this->returnValue($deleteReturn));
		$this->_mockDataAccessService->expects($this->once())->method('destroy');
		$obj["id"]="testId";
		$taxonomyService=new Rubedo\Collection\Taxonomy();
		$taxonomyService->destroy($obj);
	}
	public function testDestroyWhenDeleteByVocabularyFail(){
		$deleteReturn["ok"]=0;
		$this->_mockTaxonomyTermsService->expects($this->once())->method('deleteByVocabularyId')
										->will($this->returnValue($deleteReturn));
		$this->_mockDataAccessService->expects($this->never())->method('destroy');
		$obj["id"]="testId";
		$taxonomyService=new Rubedo\Collection\Taxonomy();
		$taxonomyService->destroy($obj);
	}
	
}

	