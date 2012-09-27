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
        $mockService = $this->getMock('Rubedo\User\CurrentUser');
        Rubedo\Services\Manager::setMockService('CurrentUser', $mockService);
        parent::setUp();
    }

    /**
     * Initialize a mock user service
     */
    public function initUser()
    {
        $this->_fakeUser = array('id' => 1, 'login' => (string) rand(21, 128));
        $mockService = $this->getMock('Rubedo\User\CurrentUser');
        $mockService->expects($this->once())->method('getCurrentUserSummary')->will($this->returnValue($this->_fakeUser));
        Rubedo\Services\Manager::setMockService('CurrentUser', $mockService);
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
     * Check if  createUser and lastUpdateUser properties add been added
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
     * Check if the version add been incremented
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
     * Test of the update feature
     *
     * Create an item with phactory
     * Update it with the service
     * Read it again with phactory
     * Check if the version add been incremented
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

}
