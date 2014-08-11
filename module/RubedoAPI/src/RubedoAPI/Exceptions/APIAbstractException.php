<?php

namespace RubedoAPI\Exceptions;

abstract class APIAbstractException extends \Exception
{
    private $httpCode;
    function __construct($message, $httpCode)
    {
        parent::__construct($message);
        $this->setHttpCode($httpCode);
    }

    /**
     * @param mixed $httpCode
     */
    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
    }

    /**
     * @return mixed
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

}
