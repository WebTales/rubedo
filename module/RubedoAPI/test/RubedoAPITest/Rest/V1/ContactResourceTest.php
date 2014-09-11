<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace RubedoAPITest\Rest\V1;

use Rubedo\Services\Manager;
use RubedoAPI\Rest\V1\ContactResource;

class SwiftObjectToMock extends \Swift_Message
{
    function __construct()
    {

    }
}

class ContactResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \RubedoAPI\Rest\V1\ContactResource
     */
    protected $resource;
    protected $mockMailingList;
    protected $mockMailer;
    protected $mockMailerObject;

    function setUp()
    {
        $this->resource = new ContactResource();
        $this->mockMailingList = $this->getMock('Rubedo\Collection\MailingList');
        $this->mockMailer = $this->getMock('Rubedo\Mail\Mailer');
        $this->mockMailerObject = $this->getMock('RubedoAPITest\Rest\V1\SwiftObjectToMock');
        Manager::setMockService('MailingList', $this->mockMailingList);
        Manager::setMockService('Mailer', $this->mockMailer);
        parent::setUp();
    }

    function tearDown()
    {
        Manager::resetMocks();
        parent::tearDown();
    }

    public function testDefinition()
    {
        $this->assertNotNull($this->resource->getDefinition()->getVerb('post'));
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIControllerException
     */
    public function testPostActionMailingListEmpty()
    {
        $this->mockMailingList
            ->expects($this->once())
            ->method('findById')
            ->will($this->returnValue(array()));
        $this->resource->postAction(
            array(
                'mailingListId' => 'mailingListId',
            )
        );
    }

    public function testPostAction()
    {
        $this->mockMailerObject
            ->expects($this->once())
            ->method('setTo')
            ->will(
                $this->returnValue(null)
            );
        $this->mockMailerObject
            ->expects($this->once())
            ->method('setFrom')
            ->will(
                $this->returnValue(null)
            );
        $this->mockMailerObject
            ->expects($this->once())
            ->method('setSubject')
            ->will(
                $this->returnValue(null)
            );
        $this->mockMailerObject
            ->expects($this->once())
            ->method('setBody')
            ->will(
                $this->returnValue(null)
            );
        $this->mockMailer
            ->expects($this->once())
            ->method('getNewMessage')
            ->will(
                $this->returnValue(
                    $this->mockMailerObject
                )
            );
        $this->mockMailingList
            ->expects($this->once())
            ->method('findById')
            ->will(
                $this->returnValue(
                    array(
                        'replyToAddress' => 'foo@localhost.localdomain',
                    )
                )
            );
        $this->mockMailer
            ->expects($this->once())
            ->method('sendMessage')
            ->will(
                $this->returnValue(true)
            );
        $postResult = $this->resource->postAction(
            array(
                'mailingListId' => 'mailingListId',
                'from' => 'From',
                'subject' => 'Subject',
                'fields' => array(
                    'toto' => 'Toto',
                ),
            )
        );
        $this->assertArrayHasKey('success', $postResult);
    }
} 