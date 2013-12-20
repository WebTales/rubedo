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
 *
 * @author jbourdin
 * @category Rubedo-Test
 * @package Rubedo-Test
 */

namespace RubedoTest\Collection;

use Rubedo\Collection\AbstractCollection;
use Rubedo\Collection\Contents;
use Rubedo\Content\Context;
use Rubedo\Services\Manager;

class ContentsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Rubedo\Mongo\WorkflowDataAccess
     */
    private $mockWorkflowDataAccessService;

    /**
     * @var \Rubedo\Collection\ContentTypes
     */
    private $mockContentTypesService;

    /**
     * @var \Rubedo\Elastic\DataIndex
     */
    private $mockDataIndexService;

    /**
     * @var \Rubedo\User\CurrentUser
     */
    private $mockCurrentUserService;

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
        $this->mockWorkflowDataAccessService = $this->getMock('Rubedo\Mongo\WorkflowDataAccess');
        Manager::setMockService('MongoWorkflowDataAccess', $this->mockWorkflowDataAccessService);

        $this->mockContentTypesService = $this->getMock('Rubedo\Collection\ContentTypes');
        Manager::setMockService('ContentTypes', $this->mockContentTypesService);

        $this->mockDataIndexService = $this->getMock('Rubedo\Elastic\DataIndex');
        Manager::setMockService('ElasticDataIndex', $this->mockDataIndexService);

        parent::setUp();
    }

    /**
     * Test the verification of the configuration of a field
     *
     * Case with good values, should return true
     */
    public function testCreateWithGoodConfiguration()
    {
        $this->mockContentTypesService->expects($this->any())
            ->method('findById')
            ->will($this->returnValue(array(
                'fields' => array(
                    array(
                        'cType' => 'text',
                        'config' => array(
                            'name' => 'text',
                            'allowBlank' => true,
                            'multivalued' => false,
                            'minLength' => 2,
                            'maxLength' => 5
                        )
                    ),
                    array(
                        'cType' => 'description',
                        'config' => array(
                            'name' => 'description',
                            'allowBlank' => true,
                            'multivalued' => false,
                            'minLength' => 2,
                            'maxLength' => 5
                        )
                    ),
                    array(
                        'cType' => 'body',
                        'config' => array(
                            'name' => 'body',
                            'allowBlank' => true,
                            'multivalued' => false,
                            'minLength' => 2,
                            'maxLength' => 20
                        )
                    )
                )
            )));

        $this->mockWorkflowDataAccessService->expects($this->once())
            ->method('create')
            ->will($this->returnValue(array(
                'success' => true,
                'data' => array(
                    'id' => 'id',
                    'status' => 'draft'
                )
            )));

        $obj = array(
            'typeId' => '50c0c8669a199d930f000001',
            'fields' => array(
                'text' => 'test',
                'summary' => 'content summary',
                'description' => 'test',
                'body' => '<p>Paragraphe</p>'
            ),
            "text" => "test",
            "nativeLanguage" => "en",
            "i18n" => array(
                "en" => array(
                    "fields" => array(
                        'text' => 'test',
                        'summary' => 'content summary',
                        'description' => 'test',
                        'body' => '<p>Paragraphe</p>'
                    )
                )
            ),
            "target" => array(
                "test"
            )
        );

        $contents = new Contents();
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
    public function testCreateWithBadConfigurationForAllowBlankOnTextField()
    {
        $this->mockContentTypesService->expects($this->any())
            ->method('findById')
            ->will($this->returnValue(array(
                'fields' => array(
                    array(
                        'cType' => 'text',
                        'config' => array(
                            'name' => 'text',
                            'allowBlank' => false,
                            'multivalued' => false
                        )
                    ),
                    array(
                        'cType' => 'summary',
                        'config' => array(
                            'name' => 'summary',
                            'allowBlank' => true,
                            'multivalued' => false
                        )
                    ),
                    array(
                        'cType' => 'body',
                        'config' => array(
                            'name' => 'body',
                            'allowBlank' => true,
                            'multivalued' => false
                        )
                    )
                )
            )));

        $obj = array(
            'typeId' => '123456789',
            'fields' => array(
                'text' => 'test',
                'summary' => 'test',
                'body' => '<p>Paragraphe</p>'
            ),
            'text' => '',
            'nativeLanguage' => 'en',
            'i18n' => array(
                'en' => array(
                    'fields' => array(
                        'text' => '',
                        'summary' => 'content summary',
                        'description' => 'test',
                        'body' => '<p>Paragraphe</p>'
                    )
                )
            ),
            'target' => array(
                'test'
            )
        );

        $this->mockWorkflowDataAccessService->expects($this->any())
            ->method('create')
            ->will($this->returnValue(array(
                'success' => true,
                'data' => array(
                    'id' => 'id'
                )
            )));

        $contents = new Contents();
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
    public function testCreateWithBadConfigurationForAllowBlankOnBodyField()
    {
        $this->mockContentTypesService->expects($this->any())
            ->method('findById')
            ->will($this->returnValue(array(
                'fields' => array(
                    array(
                        'cType' => 'text',
                        'config' => array(
                            'name' => 'text',
                            'allowBlank' => false,
                            'multivalued' => false
                        )
                    ),
                    array(
                        'cType' => 'summary',
                        'config' => array(
                            'name' => 'summary',
                            'allowBlank' => true,
                            'multivalued' => false
                        )
                    ),
                    array(
                        'cType' => 'body',
                        'config' => array(
                            'name' => 'body',
                            'allowBlank' => false,
                            'multivalued' => false
                        )
                    )
                )
            )));
        $obj = array(
            "typeId" => "123456789",
            "fields" => array(
                'text' => 'test',
                'summary' => 'test',
                'body' => ''
            ),
            "text" => "test",
            "nativeLanguage" => "en",
            "i18n" => array(
                "en" => array(
                    "fields" => array(
                        'text' => 'test',
                        'summary' => 'content summary',
                        'description' => 'test',
                        'body' => ''
                    )
                )
            ),
            "target" => array(
                "test"
            )
        );

        $this->mockWorkflowDataAccessService->expects($this->any())
            ->method('create')
            ->will($this->returnValue(array(
                'success' => true,
                'data' => array(
                    'id' => 'id'
                )
            )));

        $contents = new Contents();
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
    public function testCreateWithBadConfigurationMinlength()
    {
        $this->mockContentTypesService->expects($this->any())
            ->method('findById')
            ->will($this->returnValue(array(
                'fields' => array(
                    array(
                        'cType' => 'text',
                        'config' => array(
                            'name' => 'text',
                            'allowBlank' => true,
                            'multivalued' => false
                        )
                    ),
                    array(
                        'cType' => 'description',
                        'config' => array(
                            'name' => 'description',
                            'allowBlank' => true,
                            'multivalued' => false,
                            'minLength' => 10
                        )
                    ),
                    array(
                        'cType' => 'body',
                        'config' => array(
                            'name' => 'body',
                            'allowBlank' => true,
                            'multivalued' => false
                        )
                    )
                )
            )));

        $obj = array(
            "typeId" => "50c0c8669a199d930f000001",
            "fields" => array(
                'text' => 'test',
                'summary' => 'summary of test',
                'description' => 'test',
                'body' => '<p>Paragraphe</p>'
            ),
            "text" => "test",
            "nativeLanguage" => "en",
            "i18n" => array(
                "en" => array(
                    "fields" => array(
                        'text' => 'test',
                        'summary' => 'summary of test',
                        'description' => 'test',
                        'body' => '<p>Paragraphe</p>'
                    )
                )
            ),
            "target" => array(
                "test"
            )
        );

        $contents = new Contents();
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
    public function testCreateWithBadConfigurationMaxlength()
    {
        $this->mockContentTypesService->expects($this->any())
            ->method('findById')
            ->will($this->returnValue(array(
                'fields' => array(
                    array(
                        'cType' => 'text',
                        'config' => array(
                            'name' => 'text',
                            'allowBlank' => true,
                            'multivalued' => false
                        )
                    ),
                    array(
                        'cType' => 'summary',
                        'config' => array(
                            'name' => 'summary',
                            'allowBlank' => true,
                            'multivalued' => false
                        )
                    ),
                    array(
                        'cType' => 'body',
                        'config' => array(
                            'name' => 'body',
                            'allowBlank' => true,
                            'multivalued' => false,
                            'maxLength' => 5
                        )
                    )
                )
            )));

        $obj = array(
            "typeId" => "50c0c8669a199d930f000001",
            "fields" => array(
                'text' => 'test',
                'summary' => 'test',
                'body' => '<p>Paragraphe</p>'
            ),
            "text" => "test",
            "nativeLanguage" => "en",
            "i18n" => array(
                "en" => array(
                    "fields" => array(
                        'text' => 'test',
                        'summary' => 'summary of test',
                        'description' => 'test',
                        'body' => '<p>Paragraphe</p>'
                    )
                )
            ),
            "target" => array(
                "test"
            )
        );

        $contents = new Contents();
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
    public function testUpdateWithGoodConfigurationForAlphaVType()
    {
        $this->mockCurrentUserService = $this->getMock('Rubedo\User\CurrentUser');
        Manager::setMockService('CurrentUser', $this->mockCurrentUserService);

        $this->mockContentTypesService->expects($this->any())
            ->method('findById')
            ->will($this->returnValue(array(
                'fields' => array(
                    array(
                        'cType' => 'text',
                        'config' => array(
                            'name' => 'text',
                            'allowBlank' => false,
                            'multivalued' => false
                        )
                    ),
                    array(
                        'cType' => 'summary',
                        'config' => array(
                            'name' => 'summary',
                            'allowBlank' => true,
                            'multivalued' => false
                        )
                    ),
                    array(
                        'cType' => 'body',
                        'config' => array(
                            'name' => 'body',
                            'allowBlank' => true,
                            'multivalued' => false,
                            'vtype' => 'alpha'
                        )
                    )
                ),
                "workspaces" => array(
                    "test",
                    "test2"
                )
            )));

        $this->mockContentTypesService->expects($this->once())
            ->method('getLocalizableFieldForCType')
            ->will($this->returnValue(array()));

        $obj = array(
            "id" => "test",
            "typeId" => "50c0c8669a199d930f000001",
            "fields" => array(
                'text' => 'test',
                'summary' => 'test',
                'body' => 'Paragraphe'
            ),
            "text" => "test",
            "nativeLanguage" => "en",
            "i18n" => array(
                "en" => array(
                    "fields" => array(
                        'text' => 'test',
                        'summary' => 'test',
                        'body' => 'Paragraphe'
                    )
                )
            ),
            "target" => "test",
            "writeWorkspace" => "test2"
        );

        $this->mockWorkflowDataAccessService->expects($this->any())
            ->method('update')
            ->will($this->returnValue(array(
                'success' => true,
                'data' => array(
                    'status' => 'test',
                    "typeId" => "50c0c8669a199d930f000001",
                    "id" => "id"
                )
            )));

        $this->mockCurrentUserService->expects($this->any())
            ->method("getWriteWorkspaces")
            ->will($this->returnValue(array(
                "test2"
            )));
        $this->mockCurrentUserService->expects($this->any())
            ->method("getReadWorkspaces")
            ->will($this->returnValue(array(
                "test2",
                "test"
            )));

        $contents = new Contents();
        AbstractCollection::disableUserFilter(false);
        $result = $contents->update($obj);
        AbstractCollection::disableUserFilter(true);

        $this->assertTrue($result['success']);
    }

    /**
     * Test the read only flag
     *
     * @expectedException \Rubedo\Exceptions\Access
     */
    public function testUpdateInReadOnly()
    {
        AbstractCollection::disableUserFilter(false);

        $this->mockContentTypesService->expects($this->any())
            ->method('findById')
            ->will($this->returnValue(array(
                'fields' => array(
                    array(
                        'cType' => 'text',
                        'config' => array(
                            'name' => 'text',
                            'allowBlank' => false,
                            'multivalued' => false
                        )
                    ),
                    array(
                        'cType' => 'summary',
                        'config' => array(
                            'name' => 'summary',
                            'allowBlank' => true,
                            'multivalued' => false
                        )
                    ),
                    array(
                        'cType' => 'body',
                        'config' => array(
                            'name' => 'body',
                            'allowBlank' => true,
                            'multivalued' => false,
                            'vtype' => 'alpha'
                        )
                    )
                )
            )));

        $obj = array(
            "id" => "test",
            "typeId" => "50c0c8669a199d930f000001",
            "fields" => array(
                'text' => 'test',
                'summary' => 'test',
                'body' => 'Paragraphe'
            ),
            "text" => "test",
            "target" => array(
                "test"
            ),
            "writeWorkspace" => "test"
        );

        $this->mockWorkflowDataAccessService->expects($this->any())
            ->method('findById')
            ->will($this->returnValue(array(
                "readOnly" => true,
                "status" => "test",
                "typeId" => "test"
            )));

        $this->mockWorkflowDataAccessService->expects($this->any())
            ->method('update')
            ->will($this->returnValue(array(
                'success' => true,
                'data' => array(
                    'status' => 'test',
                    "id" => "id"
                )
            )));

        $contents = new Contents();
        $result = $contents->update($obj);

        $this->assertTrue($result['success']);

        AbstractCollection::disableUserFilter(true);
    }

    /**
     * Test the verification of the configuration of a field
     *
     * Case with bad values, should return false
     *
     * The body field contain an url and only allow email
     */
    public function testUpdateWithBadConfigurationForEmailVType()
    {
        $this->mockCurrentUserService = $this->getMock('Rubedo\User\CurrentUser');
        Manager::setMockService('CurrentUser', $this->mockCurrentUserService);
        $this->mockCurrentUserService->expects($this->any())
            ->method("getWriteWorkspaces")
            ->will($this->returnValue(array(
                "test"
            )));
        $this->mockCurrentUserService->expects($this->any())
            ->method("getReadWorkspaces")
            ->will($this->returnValue(array(
                "test"
            )));
        $this->mockContentTypesService->expects($this->any())
            ->method('findById')
            ->will($this->returnValue(array(
                'fields' => array(
                    array(
                        'cType' => 'text',
                        'config' => array(
                            'name' => 'text',
                            'allowBlank' => false,
                            'multivalued' => false
                        )
                    ),
                    array(
                        'cType' => 'summary',
                        'config' => array(
                            'name' => 'summary',
                            'allowBlank' => true,
                            'multivalued' => false
                        )
                    ),
                    array(
                        'cType' => 'body',
                        'config' => array(
                            'name' => 'body',
                            'allowBlank' => true,
                            'multivalued' => false,
                            'vtype' => 'email'
                        )
                    )
                ),
                "workspaces" => array(
                    "test"
                )
            )));

        $obj = array(
            "id" => "test",
            "typeId" => "50c0c8669a199d930f000001",
            "fields" => array(
                'text' => 'test',
                'summary' => 'test',
                'body' => 'http://test.fr'
            ),
            "text" => "test",
            "nativeLanguage" => "en",
            "i18n" => array(
                "en" => array(
                    "fields" => array(
                        'text' => 'test',
                        'summary' => 'test',
                        'body' => 'http://test.fr'
                    )
                )
            ),
            "target" => array(
                "test"
            ),
            "writeWorkspace" => "test"
        );

        $this->mockWorkflowDataAccessService->expects($this->any())
            ->method('update')
            ->will($this->returnValue(array(
                'success' => true,
                'data' => array(
                    'status' => 'test',
                    "typeId" => "50c0c8669a199d930f000001",
                    "id" => "id"
                )
            )));

        $contents = new Contents();
        $result = $contents->update($obj);

        $this->assertFalse($result['success']);
    }

    /**
     * Test if the destroy method works fine
     */
    public function testDestroy()
    {
        $this->mockWorkflowDataAccessService->expects($this->once())
            ->method('destroy')
            ->will($this->returnValue(array(
                "success" => true
            )));
        $this->mockDataIndexService->expects($this->once())
            ->method('deleteContent');

        $obj = array(
            "id" => "test",
            "typeId" => "50c0c8669a199d930f000001",
            "fields" => array(
                'text' => 'test',
                'summary' => 'test',
                'body' => 'http://test.fr'
            ),
            "text" => "test"
        );

        $contents = new Contents();
        AbstractCollection::disableUserFilter(false);
        $result = $contents->destroy($obj);
        AbstractCollection::disableUserFilter(true);

        $this->assertTrue($result['success']);
    }

    /**
     * Test destroy on a read only content
     *
     * @expectedException \Rubedo\Exceptions\Access
     */
    public function testDestroyInReadOnly()
    {
        $this->mockWorkflowDataAccessService->expects($this->any())
            ->method('findById')
            ->will($this->returnValue(array(
                "readOnly" => true,
                "typeId" => "test"
            )));

        $obj = array(
            "id" => "test",
            "typeId" => "50c0c8669a199d930f000001",
            "fields" => array(
                'text' => 'test',
                'summary' => 'test',
                'body' => 'http://test.fr'
            ),
            "text" => "test"
        );

        $contents = new \Rubedo\Collection\Contents();
        AbstractCollection::disableUserFilter(false);
        $result = $contents->destroy($obj);
        AbstractCollection::disableUserFilter(true);

        $this->assertTrue($result['success']);
    }

    /**
     * Check if rights are disabled
     */
    public function testAnyFunctionWithoutRights()
    {
        AbstractCollection::disableUserFilter(false);

        $contents = new Contents();
        $contents->getList();

        $this->assertFalse(AbstractCollection::isUserFilterDisabled());
    }

    /**
     * Check if we are in front end mode
     */
    public function testAnyFunctionInFrontEndMode()
    {
        $contents = new Contents();
        $contents->setIsFrontEnd(true);

        $contents->getList();

        $this->assertTrue($contents->getIsFrontEnd());
    }

    /**
     * Check if we are in front end mode with draft status on contents
     */
    public function testAnyFunctionInFrontEndModeAndDraftStatus()
    {
        $contents = new Contents();
        $contents->setIsFrontEnd(true);
        Context::setIsDraft();

        $contents->getList();

        $this->assertTrue($contents->getIsFrontEnd());
        $this->assertTrue($contents->getIsFrontEnd(Context::isDraft()));

        Context::setIsDraft(false);
    }

    /**
     * Test getOnlineList function
     */
    public function testGetOnlineList()
    {
        $this->mockWorkflowDataAccessService->expects($this->once())
            ->method('read')
            ->will($this->returnValue(array(
                "typeId" => "test",
                'data' => array(
                    'status' => 'testb',
                    "typeId" => "50c0c8669a199d930f000001",
                    "id" => "id"
                ),
                "count" => 1
            )));

        $contents = new Contents();
        $result = $contents->getOnlineList();

        $this->assertTrue(is_array($result["data"]));
    }

    /**
     * Test getOnlineList function with draft status
     */
    public function testGetOnlineListWithDraftStatus()
    {
        $this->mockWorkflowDataAccessService->expects($this->once())
            ->method('read')
            ->will($this->returnValue(array(
                'data' => array(
                    "typeId" => "test"
                ),
                "count" => 1
            )));
        Context::setIsDraft();

        $contents = new Contents();
        $result = $contents->getOnlineList();

        $this->assertTrue(is_array($result["data"]));
        Context::setIsDraft(false);
    }

    /**
     * Try to unset a term
     */
    public function testUnsetTerms()
    {
        $this->mockWorkflowDataAccessService->expects($this->once())
            ->method('customUpdate')
            ->will($this->returnValue(array(
                'success' => true
            )));

        $contents = new Contents();
        $result = $contents->unsetTerms("test", "test");

        $this->assertTrue($result["success"]);
    }

    /**
     * Try to unset a term without a term id
     *
     * @expectedException \Rubedo\Exceptions\Server
     */
    public function testUnsetTermsWithoutTermId()
    {
        $contents = new Contents();
        $contents->unsetTerms("test", null);
    }
}
