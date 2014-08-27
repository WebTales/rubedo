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
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace RubedoAPITest\Rest\V1\Ecommerce;


use Rubedo\Services\Manager;
use RubedoAPI\Entities\API\Identity;
use RubedoAPI\Rest\V1\Ecommerce\ShippersRessource;

class ShippersRessourceTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \RubedoAPI\Rest\V1\Ecommerce\ShippersRessource
     */
    protected $ressource;
    protected $mockShoppingCart;
    protected $mockShippers;
    protected $mockCurrentUser;

    function setUp()
    {
        $this->ressource = new ShippersRessource();
        $this->mockShoppingCart = $this->getMock('Rubedo\Collection\ShoppingCart');
        $this->mockShippers = $this->getMock('Rubedo\Collection\Shippers');
        $this->mockCurrentUser = $this->getMock('RubedoAPI\Services\User\CurrentUser');
        $this->mockCurrentUser
            ->expects($this->any())
            ->method('getCurrentUser')
            ->will(
                $this->returnValue(
                    array(
                        'shippingAddress' => array(
                            'country' => 'FooCountry',
                        ),
                    )
                )
            );
        Manager::setMockService('ShoppingCart', $this->mockShoppingCart);
        Manager::setMockService('Shippers', $this->mockShippers);
        Manager::setMockService('API\Services\CurrentUser', $this->mockCurrentUser);
        parent::setUp();
    }

    function tearDown()
    {
        Manager::resetMocks();
        parent::tearDown();
    }

    public function testDefinition()
    {
        $this->assertTrue($this->ressource->getDefinition()->getVerb('get')->hasIdentityRequired());
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIEntityException
     */
    public function testGetActionNotCountry()
    {
        $this->mockShoppingCart
            ->expects($this->once())
            ->method('getCurrentCart')
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'amount' => 2,
                        ),
                        array(
                            'amount' => 1,
                        ),
                    )
                )
            );
        $this->mockShippers
            ->expects($this->once())
            ->method('getApplicableShippers')
            ->will($this->returnValue(array()));
        $params = array();
        $params['identity'] = new Identity('access_token');
        $this->ressource->getAction($params);
    }

    public function testGetAction()
    {
        $this->mockShoppingCart
            ->expects($this->once())
            ->method('getCurrentCart')
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'amount' => 2,
                        ),
                        array(
                            'amount' => 1,
                        ),
                    )
                )
            );
        $this->mockShippers
            ->expects($this->once())
            ->method('getApplicableShippers')
            ->will(
                $this->returnValue(
                    array(
                        'foo' => 'bar',
                    )
                )
            );
        $params = array();
        $params['identity'] = new Identity('access_token');
        $this->ressource->getAction($params);
    }

} 