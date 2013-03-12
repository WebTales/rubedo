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



Use Rubedo\Collection\WorkflowAbstractCollection;

require_once('Rubedo/Interfaces/Collection/IAbstractCollection.php');
require_once('Rubedo/Interfaces/Collection/IWorkflowAbstractCollection.php');
require_once('Rubedo/Collection/WorkflowAbstractCollection.php');

class testWorkflowCollection extends WorkflowAbstractCollection {
    public function __construct() {
        $this->_collectionName = 'test';
        parent::__construct();
    }

}
 
class WorkflowAbstractCollectionTest extends PHPUnit_Framework_TestCase {
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
        $this->_mockDataAccessService = $this->getMock('Rubedo\\Mongo\\DataAccess');
        Rubedo\Services\Manager::setMockService('MongoDataAccess', $this->_mockDataAccessService);
		$this->_mockWorkflowDataAccessService = $this->getMock('Rubedo\\Mongo\\WorkflowDataAccess');
        Rubedo\Services\Manager::setMockService('MongoWorkflowDataAccess', $this->_mockWorkflowDataAccessService);
		

        parent::setUp();
    }

	/*
	 * Test if FindById call method setWorkspace once when live is false
	 */
		public function testNormalFindByIdWithLiveToFalse(){
		$this->_mockWorkflowDataAccessService->expects($this->never())->method('setLive');
		$this->_mockWorkflowDataAccessService->expects($this->once())->method('setWorkspace');
		$this->_mockWorkflowDataAccessService->expects($this->once())->method('findById');
		
		$contentId="id";
		$collection = new testWorkflowCollection();
		$collection->findById($contentId,false);
		
	}

	public function testNormalCreate()
	{
		$createReturn['success']=true;
		$createReturn['data']['status']='published';
		$createReturn['data']['id']='testId';
		$publishReturn['success']=true;
		$this->_mockWorkflowDataAccessService->expects($this->once())->method('create')->will($this->returnValue($createReturn));
			$this->_mockWorkflowDataAccessService->expects($this->once())->method('setWorkspace');
			$this->_mockWorkflowDataAccessService->expects($this->once())->method('publish')->will($this->returnValue($publishReturn));
		
		$obj=array("value"=>"test");
		$collection = new testWorkflowCollection();
		$result=$collection->create($obj);
		$this->assertTrue($result['success']);
	}

	/*
	 * test if readChild function works fine
	 */
	public function testNormalReadChild(){
		$this->_mockWorkflowDataAccessService->expects($this->once())->method('setLive');
		$this->_mockWorkflowDataAccessService->expects($this->never())->method('setWorkspace');
		
		$parentId="parentId";
		$collection = new testWorkflowCollection();
		$result =  $collection->readChild($parentId);
	}
	/*
	 * test if readChild function works fine when live param is false
	 */
	public function testNormalReadChildWithLiveFalse(){
		$this->_mockWorkflowDataAccessService->expects($this->never())->method('setLive');
		$this->_mockWorkflowDataAccessService->expects($this->once())->method('setWorkspace');
		
		$parentId="parentId";
		$collection = new testWorkflowCollection();
		$result =  $collection->readChild($parentId, null,null,false);
	}
}

	