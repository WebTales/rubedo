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
class WorkflowDataAccessTest extends PHPUnit_Framework_TestCase {
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
		
		static::$phactory->define('fields',array());
			
        // define default values for each user we will create
        static::$phactory->define('item',array('version'=>1), array('live'=>static::$phactory->embedsOne('fields'),'workspace'=>static::$phactory->embedsOne('fields')));
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
    public function testLiveRead() {
        $dataAccessObject = new \Rubedo\Mongo\WorkflowDataAccess();
        $dataAccessObject->init('items', 'test_db');
		$dataAccessObject->setLive();
		
        $items = array();
		
		//create 2 sub Documents, one for live, one for draft and global content
		$fieldsLive = static::$phactory->build('fields',array('label'=>'test live'));
		$fieldsDraft = static::$phactory->build('fields',array('label'=>'test draft'));
        $item = static::$phactory->createWithAssociations('item',array('live'=>$fieldsLive,'workspace'=>$fieldsDraft));
		//run with these documents
		
		
        $item['id'] = (string)$item['_id'];
        $item['version'] = 1;
        unset($item['_id']);
		
		$targetItem = array('id'=>$item['id'],'version'=>$item['version'],'label'=>'test live');
		
        $items[] = $targetItem;

        $readArray = $dataAccessObject->read();

        $this->assertEquals($items, $readArray);

    }
	
	/**
     * test of the read feature
     *
     * Create 3 items through Phactory and read them with the service
     * a version number is added on the fly
     */
    public function testWorkspaceRead() {
        $dataAccessObject = new \Rubedo\Mongo\WorkflowDataAccess();
        $dataAccessObject->init('items', 'test_db');
		$dataAccessObject->setWorkspace();
		
        $items = array();
		
		//create 2 sub Documents, one for live, one for draft and global content
		$fieldsLive = static::$phactory->build('fields',array('label'=>'test live'));
		$fieldsDraft = static::$phactory->build('fields',array('label'=>'test draft'));
        $item = static::$phactory->createWithAssociations('item',array('live'=>$fieldsLive,'workspace'=>$fieldsDraft));
		//run with these documents
		
		
        $item['id'] = (string)$item['_id'];
        $item['version'] = 1;
        unset($item['_id']);
		
		$targetItem = array('id'=>$item['id'],'version'=>$item['version'],'label'=>'test draft');
		
        $items[] = $targetItem;

        $readArray = $dataAccessObject->read();

        $this->assertEquals($items, $readArray);

    }

