<?php
namespace RubedoAPI\V1\Rest\ApplicationLog;

class ApplicationLogEntity
{
    private $id;
    private $foo;

    function __construct ($array = array()) {
        foreach ($array as $key => $value)
        {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
