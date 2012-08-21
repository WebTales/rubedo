<?php

class DataAccessTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        \Rubedo\Mongo\DataAccess::getDefaultMongo();
        // create a db connection and tell Phactory to use it
        $mongo = new Mongo(\Rubedo\Mongo\DataAccess::getDefaultMongo());
        Phactory::setDb($mongo->test_db);

        // reset any existing blueprints and empty any tables Phactory has used
        Phactory::reset();

        // define default values for each user we will create
        Phactory::define('item', array('name' => 'Test item $n'));
    }

    public function tearDown()
    {
        Phactory::recall();
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
            $item = Phactory::create('item');
            $item['id'] = (string)$item['_id'];
            unset($item['_id']);
            $items[] = $item;
        }

        $nano = time_nanosleep(0, 100000);
        //délai de 100 micro secondes

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
        $nano = time_nanosleep(0, 100000);
        //délai de 100 micro secondes

        $this->assertTrue($createArray["success"]);
        $writtenItem = $createArray["data"];

        $readItems = array_values(iterator_to_array(Phactory::getDb()->items->find()));
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

        $item = Phactory::create('item');

        $nano = time_nanosleep(0, 100000);
        //délai de 100 micro secondes

        $itemId = (string)$item['_id'];

        $item['id'] = $itemId;
        unset($item['_id']);
        $item['name'] .= ' updated';

        $updateArray = $dataAccessObject->update($item, true);

        $nano = time_nanosleep(0, 100000);
        //délai de 100 micro secondes

        $this->assertTrue($updateArray["success"]);
        $writtenItem = $updateArray["data"];

        $readItems = array_values(iterator_to_array(Phactory::getDb()->items->find()));
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
            $item = Phactory::create('item');
            $item['id'] = (string)$item['_id'];
            unset($item['_id']);
            $items[] = $item;
        }

        $item = Phactory::create('item');

        $nano = time_nanosleep(0, 100000);
        //délai de 100 micro secondes

        $itemId = (string)$item['_id'];

        $item['id'] = $itemId;
        unset($item['_id']);

        $updateArray = $dataAccessObject->destroy($item, true);

        $nano = time_nanosleep(0, 100000);
        //délai de 100 micro secondes

        $this->assertTrue($updateArray["success"]);
        
        $readItems = array_values(iterator_to_array(Phactory::getDb()->items->find()));
        $this->assertEquals(3, count($readItems));

        $readItem = Phactory::getDb()->items->findOne(array('_id' => new mongoId($itemId)));

        $this->assertNull($readItem);    }

}
