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

namespace RubedoAPITest\Rest\V1\Auth\Oauth2;

use Rubedo\Services\Manager;
use RubedoAPI\Frontoffice\Controller\ApiController;
use RubedoAPI\Rest\V1\Auth\Oauth2\GenerateResource;

if (!class_exists('RubedoAPITest\Rest\V1\Auth\Oauth2\TrustAPIController')) {
    class TrustAPIController extends ApiController
    {
        function params()
        {
            return false;
        }

        function forward()
        {
            return true;
        }
    }
}

if (!class_exists('RubedoAPITest\Rest\V1\Auth\Oauth2\Forward')) {
    class Forward extends \Zend\Mvc\Controller\Plugin\Forward
    {
        public function __construct()
        {
        }
    }
}

class GenerateResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \RubedoAPI\Rest\V1\Auth\Oauth2\GenerateResource
     */
    protected $resource;
    protected $mockAuthAPI;
    protected $mockApplication;
    protected $request;
    protected $mockCurrentUser;
    protected $mockUsersCollection;
    protected $forwardResult;
    protected $mockAPIController;
    protected $params;
    protected $dispatchResult;

    function setUp()
    {
        $this->resource = new GenerateResource();

        $this->mockAuthAPI = $this->getMock('RubedoAPI\Services\Security\Authentication');
        $this->mockCurrentUser = $this->getMock('RubedoAPI\Services\User\CurrentUser');
        $this->mockUsersCollection = $this->getMock('Rubedo\Collection\Users');
        $this->forwardResult = $this->getMock('RubedoAPITest\Rest\V1\Auth\Oauth2\Forward');
        $this->mockAPIController = $this->getMock('RubedoAPITest\Rest\V1\Auth\Oauth2\TrustAPIController');
        $this->params = $this->getMock('Zend\Mvc\Controller\Plugin\Params');
        $this->dispatchResult = $this->getMock('Zend\View\Model\JsonModel');

        Manager::setMockService('API\\Services\\Auth', $this->mockAuthAPI);
        Manager::setMockService('API\Services\CurrentUser', $this->mockCurrentUser);
        Manager::setMockService('Users', $this->mockUsersCollection);

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

    public function testPostAction()
    {

        $this->dispatchResult
            ->expects($this->once())
            ->method('getVariables')
            ->will($this->returnValue(array(
                'rights' => array('myRights'),
                'currentUser' => "toto"
            )));
        $this->forwardResult
            ->expects($this->once())
            ->method('dispatch')
            ->will($this->returnValue($this->dispatchResult));
        $this->params
            ->expects($this->once())
            ->method('fromRoute')
            ->will($this->returnValue(array()));
        $this->mockAPIController
            ->expects($this->once())
            ->method('params')
            ->will($this->returnValue($this->params));
        $this->mockAPIController
            ->expects($this->once())
            ->method('forward')
            ->will($this->returnValue($this->forwardResult));
        $this->mockAuthAPI
            ->expects($this->once())
            ->method('APIAuth')
            ->will($this->returnValue(array(
                'token' => array('access_token' => 'myAmazingToken'),
                'user' => array(
                    'id' => 'MongoId',
                ),
            )));
        $this->mockCurrentUser
            ->expects($this->any())
            ->method('setAccessToken');

        $this->mockUsersCollection
            ->expects($this->once())
            ->method('findById')
            ->will($this->returnValue(array()));

        $this->resource->setContext($this->mockAPIController);
        $postResult = $this->resource->postAction(array(
            'PHP_AUTH_USER' => 'foo',
            'PHP_AUTH_PW' => 'bar',
        ));
        $this->assertArrayHasKey('success', $postResult);
    }
}