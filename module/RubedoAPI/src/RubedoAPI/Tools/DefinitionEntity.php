<?php
/**
 * Created by IntelliJ IDEA.
 * User: nainterceptor
 * Date: 06/08/14
 * Time: 16:34
 */

namespace RubedoAPI\Tools;


use RubedoAPI\Exceptions\APIEntityException;
use Zend\Stdlib\JsonSerializable;

class DefinitionEntity implements JsonSerializable{
    protected $name;
    protected $description;
    protected $verbList = [];

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @throws \RubedoAPI\Exceptions\APIEntityException
     * @return array
     */
    protected function getVerbsSerialized()
    {
        $verbs = [];
        foreach ($this->verbList as $key => $value) {
            if (!$value instanceof VerbDefinitionEntity)
                throw new APIEntityException('Verbs in Definition must be VerbDefinitionEntity', 500);
            $verbs[$key] = $value->jsonSerialize();
        }
        return $verbs;
    }

    /**
     * @param $array
     * @internal param array $verbList
     */
    public function editVerb($verb, $function)
    {
        $verb = strtoupper($verb);
        if(!array_key_exists($verb, $this->verbList)) {
            $this->verbList[$verb] = (new VerbDefinitionEntity())->setVerb($verb);
        }
        $function($this->verbList[$verb]);
        return $this;
    }

    function jsonSerialize() {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'verbs' => $this->getVerbsSerialized(),
        ];
    }
} 