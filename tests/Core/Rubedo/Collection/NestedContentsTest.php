<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

Use Rubedo\Collection\NestedContents;

/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class NestedContentsTest extends PHPUnit_Framework_TestCase {

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
        static::$phactory->define('nestedContent', array('version' => 1));

        static::$phactory->define('nestedContents', array(), array(static::$phactory->embedsOne('nestedContent'), static::$phactory->embedsOne('nestedContent')));

        static::$phactory->define('Contents', array('version' => 1), array('nestedContents' => static::$phactory->embedsOne('nestedContents')));
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
        testBootstrap();
        Rubedo\Mongo\DataAccess::setDefaultDb('test_db');

        $this->_mockCurrentUser = $this->getMock('Rubedo\\User\\CurrentUser');
        Rubedo\Services\Manager::setMockService('CurrentUser', $this->_mockCurrentUser);

        $this->_mockCurrentTime = $this->getMock('Rubedo\\Time\\CurrentTime');
        Rubedo\Services\Manager::setMockService('CurrentTime', $this->_mockCurrentTime);

        parent::setUp();
    }

    /**
     * Test getList with nested contents
     */
    public function testGetList() {
        $collection = new NestedContents();

        $content1 = static::$phactory->build('nestedContent', array('id' => (string)new \MongoId(), 'label' => 'test1'));
        $content2 = static::$phactory->build('nestedContent', array('id' => (string)new \MongoId(), 'label' => 'test2'));
        $contents = static::$phactory->build('nestedContents', array($content1, $content2));
        $item = static::$phactory->createWithAssociations('Contents', array('nestedContents' => $contents));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);
		
		$item2 = static::$phactory->createWithAssociations('Contents', array('nestedContents' => $contents));
		$item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
		$expectedResult = $contents; 
        $result = $collection->getList($item['id']);

        $this->assertEquals($result, $expectedResult);
    }
	
	/**
     * Test getList with nested contents
	 * 
	 * @todo Fix fail in findById
     */
    /*public function testFindById() {
        $collection = new NestedContents();

        $content1 = static::$phactory->build('nestedContent', array('id' => (string)new \MongoId(), 'label' => 'test1'));
        $content2 = static::$phactory->build('nestedContent', array('id' => (string)new \MongoId(), 'label' => 'test2'));
        $contents = static::$phactory->build('nestedContents', array($content1, $content2));
        $item = static::$phactory->createWithAssociations('Contents', array('nestedContents' => $contents));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);
		
		$expectedResult = $content1; 
        $result = $collection->findById($item['id'], $content1['id']);

        $this->assertEquals($result, $expectedResult);
    }*/
    
    /**
	 * Test the creation of a nested content in an existing content
	 */
	public function testCreate(){
		$collection = new NestedContents();
		
		$content1 = static::$phactory->build('nestedContent', array('id' => (string)new \MongoId(), 'label' => 'test1'));
        $content2 = static::$phactory->build('nestedContent', array('id' => (string)new \MongoId(), 'label' => 'test2'));
        $contents = static::$phactory->build('nestedContents', array($content1, $content2));
        $item = static::$phactory->createWithAssociations('Contents', array('nestedContents' => $contents));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);
		
		$item2 = static::$phactory->createWithAssociations('Contents', array('nestedContents' => $contents));
		$item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
		$nestedContent = array('version' => 1, 'label' => 'test');
		
		$result = $collection->create($item['id'], $nestedContent);
		
		$this->assertTrue($result['success']);
	}
	
	/**
	 * Test the update of an existing nested content
	 */
	public function testUpdate(){
		$collection = new NestedContents();
		
		$content1 = static::$phactory->build('nestedContent', array('id' => (string)new \MongoId(), 'label' => 'test1'));
        $content2 = static::$phactory->build('nestedContent', array('id' => (string)new \MongoId(), 'label' => 'test2'));
        $contents = static::$phactory->build('nestedContents', array($content1, $content2));
        $item = static::$phactory->createWithAssociations('Contents', array('nestedContents' => $contents));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);
		
		$item2 = static::$phactory->createWithAssociations('Contents', array('nestedContents' => $contents));
		$item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
		$content1['label'] = 'test1 updated';
		
		$result = $collection->update($item['id'], $content1);
		
		$this->assertTrue($result['success']);
	}
	
	/**
	 * Test the deletion of a nested content
	 */
	public function testDestroy(){
		$collection = new NestedContents();
		
		$content1 = static::$phactory->build('nestedContent', array('id' => (string)new \MongoId(), 'label' => 'test1'));
        $content2 = static::$phactory->build('nestedContent', array('id' => (string)new \MongoId(), 'label' => 'test2'));
        $contents = static::$phactory->build('nestedContents', array($content1, $content2));
        $item = static::$phactory->createWithAssociations('Contents', array('nestedContents' => $contents));
        $item['id'] = (string)$item['_id'];
        unset($item['_id']);
		
		$item2 = static::$phactory->createWithAssociations('Contents', array('nestedContents' => $contents));
		$item2['id'] = (string)$item2['_id'];
        unset($item2['_id']);
		
		$result = $collection->destroy($item['id'], $content1);
		
		$this->assertTrue($result['success']);
	}

}
