<?php
namespace RubedoAPI\V1\Rest\ApplicationLog;

class ApplicationLogResourceFactory
{
    public function __invoke($services)
    {
        return new ApplicationLogResource();
    }
}
