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

use Rubedo\Collection\AbstractCollection;
use Rubedo\Services\Manager;

class testCollection extends AbstractCollection
{
    public function __construct()
    {
        $this->_collectionName = 'test';
        parent::__construct();
    }

}

/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class AbstractCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Rubedo\Mongo\DataAccess
     */
    private $mockDataAccessService;

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
        parent::setUp();
    }

    /**
     * Test if getList call the read method only one time
     */
    public function testNormalGetList()
    {
        $this->mockDataAccessService->expects($this->once())->method('read');

        $collection = new testCollection();
        $collection->getList();
    }

    /**
     * Test if getList method call addFilter when a filter is given in parameter
     */
    public function testGetListWithFilter()
    {


        $filter = new \WebTales\MongoFilters\ValueFilter();
        $filter->setName('test')->setValue('test');
        $this->mockDataAccessService->expects($this->once())->method('read')->with($filter);
        $collection = new testCollection();
        $collection->getList($filter);
    }

    /**
     * Test if getList method call addSort when a sort is given in parameter
     */
    public function testGetListWithSort()
    {
        $this->mockDataAccessService->expects($this->once())->method('read');
        $this->mockDataAccessService->expects($this->once())->method('addSort');

        $sort = array(array("property" => "test", "direction" => "test"));

        $collection = new testCollection();
        $collection->getList(NULL, $sort);
    }

    /**
     * Test if getList method call addFilter and addSort when a filter and a sort are given in parameters
     */
    public function testGetListWithFilterAndSort()
    {


        $filter = new \WebTales\MongoFilters\ValueFilter();
        $filter->setName('test')->setValue('test');
        $sort = array(array("property" => "test", "direction" => "test"));

        $this->mockDataAccessService->expects($this->once())->method('read')->with($filter);
        $this->mockDataAccessService->expects($this->once())->method('addSort');

        $collection = new testCollection();
        $collection->getList($filter, $sort);
    }

    /**
     * Test if getList method call setFirstResult and setNumberOfResults when a start and a limit are given in parameters
     */
    public function testGetListWithStartAndLimit()
    {
        $this->mockDataAccessService->expects($this->once())->method('read');
        $this->mockDataAccessService->expects($this->once())->method('setFirstResult');
        $this->mockDataAccessService->expects($this->once())->method('setNumberOfResults');

        $collection = new testCollection();
        $collection->getList(null, null, 0, 10);
    }


    /**
     * Test if findById method call the findById method only one time
     */
    public function testNormalFindById()
    {
        $this->mockDataAccessService->expects($this->once())->method('findById');

        $id = 'test';

        $collection = new testCollection();
        $collection->findById($id);
    }

    /**
     * Test if create method call the create method only one time
     */
    public function testNormalCreate()
    {
        $this->mockDataAccessService->expects($this->once())->method('create');

        $obj = array('key' => 'value');

        $collection = new testCollection();
        $collection->create($obj);
    }

    /**
     * Test if customFind method call the customFind method only one time
     */
    public function testNormalCustomFind()
    {
        $this->mockDataAccessService->expects($this->once())->method('customFind');

        $filter = new \WebTales\MongoFilters\ValueFilter();
        $filter->setName('test')->setValue('test');
        $fieldRule = array(array("property" => "test", "value" => "test"));

        $collection = new testCollection();
        $collection->customFind($filter, $fieldRule);
    }

    /**
     * Test if findByName method call the findByName method only one time
     */
    public function testNormalFindByName()
    {

        $this->mockDataAccessService->expects($this->once())->method('findByName');

        $name = "name";

        $collection = new testCollection();
        $collection->findByName($name);

    }

    /**
     * Test if update method call the update method only one time
     */
    public function testNormalUpdate()
    {
        $this->mockDataAccessService->expects($this->once())->method('update');

        $obj = array('key' => 'value');

        $collection = new testCollection();
        $collection->update($obj);
    }

    /**
     * Test if destroy method call the destroy method only one time
     */
    public function testNormalDestroy()
    {
        $this->mockDataAccessService->expects($this->once())->method('destroy');

        $obj = array('key' => 'value');

        $collection = new testCollection();
        $collection->destroy($obj, true);
    }

    /**
     * Test if readChild method call the readChild method only one time
     */
    public function testNormalReadChild()
    {
        $this->mockDataAccessService->expects($this->once())->method('readChild');

        $parentId = '123456798';

        $collection = new testCollection();
        $collection->readChild($parentId);
    }

    /**
     * Test if customDelete method call the customDelete method only one time
     */
    public function testNormalcustomDelete()
    {
        $this->mockDataAccessService->expects($this->once())->method('customDelete');

        $filter = new \WebTales\MongoFilters\ValueFilter();
        $filter->setName('test')->setValue('test');
        $collection = new testCollection();
        $collection->customDelete($filter);
    }

    /**
     * Test if customUpdate method call the customUpdate method only one time
     */
    public function testNormalcustomUpdate()
    {
        $this->mockDataAccessService->expects($this->once())
            ->method('customUpdate');

        $data = array(
            'value' => "test"
        );
        $filter = new \WebTales\MongoFilters\ValueFilter();
        $filter->setName('test')->setValue('test');
        $collection = new testCollection();
        $collection->customUpdate($data, $filter);
    }


    /**
     * Test if readChild method call addFilter method when a filter is given in parameter
     */
    public function testReadChildWithFilter()
    {
        $parentId = '123456798';
        $filter = new \WebTales\MongoFilters\ValueFilter();
        $filter->setName('test')->setValue('test');

        $this->mockDataAccessService->expects($this->once())->method('readChild')->with($parentId, $filter);

        $collection = new testCollection();
        $collection->readChild($parentId, $filter);
    }

    /**
     * Test if readChild method call addSort method when a sort is given in parameter
     */
    public function testReadChildWithSort()
    {
        $this->mockDataAccessService->expects($this->once())->method('readChild');
        $this->mockDataAccessService->expects($this->once())->method('addSort');

        $parentId = '123456798';
        $sort = array(array("property" => "test", "direction" => "test"));

        $collection = new testCollection();
        $collection->readChild($parentId, NULL, $sort);
    }

    /**
     * Test if readChild method call addFilter and addSort methods when a filter and a sort are given in parameters
     */
    public function testReadChildWithFilterAndSort()
    {
        $parentId = '123456798';
        $filter = new \WebTales\MongoFilters\OperatorToValueFilter();
        $filter->setName('test')->setValue('test')->setOperator('test');
        $sort = array(array("property" => "test", "direction" => "test"));

        $this->mockDataAccessService->expects($this->once())->method('readChild')->with($parentId, $filter);
        $this->mockDataAccessService->expects($this->once())->method('addSort');

        $collection = new testCollection();
        $collection->readChild($parentId, $filter, $sort);
    }

    /**
     * Test if getAncestors return array() when parentId is root
     */
    public function testgetAncestorsIfParentIdIsRoot()
    {
        $item['parentId'] = 'root';
        $limit = 5;
        $collection = new testCollection();
        $result = $collection->getAncestors($item, $limit);
        $this->assertTrue(is_array($result));
    }

    /**
     * Test if getAncestors return array() when limit=0
     */
    public function testgetAncestorsIfLimitLessThanZero()
    {

        $item['parentId'] = 'parent';
        $limit = 0;
        $collection = new testCollection();
        $result = $collection->getAncestors($item, $limit);
        $this->assertTrue(is_array($result));
    }

    /**
     * Test if getAncestors method call findById method
     */
    public function testNormalGetAncestorsWithLimitFive()
    {
        $this->mockDataAccessService->expects($this->once())->method('findById');
        $item['parentId'] = "parent";
        $limit = 5;
        $collection = new testCollection();

        $result = $collection->getAncestors($item, $limit);
        $this->assertTrue(is_array($result));
    }
}
