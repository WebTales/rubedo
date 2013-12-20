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

use Rubedo\Collection\PersonalPrefs;
use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;

/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class PersonalPrefsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Rubedo\Mongo\DataAccess
     */
    private $mockDataAccessService;

    /**
     * @var \Rubedo\User\CurrentUser
     */
    private $mockCurrentUser;

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
        $this->mockCurrentUser = $this->getMock('Rubedo\User\CurrentUser');
        Manager::setMockService('MongoDataAccess', $this->mockDataAccessService);
        Manager::setMockService('CurrentUser', $this->mockCurrentUser);

        parent::setUp();
    }

    /**
     * Test if the create method use the id given by the current user service
     */
    public function testNormalCreate()
    {
        $this->mockCurrentUser->expects($this->once())->method('getCurrentUserSummary')->will($this->returnValue(array('id' => '123456789')));
        $this->mockDataAccessService->expects($this->once())->method('create');

        $obj = array('key' => 'value');

        $collection = new PersonalPrefs();
        $collection->create($obj, true);
    }

    /**
     * Test if the filter is set with the id given by the current user service in getList method
     */
    public function testNormalGetList()
    {
        $this->mockCurrentUser->expects($this->once())->method('getCurrentUserSummary')->will($this->returnValue(array('id' => '123456789')));
        $filters = Filter::factory('Value')->setName('userId')->setValue('123456789');
        $this->mockDataAccessService->expects($this->once())->method('addFilter')->with($this->equalTo($filters));
        $this->mockDataAccessService->expects($this->once())->method('read');

        $collection = new PersonalPrefs();
        $collection->getList();
    }

    /**
     * Test if the filter is set with the id given by the current user service in update method
     */
    public function testNormalUpdate()
    {
        $this->mockCurrentUser->expects($this->once())->method('getCurrentUserSummary')->will($this->returnValue(array('id' => '123456789')));
        $filters = Filter::factory('Value')->setName('userId')->setValue('123456789');
        $this->mockDataAccessService->expects($this->once())->method('addFilter')->with($this->equalTo($filters));
        $this->mockDataAccessService->expects($this->once())->method('update');

        $obj = array('key' => 'value');

        $collection = new PersonalPrefs();
        $collection->update($obj);
    }

    /**
     * Test if the filter is set with the id given by the current user service in destroy method
     */
    public function testNormalDestroy()
    {
        $this->mockCurrentUser->expects($this->once())->method('getCurrentUserSummary')->will($this->returnValue(array('id' => '123456789')));
        $filters = Filter::factory('Value')->setName('userId')->setValue('123456789');
        $this->mockDataAccessService->expects($this->once())->method('addFilter')->with($this->equalTo($filters));
        $this->mockDataAccessService->expects($this->once())->method('destroy');

        $obj = array('key' => 'value');

        $collection = new PersonalPrefs();
        $collection->destroy($obj);
    }
}
