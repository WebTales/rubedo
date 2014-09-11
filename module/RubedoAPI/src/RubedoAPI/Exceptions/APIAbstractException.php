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

namespace RubedoAPI\Exceptions;

/**
 * Abstract class APIAbstractException
 * @package RubedoAPI\Exceptions
 */
abstract class APIAbstractException extends \Exception
{
    /**
     * Define if the exception must be return directly
     *
     * @var bool
     */
    protected $transparent;
    /**
     * HTTP Code to return
     *
     * @var int
     */
    protected $httpCode;

    /**
     * Construct the error
     *
     * @param string $message Error label
     * @param int $httpCode
     * @param bool $transparent
     */
    function __construct($message, $httpCode = 500, $transparent = false)
    {
        parent::__construct($message);
        $this->setHttpCode($httpCode);
        $this->transparent = $transparent;
    }

    /**
     * Set HTTP Code
     *
     * @param mixed $httpCode
     */
    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
    }

    /**
     * Return HTTP Code
     *
     * @return mixed
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Return true if this error must be directly return
     *
     * @return bool
     */
    public function isTransparent()
    {
        return (bool)$this->transparent;
    }
}
