<?php

class DataAccessTest extends PHPUnit_Framework_TestCase
{
    protected static $phactory; 
    
    public static function setUpBeforeClass()
    {
        \Rubedo\Mongo\DataAccess::getDefaultMongo();
        // create a db connection and tell Phactory to use it
        $mongo = new Mongo(\Rubedo\Mongo\DataAccess::getDefaultMongo());
        $mongoDb = $mongo->test_db;
        
        static::$phactory = new \Phactory\Mongo\Phactory($mongoDb);

        // reset any existing blueprints and empty any tables Phactory has used
        static::$phactory->reset();

        // define default values for each user we will create
        static::$phactory->define('item', array('name' => 'Test item $n'));
    }

    public function tearDown()
    {
        static::$phactory->recall();
    }

    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }

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

    public function testUpdate()
    {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item');

        $itemId = (string)$item['_id'];

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
    }

    public function testDestroy()
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

        $item = static::$phactory->create('item');

        $itemId = (string)$item['_id'];

        $item['id'] = $itemId;
        unset($item['_id']);

        $updateArray = $dataAccessObject->destroy($item, true);

        $this->assertTrue($updateArray["success"]);
        
        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));
        $this->assertEquals(3, count($readItems));

        $readItem = static::$phactory->getDb()->items->findOne(array('_id' => new mongoId($itemId)));

        $this->assertNull($readItem);    }

}
