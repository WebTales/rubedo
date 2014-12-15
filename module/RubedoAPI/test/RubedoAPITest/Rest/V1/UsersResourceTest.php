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
use RubedoAPI\Rest\V1\UsersResource;

class UsersResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \RubedoAPI\Rest\V1\UsersResource
     */
    protected $resource;
    protected $users;
    protected $userTypes;
    protected $currentUser;

    function setUp()
    {
        $this->resource = new UsersResource();
        $this->users = $this->getMock('Rubedo\Collection\Users');
        Manager::setMockService('Users', $this->users);
        $this->userTypes = $this->getMock('Rubedo\Collection\UserTypes');
        Manager::setMockService('UserTypes', $this->userTypes);
        $this->currentUser = $this->getMock('RubedoAPI\Services\User\CurrentUser');
        Manager::setMockService('API\Services\CurrentUser', $this->currentUser);
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
        $this->assertNotNull($this->resource->getEntityDefinition()->getVerb('get'));
        $this->assertNotNull($this->resource->getEntityDefinition()->getVerb('patch'));
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIEntityException
     */
    public function testPostTypeNotExist()
    {
        $this->resource->postAction(array(
            'usertype' => new \MongoId()
        ));
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIEntityException
     */
    public function testPostWithFailSetPassword()
    {
        $this->userTypes
            ->expects($this->once())
            ->method('findById')
            ->will(
                $this->returnValue(
                    array(
                        'id' => new \MongoId(),
                        'defaultGroup' => new \MongoId(),
                        'fields' => array(),
                        'signUpType' => 'open',
                    )
                )
            );
        $this->users
            ->expects($this->once())
            ->method('create')
            ->will(
                $this->returnValue(
                    array(
                        'success' => true,
                        'data' => array(
                            'version' => 1,
                            'id' => new \MongoId(),
                        ),
                    )
                )
            );
        $this->users
            ->expects($this->once())
            ->method('changePassword')
            ->will(
                $this->returnValue(false)
            );
        $this->resource->postAction(array(
            'usertype' => new \MongoId(),
            'fields' => array(
                'password' => 'foo',
                'login' => 'bar',
                'email' => 'email@test.fr',
                'name' => 'foobar'
            ),
        ));
    }

    public function testPost()
    {
        $this->userTypes
            ->expects($this->once())
            ->method('findById')
            ->will(
                $this->returnValue(
                    array(
                        'id' => new \MongoId(),
                        'defaultGroup' => new \MongoId(),
                        'fields' => array(),
                        'signUpType' => 'open',
                    )
                )
            );
        $this->users
            ->expects($this->once())
            ->method('create')
            ->will(
                $this->returnValue(
                    array(
                        'success' => true,
                        'data' => array(
                            'version' => 1,
                            'id' => new \MongoId(),
                        ),
                        'msg' => 'Foobar',
                    )
                )
            );
        $this->users
            ->expects($this->once())
            ->method('changePassword')
            ->will(
                $this->returnValue(true)
            );

        $result = $this->resource->postAction(array(
            'usertype' => new \MongoId(),
            'fields' => array(
                'password' => 'foo',
                'login' => 'bar',
                'email' => 'email@test.fr',
                'name' => 'foobar'
            ),
        ));
        $this->assertArrayHasKey('success', $result);
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIEntityException
     */
    public function testGetEntityWithEmptyUser()
    {
        $this->users
            ->expects($this->once())
            ->method('findById')
            ->will(
                $this->returnValue(array())
            );
        $result = $this->resource->getEntityAction(new \MongoId(), array());
        $this->assertArrayHasKey('success', $result);
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIEntityException
     */
    public function testGetEntityWithEmptyUsertype()
    {
        $this->users
            ->expects($this->once())
            ->method('findById')
            ->will(
                $this->returnValue(
                    array(
                        'fields' => array(),
                        'name' => 'foo',
                        'email' => 'foo@bar.com',
                        'typeId' => new \MongoId(),
                    )
                )
            );
        $this->userTypes
            ->expects($this->once())
            ->method('findById')
            ->will(
                $this->returnValue(
                    array()
                )
            );
        $this->resource->getEntityAction(new \MongoId(), array());
    }

    public function testGetEntity()
    {
        $this->users
            ->expects($this->once())
            ->method('findById')
            ->will(
                $this->returnValue(
                    array(
                        'fields' => array(),
                        'name' => 'foo',
                        'email' => 'foo@bar.com',
                        'typeId' => new \MongoId(),
                    )
                )
            );
        $this->userTypes
            ->expects($this->once())
            ->method('findById')
            ->will(
                $this->returnValue(
                    array(
                        'id' => new \MongoId(),
                    )
                )
            );
        $result = $this->resource->getEntityAction(new \MongoId(), array());
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('user', $result);
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIEntityException
     */
    public function testPatchEntityFailGetUser()
    {
        $this->users
            ->expects($this->once())
            ->method('findById')
            ->will(
                $this->returnValue(
                    array()
                )
            );
        $this->resource->patchEntityAction(new \MongoId(), array());
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIEntityException
     */
    public function testPatchEntityFailGetUsertype()
    {
        $this->users
            ->expects($this->once())
            ->method('findById')
            ->will(
                $this->returnValue(
                    array(
                        'fields' => array(),
                        'name' => 'foo',
                        'email' => 'foo@bar.com',
                        'typeId' => new \MongoId(),
                    )
                )
            );
        $this->userTypes
            ->expects($this->once())
            ->method('findById')
            ->will(
                $this->returnValue(
                    array()
                )
            );
        $this->resource->patchEntityAction(new \MongoId(), array());
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIAuthException
     */
    public function testPatchEntityBadUser()
    {
        $this->users
            ->expects($this->once())
            ->method('findById')
            ->will(
                $this->returnValue(
                    array(
                        'fields' => array(),
                        'name' => 'foo',
                        'email' => 'foo@bar.com',
                        'typeId' => new \MongoId(),
                        'id' => 'IdUser',
                    )
                )
            );
        $this->userTypes
            ->expects($this->once())
            ->method('findById')
            ->will(
                $this->returnValue(
                    array(
                        'id' => new \MongoId(),
                    )
                )
            );
        $this->currentUser
            ->expects($this->once())
            ->method('getCurrentUser')
            ->will(
                $this->returnValue(
                    array(
                        'id' => 'IdCurrentUser',
                    )
                )
            );
        $this->resource->patchEntityAction(new \MongoId(), array());
    }

    public function testPatchEntity()
    {
        $this->users
            ->expects($this->once())
            ->method('findById')
            ->will(
                $this->returnValue(
                    array(
                        'fields' => array(),
                        'name' => 'foo',
                        'email' => 'foo@bar.com',
                        'typeId' => new \MongoId(),
                        'id' => 'IdCurrentUser',
                    )
                )
            );

        $this->userTypes
            ->expects($this->once())
            ->method('findById')
            ->will(
                $this->returnValue(
                    array(
                        'id' => new \MongoId(),
                        'fields' => array(),
                    )
                )
            );

        $this->currentUser
            ->expects($this->once())
            ->method('getCurrentUser')
            ->will(
                $this->returnValue(
                    array(
                        'id' => 'IdCurrentUser',
                    )
                )
            );

        $this->users
            ->expects($this->once())
            ->method('update')
            ->will(
                $this->returnValue(
                    array(
                        'success' => true,
                        'data' => array(
                            'version' => 1,
                            'id' => new \MongoId(),
                        ),
                        'msg' => 'Foobar',
                    )
                )
            );

        $this->users
            ->expects($this->any())
            ->method('changePassword')
            ->will(
                $this->returnValue(true)
            );

        $result = $this->resource->patchEntityAction(new \MongoId(), array(
            'user' => array(
                'fields' => array(
                    'foo' => 'bar',
                ),
            ),
        ));
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('data', $result);
    }
}