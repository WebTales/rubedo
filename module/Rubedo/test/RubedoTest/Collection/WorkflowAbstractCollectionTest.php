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

namespace RubedoTest\Collection;

use Rubedo\Collection\WorkflowAbstractCollection;
use Rubedo\Services\Manager;

class testWorkflowCollection extends WorkflowAbstractCollection
{
    public function __construct()
    {
        $this->_collectionName = 'test';
        parent::__construct();
    }

}

class WorkflowAbstractCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Rubedo\Mongo\DataAccess
     */
    private $mockDataAccessService;
    /**
     * @var \Rubedo\Mongo\WorkflowDataAccess
     */
    private $mockWorkflowDataAccessService;

    /**
     * clear the DB of the previous test data
     */
    public function tearDown()
    {
        Manager::resetMocks();
    }

    /**
     * init the Zend Application for tests
     */
    public function setUp()
    {
        $this->mockDataAccessService = $this->getMock('Rubedo\Mongo\DataAccess');
        Manager::setMockService('MongoDataAccess', $this->mockDataAccessService);
        $this->mockWorkflowDataAccessService = $this->getMock('Rubedo\Mongo\WorkflowDataAccess');
        Manager::setMockService('MongoWorkflowDataAccess', $this->mockWorkflowDataAccessService);


        parent::setUp();
    }

    /**
     * Test if FindById call method setWorkspace once when live is false
     */
    public function testNormalFindByIdWithLiveToFalse()
    {
        $this->mockWorkflowDataAccessService->expects($this->never())->method('setLive');
        $this->mockWorkflowDataAccessService->expects($this->once())->method('setWorkspace');
        $this->mockWorkflowDataAccessService->expects($this->once())->method('findById');

        $contentId = "id";
        $collection = new testWorkflowCollection();
        $collection->findById($contentId, false);

    }

    public function testNormalCreate()
    {
        $createReturn['success'] = true;
        $createReturn['data']['status'] = 'published';
        $createReturn['data']['id'] = 'testId';
        $publishReturn['success'] = true;
        $this->mockWorkflowDataAccessService->expects($this->once())->method('create')->will($this->returnValue($createReturn));
        $this->mockWorkflowDataAccessService->expects($this->once())->method('setWorkspace');
        $this->mockWorkflowDataAccessService->expects($this->once())->method('publish')->will($this->returnValue($publishReturn));

        $obj = array("value" => "test");
        $collection = new testWorkflowCollection();
        $result = $collection->create($obj);
        $this->assertTrue($result['success']);
    }

    /**
     * test if readChild function works fine
     */
    public function testNormalReadChild()
    {
        $this->mockWorkflowDataAccessService->expects($this->once())->method('setLive');
        $this->mockWorkflowDataAccessService->expects($this->never())->method('setWorkspace');

        $parentId = "parentId";
        $collection = new testWorkflowCollection();
        $collection->readChild($parentId);
    }

    /**
     * test if readChild function works fine when live param is false
     */
    public function testNormalReadChildWithLiveFalse()
    {
        $this->mockWorkflowDataAccessService->expects($this->never())->method('setLive');
        $this->mockWorkflowDataAccessService->expects($this->once())->method('setWorkspace');

        $parentId = "parentId";
        $collection = new testWorkflowCollection();
        $collection->readChild($parentId, null, null, false);
    }
}

	