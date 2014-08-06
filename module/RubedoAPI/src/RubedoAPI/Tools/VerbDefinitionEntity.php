<?php
/**
 * Created by IntelliJ IDEA.
 * User: nainterceptor
 * Date: 06/08/14
 * Time: 17:16
 */

namespace RubedoAPI\Tools;


use Zend\Stdlib\JsonSerializable;

class VerbDefinitionEntity implements JsonSerializable{
    protected $verb;
    protected $description;

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVerb()
    {
        return $this->verb;
    }

    /**
     * @param mixed $verb
     * @return $this
     */
    public function setVerb($verb)
    {
        $this->verb = $verb;
        return $this;
    }

    function jsonSerialize() {
        return [
            'verb' => $this->getVerb(),
            'description' => $this->getDescription(),
        ];
    }
}