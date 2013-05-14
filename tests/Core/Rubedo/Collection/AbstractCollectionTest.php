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

Use Rubedo\Collection\AbstractCollection;

require_once('Rubedo/Interfaces/Collection/IAbstractCollection.php');
require_once('Rubedo/Collection/AbstractCollection.php');

class testCollection extends AbstractCollection {
    public function __construct() {
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
class AbstractCollectionTest extends PHPUnit_Framework_TestCase {
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
	
	/**
	 * Test if getList call the read method only one time
	 */
	public function testNormalGetList(){
		$this->_mockDataAccessService->expects($this->once())->method('read');
		
		$collection = new testCollection();
		$collection->getList();
	}
	
	/**
	 * Test if getList method call addFilter when a filter is given in parameter
	 */
	public function testGetListWithFilter(){
		$this->_mockDataAccessService->expects($this->once())->method('read');
		$this->_mockDataAccessService->expects($this->once())->method('addFilter');
		
		$filter = array(array("property" => "test", "value" => "test"));
		
		$collection = new testCollection();
		$collection->getList($filter);
	}
	
	/**
	 * Test if getList method call addSort when a sort is given in parameter
	 */
	public function testGetListWithSort(){
		$this->_mockDataAccessService->expects($this->once())->method('read');
		$this->_mockDataAccessService->expects($this->once())->method('addSort');
		
		$sort = array(array("property" => "test", "direction" => "test"));
		
		$collection = new testCollection();
		$collection->getList(NULL, $sort);
	}
	
	/**
	 * Test if getList method call addFilter and addSort when a filter and a sort are given in parameters
	 */
	public function testGetListWithFilterAndSort(){
		$this->_mockDataAccessService->expects($this->once())->method('read');
		$this->_mockDataAccessService->expects($this->once())->method('addFilter');
		$this->_mockDataAccessService->expects($this->once())->method('addSort');
		
		$filter = array(array("property" => "test", "value" => "test"));
		$sort = array(array("property" => "test", "direction" => "test"));
		
		$collection = new testCollection();
		$collection->getList($filter, $sort);
	}
	
	/**
	 * Test if getList method call setFirstResult and setNumberOfResults when a start and a limit are given in parameters
	 */
	public function testGetListWithStartAndLimit(){
		$this->_mockDataAccessService->expects($this->once())->method('read');
		$this->_mockDataAccessService->expects($this->once())->method('setFirstResult');
		$this->_mockDataAccessService->expects($this->once())->method('setNumberOfResults');
		
		$collection = new testCollection();
		$collection->getList(null, null, 0, 10);
	}
	
	/**
	 * Test if getList method call addFilter when an operator is given in parameter
	 */
	public function testGetListWithOperator(){
		$this->_mockDataAccessService->expects($this->once())->method('read');
		$this->_mockDataAccessService->expects($this->once())->method('addFilter');
		
		$filter = array(array('operator' => 'test', "property" => "test", "value" => "test"));
		
		$collection = new testCollection();
		$collection->getList($filter);
	}
	
	/**
	 * Test if getList method call addFilter when an operator is given in parameter
	 */
	public function testGetListWithLikeOperator(){
		$this->_mockDataAccessService->expects($this->once())->method('read');
		$this->_mockDataAccessService->expects($this->once())->method('addFilter');
		
		$filter = array(array('operator' => 'like', "property" => "test", "value" => "test"));
		
		$collection = new testCollection();
		$collection->getList($filter);
	}
	
	/**
	 * Test if findById method call the findById method only one time
	 */
	public function testNormalFindById(){
		$this->_mockDataAccessService->expects($this->once())->method('findById');
		
		$id = 'test';
		
		$collection = new testCollection();
		$collection->findById($id);
	}
	
	/**
	 * Test if create method call the create method only one time
	 */
	public function testNormalCreate(){
		$this->_mockDataAccessService->expects($this->once())->method('create');
		
		$obj = array('key' => 'value');
		
		$collection = new testCollection();
		$collection->create($obj, true);
	}
		/**
	 * Test if customFind method call the customFind method only one time
	 */
	public function testNormalCustomFind(){
		$this->_mockDataAccessService->expects($this->once())->method('customFind');
		
		$filter = array(array("property" => "test", "value" => "test"));
		$fieldRule = array(array("property" => "test", "value" => "test"));
		
		$collection = new testCollection();
		$collection->customFind($filter,$fieldRule);
	}
	/**
	 * Test if findByName method call the findByName method only one time
	 */
	public function testNormalFindByName(){
		
		$this->_mockDataAccessService->expects($this->once())->method('findByName');
		
		$name="name";
		
		$collection = new testCollection();
		$collection->findByName($name);
	
	}
	
	/**
	 * Test if update method call the update method only one time
	 */
	public function testNormalUpdate(){
		$this->_mockDataAccessService->expects($this->once())->method('update');
		
		$obj = array('key' => 'value');
		
		$collection = new testCollection();
		$collection->update($obj, true);
	}
	
	/**
	 * Test if destroy method call the destroy method only one time
	 */
	public function testNormalDestroy(){
		$this->_mockDataAccessService->expects($this->once())->method('destroy');
		
		$obj = array('key' => 'value');
		
		$collection = new testCollection();
		$collection->destroy($obj, true);
	}
	
	/**
	 * Test if readChild method call the readChild method only one time
	 */
	public function testNormalReadChild(){
		$this->_mockDataAccessService->expects($this->once())->method('readChild');
		
		$parentId = '123456798';
		
		$collection = new testCollection();
		$collection->readChild($parentId);
	}
	
	/**
	 * Test if customDelete method call the customDelete method only one time
	 */
	public function testNormalcustomDelete(){
		$this->_mockDataAccessService->expects($this->once())->method('customDelete');
	
		 $deleteCond=array();
		$collection = new testCollection();
		$collection->customDelete($deleteCond);
	}
		/**
	 * Test if customUpdate method call the customUpdate method only one time
	 */
	public function testNormalcustomUpdate(){
		$this->_mockDataAccessService->expects($this->once())->method('customUpdate');
	
		 $data=array('value'=>"test");
		 $updateCond=array('condition'=>"test");
		$collection = new testCollection();
		$collection->customUpdate($data,$updateCond);
	}
	 /*
	  *  Test if readChild method call the readChild method only one time
	  */
	public function testReadChildWithOperator(){
		$this->_mockDataAccessService->expects($this->once())->method('readChild');
		
		$filter = array(array("property" => "test", "value" => "test", "operator" => "like"));
		
		$parentId = '123456798';
		
		$collection = new testCollection();
		$collection->readChild($parentId, $filter);

	}
	
	/**
	 * Test if readChild method call addFilter method when a filter is given in parameter
	 */
	public function testReadChildWithFilter(){
		$this->_mockDataAccessService->expects($this->once())->method('readChild');
		$this->_mockDataAccessService->expects($this->once())->method('addFilter');
		
		$parentId = '123456798';
		$filter = array(array("property" => "test", "value" => "test"));
		
		$collection = new testCollection();
		$collection->readChild($parentId, $filter);
	}
	
	/**
	 * Test if readChild method call addSort method when a sort is given in parameter
	 */
	public function testReadChildWithSort(){
		$this->_mockDataAccessService->expects($this->once())->method('readChild');
		$this->_mockDataAccessService->expects($this->once())->method('addSort');
		
		$parentId = '123456798';
		$sort = array(array("property" => "test", "direction" => "test"));
		
		$collection = new testCollection();
		$collection->readChild($parentId, NULL, $sort);
	}
	
	/**
	 * Test if readChild method call addFilter and addSort methods when a filter and a sort are given in parameters
	 */
	public function testReadChildWithFilterAndSort(){
		$this->_mockDataAccessService->expects($this->once())->method('readChild');
		$this->_mockDataAccessService->expects($this->once())->method('addFilter');
		$this->_mockDataAccessService->expects($this->once())->method('addSort');
		
		$parentId = '123456798';
		$filter = array(array("property" => "test", "value" => "test"));
		$sort = array(array("property" => "test", "direction" => "test"));
		
		$collection = new testCollection();
		$collection->readChild($parentId, $filter, $sort);
	}
	
	/**
	 * Test if getAncestors return array() when parentId is root 
	 */
	public function testgetAncestorsIfParentIdIsRoot(){

	$item['parentId']='root';
	$limit=5;
		$collection = new testCollection();
		$result=$collection->getAncestors($item, $limit);
		$this->assertTrue(is_array($result));
	}
	/**
	 * Test if getAncestors return array() when limit=0 
	 */
	public function testgetAncestorsIfLimitLessThanZero(){

	$item['parentId']='parent';
	$limit=0;
		$collection = new testCollection();
		$result=$collection->getAncestors($item, $limit);
		$this->assertTrue(is_array($result));
	}
	/**
	 * Test if getAncestors method call findById method
	 */
	public function testNormalGetAncestorsWithLimitFive(){
		$this->_mockDataAccessService->expects($this->once())->method('findById');
		$item['parentId']="parent";
		$limit=5;
		$collection = new testCollection();
		
		$result=$collection->getAncestors($item,$limit);
		$this->assertTrue(is_array($result));
	}
	
	
}
