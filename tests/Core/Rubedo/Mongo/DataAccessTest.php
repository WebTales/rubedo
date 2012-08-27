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
    }

    /**
     * init the Zend Application for tests
     */
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }

    /**
     * test of the read feature
     *
     * Create 3 items through Phactory and read them with the service
     */
    public function testRead()
    {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();
        for ($i = 0; $i < 3; $i++) {
            $item = static::$phactory->create('item');
            $item['id'] = (string)$item['_id'];
            unset($item['_id']);
            $items[] = $item;
        }

        $readArray = $dataAccessObject->read();

        $this->assertEquals(3, count($readArray));
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

        $origItem = array('name' => 'created item 1');
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

        $origItem['version'] = 1;
        unset($readItem['id']);

        $this->assertEquals($origItem,$readItem);
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
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $version = rand(1, 25);
        $item = static::$phactory->create('item', array('version' => $version));

        $itemId = (string)$item['_id'];
        $name = $item['name'];

        $item['id'] = $itemId;
        unset($item['_id']);
        $item['name'] .= ' updated';

        $updateArray = $dataAccessObject->update($item, true);

        $this->assertTrue($updateArray["success"]);
        $writtenItem = $updateArray["data"];

        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));
        $this->assertEquals(1, count($readItems));
        $readItem = array_pop($readItems);
        $readItem['id'] = (string)$readItem['_id'];
        unset($readItem['_id']);

        $this->assertEquals($writtenItem, $readItem);
        $this->assertEquals($itemId, $readItem['id']);

        $targetItem = array('name' => $name . ' updated', 'id' => $itemId, 'version' => $version + 1);

        $this->assertEquals($targetItem, $readItem);
    }

    /**
     * Test of the update feature
     *
     * Create an item with phactory
     * Update it with the service and a false version number
     * Should fail
     *
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testConflictUpdate()
    {
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
            $item = static::$phactory->create('item',array('version'=>1));
            $item['id'] = (string)$item['_id'];
            unset($item['_id']);
            $items[] = $item;
        }

        $item = static::$phactory->create('item');

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
     * Test of the update feature
     *
     * Create an item with phactory
     * Update it with the service and a false version number
     * Should fail
     *
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testConflictDestroy()
    {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();
        for ($i = 0; $i < 3; $i++) {
            $item = static::$phactory->create('item',array('version'=>1));
            $item['id'] = (string)$item['_id'];
            unset($item['_id']);
            $items[] = $item;
        }

        $item = static::$phactory->create('item',array('version'=>2));

        $itemId = (string)$item['_id'];
        $item['version'] = 1;
        $item['id'] = $itemId;
        unset($item['_id']);

        $updateArray = $dataAccessObject->destroy($item, true);
    }

}