    /**
     * test of the read feature
     *	Case with a simple filter
     */
    public function testReadWithFilterWithUncommonFields() {
        $dataAccessObject = new \Rubedo\Mongo\WorkflowDataAccess();
        $dataAccessObject->init('items', 'test_db');
		$dataAccessObject->setWorkspace();

        $fieldsLive1 = static::$phactory->build('fields',array('label'=>'test live 1'));
		$fieldsDraft1 = static::$phactory->build('fields',array('label'=>'test draft 1'));
        $item = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive1,'workspace'=>$fieldsDraft1));
		$item['id'] = (string)$item['_id'];
        unset($item['_id']);
		
		$fieldsLive2 = static::$phactory->build('fields',array('label'=>'test live 2'));
		$fieldsDraft2 = static::$phactory->build('fields',array('label'=>'test draft 2'));
		$item2 = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive2,'workspace'=>$fieldsDraft2));
		$item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
		$expectedResult = array(array('id' => $item['id'], 'version' => 1, 'label' => 'test draft 1'));
		
		$filter = array('label' => $item['workspace']['label']);
        $result = $dataAccessObject->addFilter($filter);

        $readArray = $dataAccessObject->read();

        $this->assertEquals($expectedResult, $readArray);
    }

	/**
     * test of the read feature
     *	Case with a simple filter
     */
    public function testReadWithFilterWithCommonFields() {
        $dataAccessObject = new \Rubedo\Mongo\WorkflowDataAccess();
        $dataAccessObject->init('items', 'test_db');
		$dataAccessObject->setWorkspace();

        $fieldsLive1 = static::$phactory->build('fields',array('label'=>'test live 1'));
		$fieldsDraft1 = static::$phactory->build('fields',array('label'=>'test draft 1'));
        $item = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive1,'workspace'=>$fieldsDraft1));
		$item['id'] = (string)$item['_id'];
        unset($item['_id']);
		
		$fieldsLive2 = static::$phactory->build('fields',array('label'=>'test live 2'));
		$fieldsDraft2 = static::$phactory->build('fields',array('label'=>'test draft 2'));
		$item2 = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive2,'workspace'=>$fieldsDraft2));
		$item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
		$expectedResult = array(array('id' => $item2['id'], 'version' => 1, 'label' => 'test draft 2'));
		
		$filter = array('id' => $item2['id']);
        $result = $dataAccessObject->addFilter($filter);

        $readArray = $dataAccessObject->read();

        $this->assertEquals($expectedResult, $readArray);
    }
	
	/**
	 * Try to read with a sort on uncommon fields (id, createUser, LastUpdateTime ...)
	 */
	public function testReadWithSortWithUncommonFields(){
		$dataAccessObject = new \Rubedo\Mongo\WorkflowDataAccess();
        $dataAccessObject->init('items', 'test_db');
		$dataAccessObject->setLive();

        $fieldsLive1 = static::$phactory->build('fields',array('label'=>'b'));
		$fieldsDraft1 = static::$phactory->build('fields',array('label'=>'b'));
        $item = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive1,'workspace'=>$fieldsDraft1));
		$item['id'] = (string)$item['_id'];
        unset($item['_id']);
		
		$fieldsLive2 = static::$phactory->build('fields',array('label'=>'a'));
		$fieldsDraft2 = static::$phactory->build('fields',array('label'=>'a'));
		$item2 = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive2,'workspace'=>$fieldsDraft2));
		$item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
		$expectedResult = array(array('id' => $item2['id'], 'version' => 1, 'label' => 'a'), array('id' => $item['id'], 'version' => 1, 'label' => 'b'));

        $dataAccessObject->addSort(array('label' => 'asc'));

        $readArray = $dataAccessObject->read();

        $this->assertEquals($expectedResult, $readArray);
	}

	/**
	 * Try to read with a sort on common fields
	 */
	public function testReadWithSortWithCommonFields(){
		$dataAccessObject = new \Rubedo\Mongo\WorkflowDataAccess();
        $dataAccessObject->init('items', 'test_db');
		$dataAccessObject->setLive();

        $fieldsLive1 = static::$phactory->build('fields',array('label'=>'b'));
		$fieldsDraft1 = static::$phactory->build('fields',array('label'=>'b'));
        $item = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive1,'workspace'=>$fieldsDraft1));
		$item['id'] = (string)$item['_id'];
        unset($item['_id']);
		
		$fieldsLive2 = static::$phactory->build('fields',array('label'=>'a'));
		$fieldsDraft2 = static::$phactory->build('fields',array('label'=>'a'));
		$item2 = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive2,'workspace'=>$fieldsDraft2));
		$item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
		$expectedResult = array(array('id' => $item['id'], 'version' => 1, 'label' => 'b'), array('id' => $item2['id'], 'version' => 1, 'label' => 'a'));

        $dataAccessObject->addSort(array('id' => 'asc'));

        $readArray = $dataAccessObject->read();

        $this->assertEquals($expectedResult, $readArray);
	}

	/**
	 * Try to read with two sort
	 */
	public function testReadWithTwoSort(){
		$dataAccessObject = new \Rubedo\Mongo\WorkflowDataAccess();
        $dataAccessObject->init('items', 'test_db');
		$dataAccessObject->setLive();

        $fieldsLive1 = static::$phactory->build('fields',array('label'=>'test live', 'name' => 'test'));
		$fieldsDraft1 = static::$phactory->build('fields',array('label'=>'test draft', 'name' => 'test'));
        $item = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive1,'workspace'=>$fieldsDraft1));
		$item['id'] = (string)$item['_id'];
        unset($item['_id']);
		
		$fieldsLive2 = static::$phactory->build('fields',array('label'=>'test live', 'name' => 'test'));
		$fieldsDraft2 = static::$phactory->build('fields',array('label'=>'test draft', 'name' => 'test'));
		$item2 = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive2,'workspace'=>$fieldsDraft2));
		$item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
		$fieldsLive3 = static::$phactory->build('fields',array('label'=>'test live', 'name' => 'test 2'));
		$fieldsDraft3 = static::$phactory->build('fields',array('label'=>'test draft', 'name' => 'test 2'));
		$item3 = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive3,'workspace'=>$fieldsDraft3));
		$item3['id'] = (string)$item3['_id'];
        unset($item3['_id']);
		
		$expectedResult = array(array('id' => $item3['id'], 'version' => 1, 'label' => 'test live', 'name'=>'test 2'), array('id' => $item['id'], 'version' => 1, 'label' => 'test live', 'name'=>'test'), array('id' => $item2['id'], 'version' => 1, 'label' => 'test live', 'name'=>'test'));

		$dataAccessObject->addSort(array('name' => 'desc'));
        $dataAccessObject->addSort(array('id' => 'asc'));

        $readArray = $dataAccessObject->read();

        $this->assertEquals($expectedResult, $readArray);
	}

	/**
     * test if read function works fine with imposed fields
     *
     * The result doesn't contain the password and first name field
     */
    public function testReadWithIncludedField() {
        $dataAccessObject = new \Rubedo\Mongo\WorkflowDataAccess();
        $dataAccessObject->init('items', 'test_db');
		$dataAccessObject->setWorkspace();

        $fieldsLive = static::$phactory->build('fields',array('label'=>'test live'));
		$fieldsDraft = static::$phactory->build('fields',array('label'=>'test draft'));
        $item = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive,'workspace'=>$fieldsDraft));
		$item2 = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive,'workspace'=>$fieldsDraft));
		
		$item['id'] = (string)$item['_id'];
        unset($item['_id']);
		
		$item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
        $includedFields = array('name');
		$sort = array('id' => 'asc');

        $dataAccessObject->addToFieldList($includedFields);
		$dataAccessObject->addSort($sort);
		
		$expectedResult = array(array('id' => $item['id'], 'version' => 1), array('id' => $item2['id'], 'version' => 1));

        $readArray = $dataAccessObject->read();

        $this->assertEquals($expectedResult, $readArray);
    }

	/**
     * test if read function works fine with imposed fields
     *
     * The result doesn't contain the password and first name field
     */
    public function testReadWithTwoIncludedField() {
        $dataAccessObject = new \Rubedo\Mongo\WorkflowDataAccess();
        $dataAccessObject->init('items', 'test_db');
		$dataAccessObject->setWorkspace();

        $fieldsLive = static::$phactory->build('fields',array('label'=>'test live', 'password' => 'test'));
		$fieldsDraft = static::$phactory->build('fields',array('label'=>'test draft', 'password' => 'test'));
        $item = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive,'workspace'=>$fieldsDraft));
		$item2 = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive,'workspace'=>$fieldsDraft));
		
		$item['id'] = (string)$item['_id'];
        unset($item['_id']);
		
		$item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
        $includedFields = array('name', 'label');
		$sort = array('id' => 'asc');

        $dataAccessObject->addToFieldList($includedFields);
		$dataAccessObject->addSort($sort);
		
		$expectedResult = array(array('id' => $item['id'], 'version' => 1, 'label' => 'test draft'), array('id' => $item2['id'], 'version' => 1, 'label' => 'test draft'));

        $readArray = $dataAccessObject->read();

        $this->assertEquals($expectedResult, $readArray);
    }

	/**
     * test if read function works fine with imposed fields
     *
     * The result doesn't contain the password and first name field
     */
    public function testReadWithExcludedField() {
        $dataAccessObject = new \Rubedo\Mongo\WorkflowDataAccess();
        $dataAccessObject->init('items', 'test_db');
		$dataAccessObject->setWorkspace();

        $fieldsLive = static::$phactory->build('fields',array('label'=>'test live'));
		$fieldsDraft = static::$phactory->build('fields',array('label'=>'test draft'));
        $item = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive,'workspace'=>$fieldsDraft));
		$item2 = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive,'workspace'=>$fieldsDraft));
		
		$item['id'] = (string)$item['_id'];
        unset($item['_id']);
		
		$item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
        $excludedFields = array('label');
		$sort = array('id' => 'asc');

        $dataAccessObject->addToExcludeFieldList($excludedFields);
		$dataAccessObject->addSort($sort);
		
		$expectedResult = array(array('id' => $item['id'], 'version' => 1), array('id' => $item2['id'], 'version' => 1));

        $readArray = $dataAccessObject->read();

        $this->assertEquals($expectedResult, $readArray);
    }

	/**
     * test if read function works fine with imposed fields
     *
     * The result doesn't contain the password and first name field
     */
    public function testReadWithTwoExcludedField() {
        $dataAccessObject = new \Rubedo\Mongo\WorkflowDataAccess();
        $dataAccessObject->init('items', 'test_db');
		$dataAccessObject->setWorkspace();

        $fieldsLive = static::$phactory->build('fields',array('label'=>'test live', 'password' => 'test'));
		$fieldsDraft = static::$phactory->build('fields',array('label'=>'test draft', 'password' => 'test'));
        $item = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive,'workspace'=>$fieldsDraft));
		$item2 = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive,'workspace'=>$fieldsDraft));
		
		$item['id'] = (string)$item['_id'];
        unset($item['_id']);
		
		$item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
        $excludedFields = array('label', 'password');
		$sort = array('id' => 'asc');

        $dataAccessObject->addToExcludeFieldList($excludedFields);
		$dataAccessObject->addSort($sort);
		
		$expectedResult = array(array('id' => $item['id'], 'version' => 1), array('id' => $item2['id'], 'version' => 1));

        $readArray = $dataAccessObject->read();

        $this->assertEquals($expectedResult, $readArray);
    }

    /**
     * Test of the create feature
     *
     * Create an item through the service and read it with Phactory
     * Check if a version property add been added
     */
    public function testCreate() {

        $dataAccessObject = new \Rubedo\Mongo\WorkflowDataAccess();
        $dataAccessObject->init('items', 'test_db');
		$dataAccessObject->setWorkspace();

        $item = array('name' => 'test draft', 'label' => 'draft');

        $createArray = $dataAccessObject->create($item, true);
		
        $this->assertTrue($createArray["success"]);
		
        $expectedResult = array('name' => 'test draft', 'label' => 'draft', 'version' => $createArray['data']['version'], 'lastUpdateUser' => null, 'createUser' => null, 'createTime' => null,	'lastUpdateTime' => null, 'id' => $createArray['data']['id']);
		
		$dataBaseResult = static::$phactory->get('items', array('version' => 1));
		$dataBaseResult['id'] = (string)$dataBaseResult['_id'];
		unset($dataBaseResult['_id']);
		
		$dataBaseExpectedResult = array('workspace' => array('name' => 'test draft', 'label' => 'draft'), 'live' => array(),'version' => $dataBaseResult['version'], 'lastUpdateUser' => null, 'createUser' => null, 'createTime' => null, 'lastUpdateTime' => null, 'id' => $dataBaseResult['id']);
		
        $this->assertEquals($expectedResult, $createArray['data']);
		$this->assertEquals($dataBaseExpectedResult, $dataBaseResult);

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
    	$this->initUser();
		$this->initTime();
        $dataAccessObject = new \Rubedo\Mongo\WorkflowDataAccess();
        $dataAccessObject->init('items', 'test_db');
		$dataAccessObject->setWorkspace();
		
        $items = array();
		
		//create 2 sub Documents, one for live, one for draft and global content
		$fieldsLive = static::$phactory->build('fields',array('label'=>'test live'));
		$fieldsDraft = static::$phactory->build('fields',array('label'=>'test draft'));
        $item = static::$phactory->createWithAssociations('item',array('live'=>$fieldsLive,'workspace'=>$fieldsDraft));

        $item['id'] = (string)$item['_id'];
  		$item['workspace']['label'] = 'test draft updated';
		$item['live']['label'] = 'test live';
		unset($item['_id']);
		
		$inputItem = array('id'=>$item['id'],'version'=>$item['version'],'label'=>'test draft updated');
		        
        $updateArray = $dataAccessObject->update($inputItem, true);

		$item['version']++;
		$item['lastUpdateUser']=$this->_fakeUser;
		$item['lastUpdateTime']=$this->_fakeTime;
		
		$inputItem['version']++;
		$inputItem['lastUpdateUser']=$this->_fakeUser;
		$inputItem['lastUpdateTime']=$this->_fakeTime;

        $this->assertTrue($updateArray["success"]);
        $writtenItem = $updateArray["data"];

        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));
        $this->assertEquals(1, count($readItems));
        $readItem = array_pop($readItems);
        $readItem['id'] = (string)$readItem['_id'];
        unset($readItem['_id']);
		
        $this->assertEquals($item, $readItem);
        $this->assertEquals($writtenItem, $inputItem);
    }

   

    /**
     * Test of the Destroy Feature
     *
     * Create items with Phactory
     * Delete one with the service
     * Check if the remaining items are OK and the deleted is no longer in DB
     */
    public function testDestroy() {
        $dataAccessObject = new \Rubedo\Mongo\WorkflowDataAccess();
        $dataAccessObject->init('items', 'test_db');

        $item = static::$phactory->create('item', array('version' => 1, 'name' => 'item 1'));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);
		
		$item2 = static::$phactory->create('item', array('version' => 1, 'name' => 'item 2'));
        $item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);

        $item3 = static::$phactory->create('item', array('version' => 1, 'name' => 'item 3'));
        $itemId = (string)$item3['_id'];
        $item3['id'] = $itemId;
        unset($item3['_id']);

        $updateArray = $dataAccessObject->destroy($item3, true);

        $this->assertTrue($updateArray["success"]);

        $readItems = array_values(iterator_to_array(static::$phactory->getDb()->items->find()));

        $this->assertEquals(2, count($readItems));

        $readItem = static::$phactory->getDb()->items->findOne(array('_id' => new mongoId($itemId)));

        $this->assertNull($readItem);
    }

	/**
	 * Test to publish a content
	 */
	public function testPublish(){
		$dataAccessObject = new \Rubedo\Mongo\WorkflowDataAccess();
        $dataAccessObject->init('items', 'test_db');
		$dataAccessObject->setWorkspace();

        $fieldsLive = static::$phactory->build('fields',array());
		$fieldsDraft = static::$phactory->build('fields',array('label'=>'test draft', 'password' => 'test'));
        $item = static::$phactory->createWithAssociations('item', array('live'=>$fieldsLive,'workspace'=>$fieldsDraft));
		$item['id'] = (string)$item['_id'];
		unset($item['_id']);
		
		$expectedResult = array('live' => array('label' => 'test draft', 'password' => 'test'), 'workspace' => array('label' => 'test draft', 'password' => 'test'));
		
		$result = $dataAccessObject->publish($item['id']);

		$this->assertTrue($result['success']);
	}

}
