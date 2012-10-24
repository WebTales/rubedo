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
class DataAccessTest extends PHPUnit_Framework_TestCase {
    /**
     * Phactory : database fixture handler
     * @var \Phactory\Mongo\Phactory
     */
    protected static $phactory;

    /**
     * Fixture : MongoDB dataset for tests
     * Create an "item" blueprint for testing purpose
     */
    public static function setUpBeforeClass() {
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
    public function tearDown() {
        static::$phactory->recall();
        Rubedo\Services\Manager::resetMocks();
    }

    /**
     * init the Zend Application for tests
     */
    public function setUp() {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		$this->bootstrap->bootstrap();
        $mockUserService = $this->getMock('Rubedo\User\CurrentUser');
        Rubedo\Services\Manager::setMockService('CurrentUser', $mockUserService);

        $mockTimeService = $this->getMock('Rubedo\Time\CurrentTime');
        Rubedo\Services\Manager::setMockService('CurrentTime', $mockTimeService);

        parent::setUp();
    }

    /**
     * Initialize a mock CurrentUser service
     */
    public function initUser() {
        $this->_fakeUser = array('id' => 1, 'login' => (string) rand(21, 128));
        $mockService = $this->getMock('Rubedo\User\CurrentUser');
        $mockService->expects($this->once())->method('getCurrentUserSummary')->will($this->returnValue($this->_fakeUser));
        Rubedo\Services\Manager::setMockService('CurrentUser', $mockService);
    }

    /**
     * Initialize a mock CurrentTime service
     */
    public function initTime() {
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
    public function testRead() {
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
     * test of the read feature
     *	Case with a simple filter
     */
    public function testReadWithFilter() {
        $items = array();

        $item = static::$phactory->create('item', array('criteria' => 'jack'));
        $item['id'] = (string)$item['_id'];
        $item['version'] = 1;
        unset($item['_id']);

        $items[] = $item;

        $otherItem = static::$phactory->create('item');

        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $dataAccessObject->addFilter(array('criteria' => 'jack'));

        $readArray = $dataAccessObject->read();

        $this->assertEquals($items, $readArray);

    }

    /**
     * test of the read feature
     * Case of a lesser than filter
     *
     */
    public function testReadWithFilterLesserThan() {
        $items = array();
        $item = static::$phactory->create('item', array('criteria' => 1));
        $item['id'] = (string)$item['_id'];
        $item['version'] = 1;
        unset($item['_id']);
        $items[] = $item;

        $otherItem = static::$phactory->create('item', array('criteria' => 2));

        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $dataAccessObject->addFilter(array('criteria' => array('$lte' => 1)));

        $readArray = $dataAccessObject->read();

        $this->assertEquals($items, $readArray);

    }

    /**
     * test of the read feature
     * 	case with a regexp filter
     *
     */
    public function testReadWithFilterRegexp() {
        $items = array();
        $item = static::$phactory->create('item', array('criteria' => 'mammouth'));
        $item['id'] = (string)$item['_id'];
        $item['version'] = 1;
        unset($item['_id']);
        $items[] = $item;

        $otherItem = static::$phactory->create('item', array('criteria' => 'bear'));

        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $dataAccessObject->addFilter(array('criteria' => array('$regex' => new \MongoRegex('/mam.*/i'))));

        $readArray = $dataAccessObject->read();

        $this->assertEquals($items, $readArray);

    }

    /**
     * test of the read feature
     *	Case with a simple filter
     */
    public function testReadWithTwoFilter() {
        $items = array();
        $item = static::$phactory->create('item', array('criteria' => 'jack', 'otherCriteria' => 1));
        $item['id'] = (string)$item['_id'];
        $item['version'] = 1;
        unset($item['_id']);
        $items[] = $item;

        $otherItem = static::$phactory->create('item', array('criteria' => 'john', 'otherCriteria' => 1));
        $againAnotherItem = static::$phactory->create('item', array('criteria' => 'jack', 'otherCriteria' => 2));

        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $dataAccessObject->addFilter(array('criteria' => 'jack'));
        $dataAccessObject->addFilter(array('otherCriteria' => 1));

        $readArray = $dataAccessObject->read();

        $this->assertEquals($items, $readArray);

    }

    /**
     * Test read with a sort
     *
     * Case with a simple ascendant sort by user name
     */
    public function testReadWithAscSort() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('user' => 'john', 'version' => '1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('user' => 'marie', 'version' => '1'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('user' => 'alice', 'version' => '1'));
        $item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);

        $dataAccessObject->addSort(array('user' => 'asc'));

        $expectedResult = array($item3, $item, $item2);

        $readArray = $dataAccessObject->read();

        $this->assertEquals($expectedResult, $readArray);
    }

    /**
     * Test read with a sort
     *
     * Case with a simple descendant sort by user name
     */
    public function testReadWithDescSort() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('user' => 'john', 'version' => '1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('user' => 'marie', 'version' => '1'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('user' => 'alice', 'version' => '1'));
        $item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);

        $dataAccessObject->addSort(array('user' => 'desc'));

        $expectedResult = array($item2, $item, $item3);

        $readArray = $dataAccessObject->read();

        $this->assertEquals($expectedResult, $readArray);
    }

    /**
     * Test read with two sort
     *
     * Case with two ascendant sort by user name and user first name
     */
    public function testReadWithTwoAscSort() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('name' => 'john', 'firstname' => 'carter', 'version' => '1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('name' => 'marie', 'firstname' => 'lyne', 'version' => '1'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('name' => 'alice', 'firstname' => 'wonderland', 'version' => '1'));
        $item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);

        $item4 = static::$phactory->create('item', array('name' => 'alice', 'firstname' => 'ecila', 'version' => '1'));
        $item4['id'] = (string)$item4['_id'];
        unset($item4['_id']);

        $dataAccessObject->addSort(array('name' => 'asc'));
        $dataAccessObject->addSort(array('firstname' => 'asc'));

        $expectedResult = array($item4, $item3, $item, $item2);

        $readArray = $dataAccessObject->read();

        $this->assertEquals($expectedResult, $readArray);
    }

