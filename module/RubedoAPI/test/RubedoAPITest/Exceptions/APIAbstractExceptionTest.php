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

namespace RubedoAPITest\Exceptions;

use RubedoAPI\Exceptions\APIAbstractException;

class ExtendedAPIAbstractException extends APIAbstractException
{
}

class APIAbstractExceptionTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $exception = new ExtendedAPIAbstractException('Foo', 500, true);
        $this->assertEquals('Foo', $exception->getMessage());
        $this->assertEquals(500, $exception->getHttpCode());
        $this->assertEquals(true, $exception->isTransparent());
    }
} 