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
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		$this->bootstrap->bootstrap();
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

	