    /**
     * Test read with two sort
     *
     * Case with two descendant sort by user name and user first name
     */
    public function testReadWithTwoDescSort() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('name' => 'john', 'firstname' => 'carter', 'version' => '1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('name' => 'marie', 'firstname' => 'lyne', 'version' => '1'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('name' => 'alice', 'firstname' => 'wonderland', 'version' => '1'));
        $item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);

        $item4 = static::$phactory->create('item', array('name' => 'alice', 'firstname' => 'ecila', 'version' => '1'));
        $item4['id'] = (string)$item4['_id'];
        unset($item4['_id']);

        $dataAccessObject->addSort(array('name' => 'desc'));
        $dataAccessObject->addSort(array('firstname' => 'desc'));

        $expectedResult = array($item2, $item, $item3, $item4);

        $readArray = $dataAccessObject->read();

        $this->assertEquals($expectedResult, $readArray);
    }

    /**
     * test if read function works fine with imposed fields
     *
     * The result doesn't contain the password and first name field
     */
    public function testReadWithIncludedField() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('name' => 'john', 'firstname' => 'carter', 'password' => 'blabla', 'version' => '1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('name' => 'marie', 'firstname' => 'lyne', 'password' => 'titi', 'version' => '1'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $includedFields = array('name');
        $sort = array('name' => 'asc');

        $dataAccessObject->addToFieldList($includedFields);
        $dataAccessObject->addSort($sort);

        $expectedResult = array( array('name' => 'john', 'id' => $item['id'], 'version' => $item['version']), array('name' => 'marie', 'id' => $item2['id'], 'version' => $item2['version']));
        $readArray = $dataAccessObject->read();

        $this->assertEquals($expectedResult, $readArray);
    }

    /**
     * test if read function works fine with imposed fields
     *
     * The result doesn't contain only the password field
     */
    public function testReadWithExcludedField() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('name' => 'john', 'firstname' => 'carter', 'password' => 'blabla', 'version' => '1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('name' => 'marie', 'firstname' => 'lyne', 'password' => 'titi', 'version' => '1'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $includedFields = array('password');
        $sort = array('name' => 'asc');

        $dataAccessObject->addToExcludeFieldList($includedFields);
        $dataAccessObject->addSort($sort);

        $expectedResult = array( array('name' => 'john', 'firstname' => 'carter', 'id' => $item['id'], 'version' => $item['version']), array('name' => 'marie', 'firstname' => 'lyne', 'id' => $item2['id'], 'version' => $item2['version']));
        $readArray = $dataAccessObject->read();

        $this->assertEquals($expectedResult, $readArray);
    }
	
	/**
	 * Read with a specified number for the first result
	 */
    public function testReadWithFirstResult() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');
		
		$item = static::$phactory->create('item', array('name' => 'john', 'version' => '1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('name' => 'marie', 'version' => '1'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
		$sort = array('name' => 'asc');
		$dataAccessObject->addSort($sort);
		
		$dataAccessObject->setFirstResult(1);
		
		$expectedResult = array($item2);
		$result = $dataAccessObject->read();
		
		$this->assertEquals($expectedResult, $result);
    }
	
	/**
	 * Read with a specified number of results
	 */
	public function testReadWithNumberOfResults() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');
		
		$item = static::$phactory->create('item', array('name' => 'john', 'version' => '1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('name' => 'marie', 'version' => '1'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
		$sort = array('name' => 'asc');
		$dataAccessObject->addSort($sort);
		
		$dataAccessObject->setNumberOfResults(1);
		
		$expectedResult = array($item);
		$result = $dataAccessObject->read();
		
		$this->assertEquals($expectedResult, $result);
    }
	
	/**
	 * Read with a specified number of results and a specified number for the first result
	 */
	public function testReadWithFirstNumberAndNumberOfResult(){
		$dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');
		
		$item = static::$phactory->create('item', array('name' => 'john', 'version' => '1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('name' => 'marie', 'version' => '1'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
		$item2 = static::$phactory->create('item', array('name' => 'carle', 'version' => '1'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
		$sort = array('name' => 'asc');
		$dataAccessObject->addSort($sort);
		
		$dataAccessObject->setNumberOfResults(1);
		$dataAccessObject->setFirstResult(1);
		
		$expectedResult = array($item);
		$result = $dataAccessObject->read();
		
		$this->assertEquals($expectedResult, $result);
	}

    /**
     * Test of the create feature
     *
     * Create an item through the service and read it with Phactory
     * Check if a version property add been added
     */
    public function testCreate() {

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
    public function testCreateNoLeaf() {

        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = array('name' => 'created item 1', 'leaf' => 'toto');

        $createArray = $dataAccessObject->create($item, true);

        $writtenItem = $createArray["data"];

        $this->assertFalse(isset($writtenItem['leaf']));

    }

    /**
     * Check if version property add been added
     */
    public function testCreateVersionMetaData() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = array('name' => 'created item 1');

        $createArray = $dataAccessObject->create($item, true);

        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));
        $readItem = array_pop($readItems);

        $this->assertArrayHasKey('version', $readItem);
        $this->assertEquals($readItem['version'], 1);

    }

    /**
     * Check if  createUser and lastUpdateUser properties had been added
     * The CurrentUser service should be called once
     */
    public function testCreateUserMetaData() {
        $this->initUser();

        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = array('name' => 'created item 1');

        $createArray = $dataAccessObject->create($item, true);

        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));
        $readItem = array_pop($readItems);

        $this->assertArrayHasKey('createUser', $readItem);
        $this->assertEquals($readItem['createUser'], $this->_fakeUser);
        $this->assertArrayHasKey('lastUpdateUser', $readItem);
        $this->assertEquals($readItem['lastUpdateUser'], $this->_fakeUser);
    }

    /**
     * Check if  createTime and lastUpdateTime properties had been added
     * The CurrentTime service should be called once
     */
    public function testCreateTimeMetaData() {
        $this->initTime();

        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = array('name' => 'created item 1');

        $createArray = $dataAccessObject->create($item, true);

        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));
        $readItem = array_pop($readItems);
        $this->assertArrayHasKey('createTime', $readItem);
        $this->assertEquals($readItem['createTime'], $this->_fakeTime);
        $this->assertArrayHasKey('lastUpdateTime', $readItem);
        $this->assertEquals($readItem['lastUpdateTime'], $this->_fakeTime);
    }

    /**
     * Test of the update feature
     *
     * Create an item with phactory
     * Update it with the service
     * Read it again with phactory
     * Check if the version add been incremented
     */
    public function testUpdate() {
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
        $this->assertEquals($readItem['name'], $name . ' updated');
    }

    /**
     * Test of the update feature
     *
     * Create an item with phactory
     * Update it with the service
     * Read it again with phactory
     * Check if the version had been incremented
     */
    public function testUpdateVersionMetaData() {
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
        $this->assertEquals($readItem['version'], $version + 1);
    }

    /**
     * Check if lastUpdateUser property had been updated
     *
     */
    public function testUpdateUserMetaData() {
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
        $this->assertEquals($readItem['lastUpdateUser'], $this->_fakeUser);
    }

    /**
     * Check if lastUpdateTime property had been updated
     *
     */
    public function testUpdateTimeMetaData() {
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
        $this->assertEquals($readItem['lastUpdateTime'], $this->_fakeTime);
    }

    /**
     * Test of the update feature without a version number
     *
     * Create an item with phactory
     * Update it with the service
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testNoVersionUpdate() {
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
    public function testConflictUpdate() {

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
    public function testUpdateNoLeaf() {
        $version = rand(1, 25);
        $item = static::$phactory->create('item', array('version' => $version));

        $itemId = (string)$item['_id'];
        $name = $item['name'];

        $item['id'] = $itemId;
        unset($item['_id']);
        $item['name'] .= ' updated';
        $item['leaf'] = 'true';

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
    public function testDestroy() {
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
    public function testNoVersionDestroy() {
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
    public function testConflictDestroy() {
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
    public function testReadTreeOneLevelTwoElements() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();
        $item = static::$phactory->create('item', array('version' => 1));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 1));
        $item2['id'] = (string)$item2['_id'];
        $item2['children'] = array();
        unset($item2['_id']);
        unset($item2['parentId']);

        $item3 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 1));
        $item3['id'] = (string)$item3['_id'];
        $item3['children'] = array();
        unset($item3['_id']);
        unset($item3['parentId']);

        $items = $item;
        $items['children'] = array($item2, $item3);

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
    public function testReadTreeTwoLevelOneElements() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();
        $item = static::$phactory->create('item', array('version' => 1));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 1));
        $item2['id'] = (string)$item2['_id'];

        unset($item2['_id']);
        unset($item2['parentId']);

        $item3 = static::$phactory->create('item', array('parentId' => $item2['id'], 'version' => 1));
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
    public function testReadTreeRootWithParentCalledRoot() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();
        $item = static::$phactory->create('item', array('parentId' => 'root', 'version' => 1));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);
        unset($item['parentId']);

        $item2 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 1));
        $item2['id'] = (string)$item2['_id'];

        unset($item2['_id']);
        unset($item2['parentId']);

        $item3 = static::$phactory->create('item', array('parentId' => $item2['id'], 'version' => 1));
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
    public function testReadTreeMoreThanOneRoot() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();
        $item = static::$phactory->create('item', array('version' => 1));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('version' => 1));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('version' => 1));
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
    public function testReadChild() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $items = array();

        $item = static::$phactory->create('item', array('version' => 1));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 1));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 1));
        $item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);

        $testId = $item['id'];

        $items = array($item2, $item3);
		$dataAccessObject->addSort(array('name'=>'asc'));
        $readArray = $dataAccessObject->readChild($testId);

        $this->assertEquals($items, $readArray);

    }

    /**
     * test readChild with a filter
     *
     * Case with a simple filter
     */
    public function testReadChildWithFilter() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('version' => 1, 'name' => 'item1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 1, 'name' => 'item2'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 1, 'name' => 'item3'));
        $item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);

        $testId = $item['id'];

        $expectedResult = array($item3);

        $dataAccessObject->addFilter(array('name' => $item3['name']));

        $readArray = $dataAccessObject->readChild($testId);

        $this->assertEquals($expectedResult, $readArray);
    }

    /**
     * test readChild with a greater than filter
     *
     * Case with a greater than filter
     */
    public function testReadChildWithFilterGreaterThan() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('version' => 1, 'name' => 'item1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 2));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 1));
        $item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);

        $testId = $item['id'];

        $expectedResult = array($item2);

        $dataAccessObject->addFilter(array('version' => array('$gt' => 1)));

        $readArray = $dataAccessObject->readChild($testId);

        $this->assertEquals($expectedResult, $readArray);
    }

    /**
     * test readChild with a regexp filter
     *
     * Case with a regexp filter
     */
    public function testReadChildWithFilterRegexp() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('version' => 1, 'name' => 'item1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 1, 'name' => 'Update'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 1, 'name' => 'Creation'));
        $item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);

        $testId = $item['id'];

        $expectedResult = array($item2);

        $dataAccessObject->addFilter(array('name' => array('$regex' => new \MongoRegex('/Up.*/i'))));

        $readArray = $dataAccessObject->readChild($testId);

        $this->assertEquals($expectedResult, $readArray);

    }

    /**
     * test readChild with two filter
     *
     * Case with two filter
     */
    public function testReadChildWithTwoFilter() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('version' => 1, 'name' => 'item1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 1, 'name' => 'Update'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 3, 'name' => 'Creation'));
        $item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);

        $testId = $item['id'];

        $expectedResult = array($item3);

        $dataAccessObject->addFilter(array('version' => array('$lte' => 5)));
        $dataAccessObject->addFilter(array('name' => array('$regex' => new \MongoRegex('/Cre.*/i'))));

        $readArray = $dataAccessObject->readChild($testId);

        $this->assertEquals($expectedResult, $readArray);

    }

    /**
     * test readChild with sort
     *
     * Case with ascendant sort
     */
    public function testReadChildWithAscSort() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('version' => 1, 'name' => 'item1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 3, 'name' => 'Update'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 2, 'name' => 'Creation'));
        $item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);

        $testId = $item['id'];

        $expectedResult = array($item3, $item2);

        $dataAccessObject->addSort(array('version' => 'asc'));

        $readArray = $dataAccessObject->readChild($testId);

        $this->assertEquals($expectedResult, $readArray);

    }

    /**
     * test readChild with sort
     *
     * Case with descendant sort
     */
    public function testReadChildWithDescSort() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('version' => 1, 'name' => 'item1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 1, 'name' => 'Update'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 3, 'name' => 'Creation'));
        $item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);

        $testId = $item['id'];

        $expectedResult = array($item3, $item2);

        $dataAccessObject->addSort(array('version' => 'desc'));

        $readArray = $dataAccessObject->readChild($testId);

        $this->assertEquals($expectedResult, $readArray);

    }

    /**
     * test readChild with sort
     *
     * Case with two ascendant sort
     */
    public function testReadChildWithTwoAscSort() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('version' => 1, 'name' => 'item1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 3, 'name' => 'Creation'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 3, 'name' => 'Update'));
        $item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);

        $item4 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 2, 'name' => 'Creation'));
        $item4['id'] = (string)$item4['_id'];
        unset($item4['_id']);

        $testId = $item['id'];

        $expectedResult = array($item4, $item2, $item3);

        $dataAccessObject->addSort(array('version' => 'asc'));
        $dataAccessObject->addSort(array('name' => 'asc'));

        $readArray = $dataAccessObject->readChild($testId);

        $this->assertEquals($expectedResult, $readArray);

    }

    /**
     * test readChild with sort
     *
     * Case with two descendant sort
     */
    public function testReadChildWithTwoDescSort() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('version' => 1, 'name' => 'item1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 3, 'name' => 'Creation'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 3, 'name' => 'Update'));
        $item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);

        $item4 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 2, 'name' => 'Creation'));
        $item4['id'] = (string)$item4['_id'];
        unset($item4['_id']);

        $testId = $item['id'];

        $expectedResult = array($item3, $item2, $item4);

        $dataAccessObject->addSort(array('version' => 'desc'));
        $dataAccessObject->addSort(array('name' => 'desc'));

        $readArray = $dataAccessObject->readChild($testId);

        $this->assertEquals($expectedResult, $readArray);

    }

    /**
     * test if readChild function works fine with included fields
     */
    public function testReadChildWithIncludedField() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('version' => 1, 'name' => 'item1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 1, 'name' => 'Creation'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('parentId' => $item['id'], 'version' => 1, 'name' => 'Update'));
        $item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);

        $includedFields = array('name');
        $sort = array('name' => 'asc');

        $dataAccessObject->addToFieldList($includedFields);
        $dataAccessObject->addSort($sort);

        //contain the expected result of readChild fonction
        $expectedResult = array( array('name' => 'Creation', 'id' => $item2['id'], 'version' => $item2['version']), array('name' => 'Update', 'id' => $item3['id'], 'version' => $item3['version']));
        $readArray = $dataAccessObject->readChild($item['id']);

        $this->assertEquals($expectedResult, $readArray);
    }

    /**
     * test if readChild function works fine with excluded fields
     */
    public function testReadChildWithExcludedField() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('version' => 1, 'name' => 'item1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('parentId' => $item['id'], 'name' => 'john', 'firstname' => 'carter', 'password' => 'blabla', 'version' => 1));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('parentId' => $item['id'], 'name' => 'marie', 'firstname' => 'lyne', 'password' => 'titi', 'version' => 1));
        $item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);

        $excludedFields = array('password', 'parentId');
        $sort = array('name' => 'asc');

        $dataAccessObject->addToExcludeFieldList($excludedFields);
        $dataAccessObject->addSort($sort);

        $expectedResult = array( array('name' => 'john', 'firstname' => 'carter', 'id' => $item2['id'], 'version' => $item2['version']), array('name' => 'marie', 'firstname' => 'lyne', 'id' => $item3['id'], 'version' => $item3['version']));
        $readArray = $dataAccessObject->readChild($item['id']);

        $this->assertEquals($expectedResult, $readArray);
    }

    /**
     * test of findOne
     */
    public function testFindOne() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('version' => 1, 'name' => 'item1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);

        $item2 = static::$phactory->create('item', array('version' => 1, 'name' => 'item2'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $expectedResult = $item;

        $value = array('name' => $item['name']);
        $readArray = $dataAccessObject->findOne($value);

        $this->assertEquals($expectedResult, $readArray);
    }

    /**
     * Check if getFilterArray return an array
     */
    public function testGetEmptyFilterArray() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');
        $this->assertEquals('array', gettype($dataAccessObject->getFilterArray()));
    }

    /**
     * Simple add filter and read it again test
     */
    public function testAddFilter() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $filterExample = array('name' => 'value');
        $dataAccessObject->addFilter($filterExample);

        $this->assertEquals($filterExample, $dataAccessObject->getFilterArray());

    }

    /**
     * two conditions case for add filter
     */
    public function testAddFilterTwoItems() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $filterExample = array('name' => 'value');
        $filterExample2 = array('name2' => 'value2');
        $filters = array_merge($filterExample, $filterExample2);
        $dataAccessObject->addFilter($filterExample);
        $dataAccessObject->addFilter($filterExample2);

        $this->assertEquals($filters, $dataAccessObject->getFilterArray());

    }

    /**
     * Simple clear Filter Test
     */
    public function testClearFilter() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $filterExample = array('name' => 'value');
        $filterExample2 = array('name2' => 'value2');
        $dataAccessObject->addFilter($filterExample);
        $dataAccessObject->addFilter($filterExample2);

        $dataAccessObject->clearFilter();

        $this->assertEquals(array(), $dataAccessObject->getFilterArray());

    }

    /**
     * filter should not be empty
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testAddFilterNotBeEmpty() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $filterExample = array();
        $dataAccessObject->addFilter($filterExample);

    }

    /**
     * filter should not have more than one item
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testAddFilterNotTwoArgs() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $filterExample = array('toto', 'titi');
        $dataAccessObject->addFilter($filterExample);

    }

    /**
     * filter should not be empty
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testAddFilterOnlyOneArrayChild() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $filterExample = array("key" => array('titi', 'toto'));
        $dataAccessObject->addFilter($filterExample);

    }

    /**
     * filter should not be empty
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testAddFilterOnlyArrayOrScalarChild() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $filterExample = array("key" => array(new stdClass()));
        $dataAccessObject->addFilter($filterExample);

    }

    /**
     * Check if getSortArray return an array
     */
    public function testGetEmptySortArray() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');
        $this->assertEquals('array', gettype($dataAccessObject->getSortArray()));
    }

    /**
     * Simple add sort and read it again test
     */
    public function testAddSort() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $sortExample = array('name' => 1);
        $dataAccessObject->addSort($sortExample);

        $this->assertEquals($sortExample, $dataAccessObject->getSortArray());

    }

    /**
     * two conditions case for add sort
     */
    public function testAddSortTwoItems() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $sortExample = array('name' => 1);
        $sortExample2 = array('firstname' => 1);
        $sorts = array_merge($sortExample, $sortExample2);
        $dataAccessObject->addSort($sortExample);
        $dataAccessObject->addSort($sortExample2);

        $this->assertEquals($sorts, $dataAccessObject->getSortArray());

    }

    /**
     * Simple clear Sort Test
     */
    public function testClearSort() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $sortExample = array('name' => 1);
        $sortExample2 = array('firstname' => 1);
        $dataAccessObject->addSort($sortExample);
        $dataAccessObject->addSort($sortExample2);

        $dataAccessObject->clearSort();

        $this->assertEquals(array(), $dataAccessObject->getSortArray());

    }

    /**
     * sort should not be empty
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testAddSortNotBeEmpty() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $sortExample = array();
        $dataAccessObject->addSort($sortExample);

    }

    /**
     * sort should not have more than one item
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testAddSortNotTwoArgs() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $sortExample = array('toto', 'titi');
        $dataAccessObject->addSort($sortExample);

    }

    /**
     * sort should not be empty
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testAddSortOnlyOneArrayChild() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $sortExample = array("key" => array('titi', 'toto'));
        $dataAccessObject->addSort($sortExample);

    }

    /**
     * sort should not be empty
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testAddSortOnlyArrayOrScalarChild() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $sortExample = array("key" => array(new stdClass()));
        $dataAccessObject->addSort($sortExample);

    }

    /**
     * Try to set the number of the first result
     */
    public function testSetFirstResult() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $dataAccessObject->setFirstResult(5);

        $expectedResult = 5;
        $result = $dataAccessObject->getFirstResult();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Try to set the number of results
     */
    public function testSetNumberOfResults() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $dataAccessObject->setNumberOfResults(20);

        $expectedResult = 20;
        $result = $dataAccessObject->getNumberOfResults();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Try to clear the number of the first result
     */
    public function testClearFirstResult() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $dataAccessObject->setFirstResult(5);
        $dataAccessObject->clearFirstResult();

        $expectedResult = 0;
        $result = $dataAccessObject->getFirstResult();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Try to clear the number of results
     */
    public function testClearNumberOfResults() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $dataAccessObject->setNumberOfResults(20);
        $dataAccessObject->clearNumberOfResults();

        $expectedResult = 0;
        $result = $dataAccessObject->getNumberOfResults();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * FirstResult should be an integer
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testSetFirstResultWithNoInteger() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $dataAccessObject->setFirstResult("test");
    }

    /**
     * numberOfResults should be an integer
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testSetNumberOfResultsWithNoInteger() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $dataAccessObject->setNumberOfResults("test");
    }

    /**
     * Simple test to add a field in the array and read it after
     */
    public function testAddFieldInList() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $fieldExemple = array('name' => true);

        $dataAccessObject->addToFieldList(array('name'));

        $readArray = $dataAccessObject->getFieldList();

        $this->assertEquals($fieldExemple, $readArray);
    }

    /**
     * Simple test to add two fields in the array and read it after
     */
    public function testAddTwoFieldsInList() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $fieldExemple = array('name' => true);
        $fieldExemple2 = array('firstname' => true);

        $dataAccessObject->addToFieldList(array('name'));
        $dataAccessObject->addToFieldList(array('firstname'));

        $expectedResult = array_merge($fieldExemple, $fieldExemple2);
        $readArray = $dataAccessObject->getFieldList();

        $this->assertEquals($expectedResult, $readArray);
    }

    /**
     * Remove one field in the fieldList array
     */
    public function testRemoveField() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $dataAccessObject->addToFieldList(array('name'));
        $dataAccessObject->removeFromFieldList(array('name'));

        $readArray = $dataAccessObject->getFieldList();

        $this->assertEquals(array(), $readArray);
    }

    /**
     * Clear the field list
     */
    public function testClearFieldList() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $dataAccessObject->clearFieldList();
    }

    /**
     * fieldList can't be an array in another array
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testAddFieldOnlyStringOrBool() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $fieldExample = array("key" => array(new stdClass()));
        $dataAccessObject->addToFieldList($fieldExample);

    }

    /**
     * Simple test to add a field list in the excludeFieldList array and read it after
     */
    public function testAddExcludeFieldInList() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $excludeFieldExemple = array('password', 'dateOfBirth');

        $dataAccessObject->addToExcludeFieldList($excludeFieldExemple);

        $expectedResult = array('password' => false, 'dateOfBirth' => false);
        $readArray = $dataAccessObject->getExcludeFieldList();

        $this->assertEquals($expectedResult, $readArray);
    }

    /**
     * Simple test to add two fields list in the excludeFieldList array and read it after
     */
    public function testAddTwoExcludeFieldsInList() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $excludeFieldExemple = array('password', 'dateOfBirth');
        $excludeFieldExemple2 = array('firstname', 'age');

        $dataAccessObject->addToExcludeFieldList($excludeFieldExemple);
        $dataAccessObject->addToExcludeFieldList($excludeFieldExemple2);

        $expectedResult = array('password' => false, 'dateOfBirth' => false, 'firstname' => false, 'age' => false);
        $readArray = $dataAccessObject->getExcludeFieldList();

        $this->assertEquals($expectedResult, $readArray);
    }

    /**
     * Remove a field list in the excludeFieldList array
     */
    public function testRemoveExcludeField() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $excludeFieldExemple = array('password', 'firstname');

        $dataAccessObject->addToExcludeFieldList($excludeFieldExemple);

        $dataAccessObject->removeFromExcludeFieldList(array('firstname'));

        $expectedResult = array('password' => false);
        $readArray = $dataAccessObject->getExcludeFieldList();

        $this->assertEquals($expectedResult, $readArray);
    }

    /**
     * Clear the exclude field list
     */
    public function testClearExcludeFieldList() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $excludeFieldExemple = array('password', 'firstname');

        $dataAccessObject->addToExcludeFieldList($excludeFieldExemple);

        $dataAccessObject->clearExcludeFieldList();

        $readArray = $dataAccessObject->getExcludeFieldList();

        $this->assertEquals(array(), $readArray);
    }

    /**
     * excludeFieldList can't be an array in another array
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testAddExcludeFieldOnlyStringOrBool() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $excludeFieldExemple = array("key" => array('toto', 'titi'));
        $dataAccessObject->addToFieldList($excludeFieldExemple);
    }

    /**
     * excludeFieldList can't be an empty array
     * @expectedException \Rubedo\Exceptions\DataAccess
     */
    public function testAddExcludeFieldNotempty() {
        $dataAccessObject = new \Rubedo\Mongo\DataAccess();
        $dataAccessObject->init('items', 'test_db');

        $dataAccessObject->addToExcludeFieldList(array());

    }

}
