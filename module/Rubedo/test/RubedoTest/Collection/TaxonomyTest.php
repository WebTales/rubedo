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
namespace RubedoTest\Collection;

use Rubedo\Collection\Taxonomy;
use Rubedo\Services\Manager;

/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class TaxonomyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Rubedo\Mongo\DataAccess
     */
    private $mockDataAccessService;
    /**
     * @var \Rubedo\Collection\TaxonomyTerms
     */
    private $mockTaxonomyTermsService;

    /**
     * clear the DB of the previous test data
     */
    public function tearDown()
    {
        Manager::resetMocks();
    }

    /**
     * init the Zend Application for tests
     */
    public function setUp()
    {
        $this->mockDataAccessService = $this->getMock('Rubedo\Mongo\DataAccess');
        Manager::setMockService('MongoDataAccess', $this->mockDataAccessService);
        $this->mockTaxonomyTermsService = $this->getMock('Rubedo\Collection\TaxonomyTerms');
        Manager::setMockService('TaxonomyTerms', $this->mockTaxonomyTermsService);


        parent::setUp();
    }

    public function testFindByName()
    {
        $this->mockDataAccessService->expects($this->once())->method('findOne');
        $name = "text";
        $taxonomyService = new Taxonomy();
        $taxonomyService->findByName($name);
    }

    public function testDestroyWhenDeleteByVocabularyOk()
    {
        $this->mockTaxonomyTermsService->expects($this->once())->method('findByVocabulary')
            ->will($this->returnValue(array('data' => array(array('test')))));
        $this->mockDataAccessService->expects($this->once())->method('destroy');

        $obj["id"] = "testId";
        $taxonomyService = new Taxonomy();
        $taxonomyService->destroy($obj);
    }

}

	