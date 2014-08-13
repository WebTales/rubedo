<?php

namespace RubedoAPI\Exceptions;

abstract class APIAbstractException extends \Exception
{
    protected $transparent;
    protected $httpCode;
    function __construct($message, $httpCode, $transparent = false)
    {
        parent::__construct($message);
        $this->setHttpCode($httpCode);
        $this->transparent = $transparent;
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

    public function isTransparent()
    {
        return (bool) $this->transparent;
    }
}
