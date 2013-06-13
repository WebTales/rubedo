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
        testBootstrap();
		
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
		$this->_mockContentTypesService->expects($this->any())->method('findById')->will($this->returnValue(array('fields' => array(
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
												
		$this->_mockWorkflowDataAccessService->expects($this->once())->method('create')->will($this->returnValue(array('success' => true,'data'=>array('id'=>'id', 'status' => 'draft'))));
		
		$obj = array(	"typeId" => "50c0c8669a199d930f000001",
						"fields" => array(	'text' => 'test',
											'summary'=>'content summary',
											'description' => 'test',
											'body' => '<p>Paragraphe</p>'),
						"text" => "test",
						"target" => array("test"));
		
		$contents = new \Rubedo\Collection\Contents();
		$result = $contents->create($obj);
		
		$this->assertTrue($result['success']);
	}

	/**
	 * Test the verification of the configuration of a field
	 * 
	 * Case with bad values, should return false
	 * 
	 * The field text must be specified
	 */
	public function testCreateWithBadConfigurationForAllowblankOnTextField(){
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
		
		$obj = array(	"typeId" => "123456789",
						"fields" => array(	'text' => 'test',
											'summary' => 'test',
											'body' => '<p>Paragraphe</p>'),
						"text" => "",
						"target" => array("test"));
						
		$this->_mockWorkflowDataAccessService->expects($this->any())->method('create')->will($this->returnValue(array('success' => true,'data'=>array('id'=>'id'))));
		
		$contents = new \Rubedo\Collection\Contents();
		$result = $contents->create($obj);
		
		$this->assertFalse($result['success']);
	}

	/**
	 * Test the verification of the configuration of a field
	 * 
	 * Case with bad values, should return false
	 * 
	 * The field body must be specified
	 */
	public function testCreateWithBadConfigurationForAllowblankOnBodyField(){
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
												'allowBlank' => false,
												'multivalued' => false,))))));
		
		$obj = array(	"typeId" => "123456789",
						"fields" => array(	'text' => 'test',
											'summary' => 'test',
											'body' => ''),
						"text" => "test",
						"target" => array("test"));
						
		$this->_mockWorkflowDataAccessService->expects($this->any())->method('create')->will($this->returnValue(array('success' => true,'data'=>array('id'=>'id'))));
		
		$contents = new \Rubedo\Collection\Contents();
		$result = $contents->create($obj);

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
						"text" => "test",
						"target" => array("test"));
		
		$contents = new \Rubedo\Collection\Contents();
		$result = $contents->create($obj);
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
						"text" => "test",
						"target" => array("test"));
		
		$contents = new \Rubedo\Collection\Contents();
		$result = $contents->create($obj);
		$this->assertFalse($result['success']);
	}
	
	/**
	 * Test the verification of the configuration of a field
	 * 
	 * Case with good values, should return true
	 * 
	 * The field body must contain only alpha character
	 */
	public function testUpdateWithGoodConfigurationForAlphaVType(){
	    $this->_mockCurrentUserService = $this->getMock('Rubedo\\User\\CurrentUser');
	    Rubedo\Services\Manager::setMockService('CurrentUser', $this->_mockCurrentUserService);
	    
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
												'multivalued' => false,
												'vtype' => 'alpha'))
		    ), "workspaces" => array("test", "test2")
		)));
		
		$obj = array(	"id" => "test",
						"typeId" => "50c0c8669a199d930f000001",
						"fields" => array(	'text' => 'test',
											'summary' => 'test',
											'body' => 'Paragraphe'),
						"text" => "test",
						"target" => "test",
						"writeWorkspace" => "test2",
		);
		
		$this->_mockWorkflowDataAccessService->expects($this->any())->method('update')->will($this->returnValue(array('success' => true,'data'=>array('status'=>'test', "id" => "id"))));
		
		$this->_mockCurrentUserService->expects($this->any())->method("getWriteWorkspaces")->will($this->returnValue(array("test2")));
		$this->_mockCurrentUserService->expects($this->any())->method("getReadWorkspaces")->will($this->returnValue(array("test2", "test")));
		
		$contents = new \Rubedo\Collection\Contents();
		\Rubedo\Collection\AbstractCollection::disableUserFilter(false);
		$result = $contents->update($obj);
		\Rubedo\Collection\AbstractCollection::disableUserFilter(true);
		
		$this->assertTrue($result['success']);
	}
	
	/**
	 * Test the read only flag
	 * 
	 * @expectedException \Rubedo\Exceptions\Access
	 */
	public function testUpdateInReadOnly(){
	    \Rubedo\Collection\AbstractCollection::disableUserFilter(false);
	    
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
	                'multivalued' => false,
	                'vtype' => 'alpha'))))));
	
	    $obj = array(	"id" => "test",
	        "typeId" => "50c0c8669a199d930f000001",
	        "fields" => array(	'text' => 'test',
	            'summary' => 'test',
	            'body' => 'Paragraphe'),
	        "text" => "test",
	        "target" => array("test"),
	        "writeWorkspace" => "test");
	
	    $this->_mockWorkflowDataAccessService->expects($this->any())->method('findById')->will($this->returnValue(array("readOnly" => true, "status" => "test", "typeId" => "test")));
	
	    $this->_mockWorkflowDataAccessService->expects($this->any())->method('update')->will($this->returnValue(array('success' => true,'data'=>array('status'=>'test', "id" => "id"))));
	
	    $contents = new \Rubedo\Collection\Contents();
	    $result = $contents->update($obj);
	
	    $this->assertTrue($result['success']);
	    
	    \Rubedo\Collection\AbstractCollection::disableUserFilter(true);
	}

	/**
	 * Test the verification of the configuration of a field
	 * 
	 * Case with bad values, should return false
	 * 
	 * The body field contain an url and only allow email
	 */
	public function testUpdateWithBadConfigurationForEmailVType(){
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
												'multivalued' => false,
												'vtype' => 'email'))))));
		
		$obj = array(	"id" => "test",
						"typeId" => "50c0c8669a199d930f000001",
						"fields" => array(	'text' => 'test',
											'summary' => 'test',
											'body' => 'http://test.fr'),
						"text" => "test",
						"target" => array("test"),
						"writeWorkspace" => "test");
		
		$this->_mockWorkflowDataAccessService->expects($this->any())->method('update')->will($this->returnValue(array('success' => true,'data'=>array('status'=>'test', "id" => "id"))));
		
		$contents = new \Rubedo\Collection\Contents();
		$result = $contents->update($obj);
		
		$this->assertFalse($result['success']);
	}
	
	/**
	 * Test if the destroy method works fine
	 */
	public function testDestroy() {
		$this->_mockWorkflowDataAccessService->expects($this->once())->method('destroy')->will($this->returnValue(array("success" => true)));
		$this->_mockDataIndexService->expects($this->once())->method('deleteContent');
		
		$obj = array(	"id" => "test",
						"typeId" => "50c0c8669a199d930f000001",
						"fields" => array(	'text' => 'test',
											'summary' => 'test',
											'body' => 'http://test.fr'),
						"text" => "test");
						
		$contents = new \Rubedo\Collection\Contents();
		\Rubedo\Collection\AbstractCollection::disableUserFilter(false);
		$result = $contents->destroy($obj);
		\Rubedo\Collection\AbstractCollection::disableUserFilter(true);
		
		$this->assertTrue($result['success']);
	}
	
	/**
	 * Test destroy on a read only content
	 * 
	 * @expectedException \Rubedo\Exceptions\Access
	 */
	public function testDestroyInReadOnly() {
	    $this->_mockWorkflowDataAccessService->expects($this->any())->method('findById')->will($this->returnValue(array("readOnly" => true, "typeId" => "test")));
	
	    $obj = array(	"id" => "test",
	        "typeId" => "50c0c8669a199d930f000001",
	        "fields" => array(	'text' => 'test',
	            'summary' => 'test',
	            'body' => 'http://test.fr'),
	        "text" => "test");
	
	    $contents = new \Rubedo\Collection\Contents();
	    \Rubedo\Collection\AbstractCollection::disableUserFilter(false);
	    $result = $contents->destroy($obj);
	    \Rubedo\Collection\AbstractCollection::disableUserFilter(true);
	
	    $this->assertTrue($result['success']);
	}
	
	/**
	 * Check if rights are disabled
	 */
	public function testAnyFunctionWithoutRights() {
	    \Rubedo\Collection\AbstractCollection::disableUserFilter(false);
	    
	    $contents = new \Rubedo\Collection\Contents();
	    $contents->getList();
	    
	    $this->assertFalse(\Rubedo\Collection\AbstractCollection::isUserFilterDisabled());
	}
	
	/**
	 * Check if we are in front end mode
	 */
	public function testAnyFunctionInFrontEndMode() {
	    $contents = new \Rubedo\Collection\Contents();
	    $contents->setIsFrontEnd(true);
	    
	    $contents->getList();
	    
	    $this->assertTrue($contents->getIsFrontEnd());
	}
	
	/**
	 * Check if we are in front end mode with draft status on contents
	 */
	public function testAnyFunctionInFrontEndModeAndDraftStatus() {
	    $contents = new \Rubedo\Collection\Contents();
	    $contents->setIsFrontEnd(true);
	    \Zend_Registry::set('draft', true);
	     
	    $contents->getList();
	     
	    $this->assertTrue($contents->getIsFrontEnd());
	    $this->assertTrue($contents->getIsFrontEnd(\Zend_Registry::get('draft')));
	    
	    \Zend_Registry::getInstance()->offsetUnset("draft");
	}
	
	/**
	 * Test getOnlineList function
	 */
	public function testGetOnlineList() {
	    $this->_mockWorkflowDataAccessService->expects($this->once())->method('read')->will($this->returnValue(array('data' => array("test"), "count" => 1)));
	    
	    $contents = new \Rubedo\Collection\Contents();
	    $result = $contents->getOnlineList();
	    
	    $this->assertTrue(is_array($result["data"]));
	}
	
	/**
	 * Test getOnlineList function with draft status
	 */
	public function testGetOnlineListWithDraftStatus() {
	    $this->_mockWorkflowDataAccessService->expects($this->once())->method('read')->will($this->returnValue(array('data' => array("test"), "count" => 1)));
	    \Zend_Registry::set('draft', true);
	    
	    $contents = new \Rubedo\Collection\Contents();
	    $result = $contents->getOnlineList();
	     
	    $this->assertTrue(is_array($result["data"]));
	    \Zend_Registry::getInstance()->offsetUnset("draft");
	}
	
	/**
	 * Try to unset a term
	 */
	public function testUnsetTerms() {
	    $this->_mockWorkflowDataAccessService->expects($this->once())->method('customUpdate')->will($this->returnValue(array('success' => true)));
	    
	    $contents = new \Rubedo\Collection\Contents();
	    $result = $contents->unsetTerms("test", "test");
	    
	    $this->assertTrue($result["success"]);
	}
	
	/**
	 * Try to unset a term without a term id
	 * 
	 * @expectedException \Rubedo\Exceptions\Server
	 */
	public function testUnsetTermsWithoutTermId() {
	    $contents = new \Rubedo\Collection\Contents();
	    $result = $contents->unsetTerms("test", null);
	}
	
}
