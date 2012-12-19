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

Use Rubedo\Collection\Icons;
 
/**
 * Test suite of the collection service :
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */
class ContentsTest extends PHPUnit_Framework_TestCase {
	
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
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		$this->bootstrap->bootstrap();
		
        $this->_mockWorkflowDataAccessService = $this->getMock('Rubedo\\Mongo\\WorkflowDataAccess');
        Rubedo\Services\Manager::setMockService('MongoWorkflowDataAccess', $this->_mockWorkflowDataAccessService);
		
		$this->_mockContentTypesService = $this->getMock('Rubedo\\Collection\\ContentTypes');
        Rubedo\Services\Manager::setMockService('ContentTypes', $this->_mockContentTypesService);
								
		$this->_mockDataIndexService = $this->getMock('Rubedo\\Elastic\\DataIndex');
        Rubedo\Services\Manager::setMockService('ElasticDataIndex', $this->_mockDataIndexService);

        parent::setUp();
    }
	
	/**
	 * Test the verification of the configuration of a field
	 * 
	 * Case with good values, should return true
	 */
	public function testCreateWithGoodConfiguration(){
		$this->_mockContentTypesService->expects($this->once())->method('findById')->will($this->returnValue(array('fields' => array(
			array 	(	'cType' => 'text',
						'config' => array 	(	'name' => 'text',
												'allowBlank' => true,
												'multivalued' => false,
												'minLength' => 2,
												'maxLength' => 5,
											)),
			array 	(	'cType' => 'description',
						'config' => array 	(	'name' => 'description',
												'allowBlank' => true,
												'multivalued' => false,
												'minLength' => 2,
												'maxLength' => 5,
											)),
			array 	(	'cType' => 'body',
						'config' => array 	(	'name' => 'body',
												'allowBlank' => true,
												'multivalued' => false,
												'minLength' => 2,
												'maxLength' => 20,))))));
												
		$this->_mockWorkflowDataAccessService->expects($this->once())->method('create')->will($this->returnValue(array('success' => true,'data'=>array('id'=>'id'))));
		
		$obj = array(	"typeId" => "50c0c8669a199d930f000001",
						"fields" => array(	'text' => 'test',
											'summary'=>'content summary',
											'description' => 'test',
											'body' => '<p>Paragraphe</p>'),
						"text" => "test");
		
		$contents = new \Rubedo\Collection\Contents();
		$result = $contents->create($obj, true);
		
		$this->assertTrue($result['success']);
	}

	/**
	 * Test the verification of the configuration of a field
	 * 
	 * Case with bad values, should return false
	 * 
	 * The field text must be specified
	 */
	public function testCreateWithBadConfigurationAllowblank(){
		$this->_mockContentTypesService->expects($this->once())->method('findById')->will($this->returnValue(array('fields' => array(
			array 	(	'cType' => 'text',
						'config' => array 	(	'name' => 'text',
												'allowBlank' => false,
												'multivalued' => false,
											)),
			array 	(	'cType' => 'summary',
						'config' => array 	(	'name' => 'summary',
												'allowBlank' => true,
												'multivalued' => false,
											)),
			array 	(	'cType' => 'body',
						'config' => array 	(	'name' => 'body',
												'allowBlank' => true,
												'multivalued' => false,))))));
		
		$obj = array(	"typeId" => "50c0c8669a199d930f000001",
						"fields" => array(	'text' => '',
											'summary' => 'test',
											'body' => '<p>Paragraphe</p>'),
						"text" => "test");
		
		$contents = new \Rubedo\Collection\Contents();
		$result = $contents->create($obj, true);
		
		$this->assertFalse($result['success']);
	}
	
	/**
	 * Test the verification of the configuration of a field
	 * 
	 * Case with bad values, should return false
	 * 
	 * The length of the field summary must be greater than 10
	 */
	public function testCreateWithBadConfigurationMinlength(){
		$this->_mockContentTypesService->expects($this->once())->method('findById')->will($this->returnValue(array('fields' => array(
			array 	(	'cType' => 'text',
						'config' => array 	(	'name' => 'text',
												'allowBlank' => true,
												'multivalued' => false,
											)),
			array 	(	'cType' => 'description',
						'config' => array 	(	'name' => 'description',
												'allowBlank' => true,
												'multivalued' => false,
												'minLength' => 10,
											)),
			array 	(	'cType' => 'body',
						'config' => array 	(	'name' => 'body',
												'allowBlank' => true,
												'multivalued' => false,))))));
		
		$obj = array(	"typeId" => "50c0c8669a199d930f000001",
						"fields" => array(	'text' => 'test',
											'summary'=>'summary of test',
											'description' => 'test',
											'body' => '<p>Paragraphe</p>'),
						"text" => "test");
		
		$contents = new \Rubedo\Collection\Contents();
		$result = $contents->create($obj, true);
		$this->assertFalse($result['success']);
	}
	
	/**
	 * Test the verification of the configuration of a field
	 * 
	 * Case with bad values, should return false
	 * 
	 * The length of the field body must be lower than 5
	 */
	public function testCreateWithBadConfigurationMaxlength(){
		$this->_mockContentTypesService->expects($this->once())->method('findById')->will($this->returnValue(array('fields' => array(
			array 	(	'cType' => 'text',
						'config' => array 	(	'name' => 'text',
												'allowBlank' => true,
												'multivalued' => false,
											)),
			array 	(	'cType' => 'summary',
						'config' => array 	(	'name' => 'summary',
												'allowBlank' => true,
												'multivalued' => false,
											)),
			array 	(	'cType' => 'body',
						'config' => array 	(	'name' => 'body',
												'allowBlank' => true,
												'multivalued' => false,
												'maxLength' => 5))))));
		
		$obj = array(	"typeId" => "50c0c8669a199d930f000001",
						"fields" => array(	'text' => 'test',
											'summary' => 'test',
											'body' => '<p>Paragraphe</p>'),
						"text" => "test");
		
		$contents = new \Rubedo\Collection\Contents();
		$result = $contents->create($obj, true);
		$this->assertFalse($result['success']);
	}
	
}
