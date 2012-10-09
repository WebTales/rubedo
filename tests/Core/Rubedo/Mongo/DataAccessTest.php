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
 * Test suite of the service handling read and write to mongoDB :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class DataAccessTest extends PHPUnit_Framework_TestCase
{
    /**
     * Phactory : database fixture handler
     * @var \Phactory\Mongo\Phactory
     */
    protected static $phactory;

    /**
     * Fixture : MongoDB dataset for tests
     * Create an "item" blueprint for testing purpose
     */
    public static function setUpBeforeClass()
    {
        // create a db connection and tell Phactory to use it
        $mongo = new Mongo(\Rubedo\Mongo\DataAccess::getDefaultMongo());
        $mongoDb = $mongo->test_db;

        static::$phactory = new \Phactory\Mongo\Phactory($mongoDb);

        // reset any existing blueprints and empty any tables Phactory has used
        static::$phactory->reset();

        // define default values for each user we will create
        static::$phactory->define('item', array('name' => 'Test item $n'));
    }

    /**
     * clear the DB of the previous test data
     */
    public function tearDown()
    {
        static::$phactory->recall();
        Rubedo\Services\Manager::resetMocks();
    }

    /**
     * init the Zend Application for tests
     */
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $mockUserService = $this->getMock('Rubedo\User\CurrentUser');
        Rubedo\Services\Manager::setMockService('CurrentUser', $mockUserService);
		
		$mockTimeService = $this->getMock('Rubedo\Time\CurrentTime');
        Rubedo\Services\Manager::setMockService('CurrentTime', $mockTimeService);
		
		
        parent::setUp();
    }

    /**
     * Initialize a mock CurrentUser service
     */
    public function initUser()
    {
        $this->_fakeUser = array('id' => 1, 'login' => (string) rand(21, 128));
        $mockService = $this->getMock('Rubedo\User\CurrentUser');
        $mockService->expects($this->once())->method('getCurrentUserSummary')->will($this->returnValue($this->_fakeUser));
        Rubedo\Services\Manager::setMockService('CurrentUser', $mockService);
    }
	
	/**
     * Initialize a mock CurrentTime service
     */
    public function initTime()
    {
        $this->_fakeTime = time();
        $mockService = $this->getMock('Rubedo\Time\CurrentTime');
        $mockService->expects($this->once())->method('getCurrentTime')->will($this->returnValue($this->_fakeTime));
        Rubedo\Services\Manager::setMockService('CurrentTime', $mockService);
    }

    /**
     * test of the read feature
     *
     * Create 3 items through Phactory and read them with the service
     * a version number is added on the fly
     */
    public function testRead()
    {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();
        $item = static::$phactory->create('item');
        $item['id'] = (string)$item['_id'];
        $item['version'] = 1;
        unset($item['_id']);
        $items[] = $item;

        $readArray = $dataAccessObject->read();

        $this->assertEquals($items, $readArray);

    }

    /**
     * Test of the create feature
     *
     * Create an item through the service and read it with Phactory
     * Check if a version property add been added
     */
    public function testCreate()
    {

        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = array('name' => 'created item 1');

        $createArray = $dataAccessObject->create($item, true);

        $this->assertTrue($createArray["success"]);
        $writtenItem = $createArray["data"];

        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));
        $this->assertEquals(1, count($readItems));
        $readItem = array_pop($readItems);
        $readItem['id'] = (string)$readItem['_id'];
        unset($readItem['_id']);

        $this->assertEquals($writtenItem, $readItem);

    }
	
	/**
     * Test of the create feature : "leaf" property should be ignored
     *
     * Create an item through the service and read it with Phactory
     * Check if a version property add been added
     */
    public function testCreateNoLeaf()
    {

        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = array('name' => 'created item 1','leaf'=>'toto');

        $createArray = $dataAccessObject->create($item, true);

        $writtenItem = $createArray["data"];


        $this->assertFalse(isset($writtenItem['leaf']));

    }
    
    /**
     * Check if version property add been added
     */
    public function testCreateVersionMetaData()
    {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = array('name' => 'created item 1');

        $createArray = $dataAccessObject->create($item, true);

        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));
        $readItem = array_pop($readItems);
        
        $this->assertArrayHasKey('version', $readItem);
        $this->assertEquals($readItem['version'],1);
    
    }
    
    /**
     * Check if  createUser and lastUpdateUser properties had been added
     * The CurrentUser service should be called once
     */
    public function testCreateUserMetaData()
    {
        $this->initUser();

        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = array('name' => 'created item 1');

        $createArray = $dataAccessObject->create($item, true);

        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));
        $readItem = array_pop($readItems);
        
        $this->assertArrayHasKey('createUser', $readItem);
        $this->assertEquals($readItem['createUser'],$this->_fakeUser);
        $this->assertArrayHasKey('lastUpdateUser', $readItem);
        $this->assertEquals($readItem['lastUpdateUser'],$this->_fakeUser);
    }

	/**
     * Check if  createTime and lastUpdateTime properties had been added
     * The CurrentTime service should be called once
     */
    public function testCreateTimeMetaData()
    {
    	$this->initTime();
		
		$dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = array('name' => 'created item 1');

        $createArray = $dataAccessObject->create($item, true);

        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));
        $readItem = array_pop($readItems);
		$this->assertArrayHasKey('createTime', $readItem);
		$this->assertEquals($readItem['createTime'],$this->_fakeTime);
        $this->assertArrayHasKey('lastUpdateTime', $readItem);
		$this->assertEquals($readItem['lastUpdateTime'],$this->_fakeTime);
	}
    

	/**
     * Test of the update feature 
     *
     * Create an item with phactory
     * Update it with the service
     * Read it again with phactory
     * Check if the version add been incremented
     */
    public function testUpdate()
    {
        $version = rand(1, 25);
        $item = static::$phactory->create('item', array('version' => $version));

        $itemId = (string)$item['_id'];
        $name = $item['name'];

        $item['id'] = $itemId;
        unset($item['_id']);
        $item['name'] .= ' updated';

        //actual begin of the application run
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $updateArray = $dataAccessObject->update($item, true);
        //end of application run

        $this->assertTrue($updateArray["success"]);
        $writtenItem = $updateArray["data"];

        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));
        $this->assertEquals(1, count($readItems));
        $readItem = array_pop($readItems);
        $readItem['id'] = (string)$readItem['_id'];
        unset($readItem['_id']);

        $this->assertEquals($writtenItem, $readItem);
        $this->assertEquals($readItem['name'],$name . ' updated');
    }

    /**
     * Test of the update feature
     *
     * Create an item with phactory
     * Update it with the service
     * Read it again with phactory
     * Check if the version had been incremented
     */
    public function testUpdateVersionMetaData()
    {
        $version = rand(1, 25);
        $item = static::$phactory->create('item', array('version' => $version));

        $itemId = (string)$item['_id'];
        $name = $item['name'];

        $item['id'] = $itemId;
        unset($item['_id']);
        $item['name'] .= ' updated';

        //actual begin of the application run
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $updateArray = $dataAccessObject->update($item, true);
        //end of application run


        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));
        $readItem = array_pop($readItems);

        $this->assertArrayHasKey('version', $readItem);
        $this->assertEquals($readItem['version'],$version + 1);
    }
    
    /**
     * Check if lastUpdateUser property had been updated
     * 
     */
    public function testUpdateUserMetaData()
    {
        $this->initUser();

        $version = rand(1, 25);
        $item = static::$phactory->create('item', array('version' => $version));

        $itemId = (string)$item['_id'];
        $name = $item['name'];

        $item['id'] = $itemId;
        unset($item['_id']);
        $item['name'] .= ' updated';

        //actual begin of the application run
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $updateArray = $dataAccessObject->update($item, true);
        //end of application run


        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));
        $readItem = array_pop($readItems);

        
        $this->assertArrayHasKey('lastUpdateUser', $readItem);
        $this->assertEquals($readItem['lastUpdateUser'],$this->_fakeUser);
    }
	
	/**
     * Check if lastUpdateTime property had been updated
     * 
     */
    public function testUpdateTimeMetaData()
    {
    	$this->initTime();
		
		$version = rand(1, 25);
        $item = static::$phactory->create('item', array('version' => $version));

        $itemId = (string)$item['_id'];
        $name = $item['name'];

        $item['id'] = $itemId;
        unset($item['_id']);
        $item['name'] .= ' updated';

        //actual begin of the application run
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $updateArray = $dataAccessObject->update($item, true);
        //end of application run
        
        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));
        $readItem = array_pop($readItems);
        
        $this->assertArrayHasKey('lastUpdateTime', $readItem);
        $this->assertEquals($readItem['lastUpdateTime'],$this->_fakeTime);
	}

    /**
     * Test of the update feature without a version number
     *
     * Create an item with phactory
     * Update it with the service
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testNoVersionUpdate()
    {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $version = rand(1, 25);
        $item = static::$phactory->create('item', array('version' => $version));

        $itemId = (string)$item['_id'];
        $name = $item['name'];

        $item['id'] = $itemId;
        unset($item['_id']);
        unset($item['version']);
        $item['name'] .= ' updated';

        $updateArray = $dataAccessObject->update($item, true);
    }

    /**
     * Test of the update feature
     *
     * Create an item with phactory
     * Update it with the service and a false version number
     * Should fail
     *
     */
    public function testConflictUpdate()
    {

        //$this->initUser();

        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $version = rand(1, 25);
        $item = static::$phactory->create('item', array('version' => $version + 1));

        $itemId = (string)$item['_id'];
        $name = $item['name'];

        $item['id'] = $itemId;
        $item['version'] = $version;
        unset($item['_id']);
        $item['name'] .= ' updated';

        $updateArray = $dataAccessObject->update($item, true);

        $this->assertFalse($updateArray['success']);
        $this->assertEquals('no record had been updated', $updateArray['msg']);

    }

	/**
     * Test of the update feature : leaf property should be ignored
     *
     * Create an item with phactory
     * Update it with the service
     * Read it again with phactory
     * Check if the version add been incremented
     */
    public function testUpdateNoLeaf()
    {
        $version = rand(1, 25);
        $item = static::$phactory->create('item', array('version' => $version));

        $itemId = (string)$item['_id'];
        $name = $item['name'];

        $item['id'] = $itemId;
        unset($item['_id']);
        $item['name'] .= ' updated';
		$item['leaf']='true';

        //actual begin of the application run
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $updateArray = $dataAccessObject->update($item, true);
        //end of application run

        $writtenItem = $updateArray["data"];

		$this->assertFalse(isset($writtenItem['leaf']));
    }

    /**
     * Test of the Destroy Feature
     *
     * Create items with Phactory
     * Delete one with the service
     * Check if the remaining items are OK and the deleted is no longer in DB
     */
    public function testDestroy()
    {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();
        for ($i = 0; $i < 3; $i++) {
            $item = static::$phactory->create('item', array('version' => 1));
            $item['id'] = (string)$item['_id'];
            unset($item['_id']);
            $items[] = $item;
        }

        $item = static::$phactory->create('item', array('version' => 1));

        $itemId = (string)$item['_id'];

        $item['id'] = $itemId;
        unset($item['_id']);

        $updateArray = $dataAccessObject->destroy($item, true);

        $this->assertTrue($updateArray["success"]);

        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));
        $this->assertEquals(3, count($readItems));

        $readItem = static::$phactory->getDb()->items->findOne(array('_id' => new mongoId($itemId)));

        $this->assertNull($readItem);
    }

    /**
     * Test of the Destroy Feature without a version parameter
     *
     * Create items with Phactory
     * Delete one with the service
     *
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testNoVersionDestroy()
    {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();
        for ($i = 0; $i < 3; $i++) {
            $item = static::$phactory->create('item', array('version' => 1));
            $item['id'] = (string)$item['_id'];
            unset($item['_id']);
            $items[] = $item;
        }

        $item = static::$phactory->create('item');

        $itemId = (string)$item['_id'];

        $item['id'] = $itemId;
        unset($item['_id']);

        $updateArray = $dataAccessObject->destroy($item, true);
    }

    /**
     * Test of the update feature
     *
     * Create an item with phactory
     * Update it with the service and a false version number
     * Should fail
     *
     */
    public function testConflictDestroy()
    {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();
        for ($i = 0; $i < 3; $i++) {
            $item = static::$phactory->create('item', array('version' => 1));
            $item['id'] = (string)$item['_id'];
            unset($item['_id']);
            $items[] = $item;
        }

        $item = static::$phactory->create('item', array('version' => 2));

        $itemId = (string)$item['_id'];
        $item['version'] = 1;
        $item['id'] = $itemId;
        unset($item['_id']);

        $updateArray = $dataAccessObject->destroy($item, true);
        $this->assertFalse($updateArray['success']);
        $this->assertEquals('no record had been deleted', $updateArray['msg']);
    }
	
	/**
     * test of the read as tree feature
     *
     * Create 3 items through Phactory and read them with the service
	 * 2 levels of items, 2 child on second level
     * check tree is as expected
     */
    public function testReadTreeOneLevelTwoElements()
    {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();
        $item = static::$phactory->create('item',array('version'=>1));
        $item['id'] = (string)$item['_id'];
		unset($item['_id']);
		
		$item2 = static::$phactory->create('item',array('parentId'=>$item['id'],'version'=>1));
		$item2['id'] = (string)$item2['_id'];
		$item2['children'] = array();
		unset($item2['_id']);
		unset($item2['parentId']);
		
		$item3 = static::$phactory->create('item',array('parentId'=>$item['id'],'version'=>1));
		$item3['id'] = (string)$item3['_id'];
		$item3['children'] = array();
		unset($item3['_id']);
		unset($item3['parentId']);
        
        $items = $item;
		$items['children'] = array($item2,$item3);

        $readArray = $dataAccessObject->readTree();

        $this->assertEquals($items, $readArray);

    }
	
	/**
     * test of the read as tree feature
     *
     * Create 3 items through Phactory and read them with the service
	 * 3 levels of items, 1 child on second level, 1 on third
     * check tree is as expected
     */
    public function testReadTreeTwoLevelOneElements()
    {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();
        $item = static::$phactory->create('item',array('version'=>1));
        $item['id'] = (string)$item['_id'];
		unset($item['_id']);
		
		$item2 = static::$phactory->create('item',array('parentId'=>$item['id'],'version'=>1));
		$item2['id'] = (string)$item2['_id'];
		
		unset($item2['_id']);
		unset($item2['parentId']);
		
		$item3 = static::$phactory->create('item',array('parentId'=>$item2['id'],'version'=>1));
		$item3['id'] = (string)$item3['_id'];
		$item3['children'] = array();
		unset($item3['_id']);
		unset($item3['parentId']);
        
		$item2['children'] = array($item3);
		
        $items = $item;
		$items['children'] = array($item2);

        $readArray = $dataAccessObject->readTree();

        $this->assertEquals($items, $readArray);

    }
	
	/**
     * test of the read as tree feature
     *
     * ParentId root means the same as no parentId
     */
    public function testReadTreeRootWithParentCalledRoot()
    {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();
        $item = static::$phactory->create('item',array('parentId'=>'root','version'=>1));
        $item['id'] = (string)$item['_id'];
		unset($item['_id']);
		unset($item['parentId']);
		
		$item2 = static::$phactory->create('item',array('parentId'=>$item['id'],'version'=>1));
		$item2['id'] = (string)$item2['_id'];
		
		unset($item2['_id']);
		unset($item2['parentId']);
		
		$item3 = static::$phactory->create('item',array('parentId'=>$item2['id'],'version'=>1));
		$item3['id'] = (string)$item3['_id'];
		$item3['children'] = array();
		unset($item3['_id']);
		unset($item3['parentId']);
        
		$item2['children'] = array($item3);
		
        $items = $item;
		$items['children'] = array($item2);

        $readArray = $dataAccessObject->readTree();

        $this->assertEquals($items, $readArray);

    }
	
	/**
     * test of the read as tree feature
     *
     * Create 3 items through Phactory without parentID
	 * readTree should fail as expected
	 * 
	 * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testReadTreeMoreThanOneRoot()
    {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();
        $item = static::$phactory->create('item',array('version'=>1));
        $item['id'] = (string)$item['_id'];
		unset($item['_id']);
		
		$item2 = static::$phactory->create('item',array('version'=>1));
		$item2['id'] = (string)$item2['_id'];
		unset($item2['_id']);
		
		$item3 = static::$phactory->create('item',array('version'=>1));
		$item3['id'] = (string)$item3['_id'];
		unset($item3['_id']);
        
        $readArray = $dataAccessObject->readTree();

    }
	
	
	/**
     * test of the children of a node in a tree
     *
     * Create 3 items through Phactory and read them with the service
	 * 2 levels of items, 2 child on second level
	 * Should return an array of items
     */
    public function testReadChild()
    {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();
        $item = static::$phactory->create('item',array('version'=>1));
        $item['id'] = (string)$item['_id'];
		$testId = $item['id'];
		unset($item['_id']);
		
		$item2 = static::$phactory->create('item',array('parentId'=>$item['id'],'version'=>1));
		$item2['id'] = (string)$item2['_id'];
		
		unset($item2['_id']);
		
		$item3 = static::$phactory->create('item',array('parentId'=>$item['id'],'version'=>1));
		$item3['id'] = (string)$item3['_id'];
		unset($item3['_id']);
        		
        $items = array($item2,$item3);

        $readArray = $dataAccessObject->readChild($testId);

        $this->assertEquals($items, $readArray);

    }
	
	/**
	 * Check if getFilterArray return an array
	 */
	public function testGetEmptyFilterArray(){
		$dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');
		$this->assertEquals('array',gettype($dataAccessObject->getFilterArray()));
	}
	
	/**
	 * Simple add filter and read it again test
	 */
	public function testAddFilter(){
		$dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');
		
		$filterExample = array('name'=>'value');
		$dataAccessObject->addFilter($filterExample);
		
		$this->assertEquals(array($filterExample),$dataAccessObject->getFilterArray());
		
	}

	/**
	 * two conditions case for add filter
	 */
	public function testAddFilterTwoItems(){
		$dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');
		
		$filterExample = array('name'=>'value');
		$filterExample2 = array('name2'=>'value2');
		$dataAccessObject->addFilter($filterExample);
		$dataAccessObject->addFilter($filterExample2);
		
		$this->assertEquals(array($filterExample,$filterExample2),$dataAccessObject->getFilterArray());
		
	}
	
	/**
	 * Simple clear Filter Test
	 */
	public function testClearFilter(){
		$dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');
		
		$filterExample = array('name'=>'value');
		$filterExample2 = array('name2'=>'value2');
		$dataAccessObject->addFilter($filterExample);
		$dataAccessObject->addFilter($filterExample2);
		
		$dataAccessObject->clearFilter();
		
		$this->assertEquals(array(),$dataAccessObject->getFilterArray());
		
	}
	
	/**
	 * filter should not be empty
	 * @expectedException \Rubedo\Exceptions\DataAccess
	 */
	public function testAddFilterNotBeEmpty(){
		$dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');
		
		$filterExample = array();
		$dataAccessObject->addFilter($filterExample);
		
	}
	
	/**
	 * filter should not have more than one item
	 * @expectedException \Rubedo\Exceptions\DataAccess
	 */
	public function testAddFilterNotTwoArgs(){
		$dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');
		
		$filterExample = array('toto','titi');
		$dataAccessObject->addFilter($filterExample);
		
	}

	/**
	 * filter should not be empty
	 * @expectedException \Rubedo\Exceptions\DataAccess
	 */
	public function testAddFilterOnlyOneArrayChild(){
		$dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');
		
		$filterExample = array("key"=>array('titi','toto'));
		$dataAccessObject->addFilter($filterExample);
		
	}
	
	/**
	 * filter should not be empty
	 * @expectedException \Rubedo\Exceptions\DataAccess
	 */
	public function testAddFilterOnlyArrayOrScalarChild(){
		$dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');
		
		$filterExample = array("key"=> array(new stdClass()));
		$dataAccessObject->addFilter($filterExample);
		
	}

}
