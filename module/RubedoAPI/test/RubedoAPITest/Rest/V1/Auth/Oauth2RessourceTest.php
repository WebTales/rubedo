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

namespace RubedoAPITest\Rest\V1\Auth;

use Rubedo\Services\Manager;
use RubedoAPI\Rest\V1\Auth\Oauth2Ressource;

class Oauth2RessourceTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \RubedoAPI\Rest\V1\Auth\Oauth2Ressource
     */
    protected $ressource;

    function setUp()
    {
        $this->ressource = new Oauth2Ressource();
        parent::setUp();
    }

    function tearDown()
    {
        Manager::resetMocks();
        parent::tearDown();
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIRequestException
     */
    public function testDefinition()
    {
        $this->assertNotNull($this->ressource->getDefinition()->getVerb('get'));
    }
}