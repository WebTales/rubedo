<?php

namespace RubedoAPITest\Exceptions;

use RubedoAPI\Exceptions\APIAbstractException;

class ExtendedAPIAbstractException extends APIAbstractException {}

class APIAbstractExceptionTest extends \PHPUnit_Framework_TestCase {

    public function testConstruct()
    {
        $exception = new ExtendedAPIAbstractException('Foo', 500, true);
        $this->assertEquals('Foo', $exception->getMessage());
        $this->assertEquals(500, $exception->getHttpCode());
        $this->assertEquals(true, $exception->isTransparent());
    }
} 