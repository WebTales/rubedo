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

namespace RubedoAPI\Entities\API\Definition;


use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Exceptions\APIRequestException;
use Zend\Stdlib\JsonSerializable;

/**
 * Class DefinitionEntity
 * @package RubedoAPI\Entities\API\Definition
 */
class DefinitionEntity implements JsonSerializable
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var array
     */
    protected $verbList = [];

    /**
     * Get description
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param mixed $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get verbs for this definition, serialized
     *
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
     * Edit a verb, with a closure
     *
     * @param $verb
     * @param $function
     * @return $this
     * @internal param $array
     * @internal param array $verbList
     */
    public function editVerb($verb, $function)
    {
        $verb = strtoupper($verb);
        if (!array_key_exists($verb, $this->verbList)) {
            $this->verbList[$verb] = new VerbDefinitionEntity($verb);
        }
        $function($this->verbList[$verb]);
        return $this;
    }

    /**
     * Get a verb definition
     *
     * @param $verb
     * @return mixed
     * @throws \RubedoAPI\Exceptions\APIRequestException
     */
    public function getVerb($verb)
    {
        $verb = strtoupper($verb);
        if (!array_key_exists($verb, $this->verbList)) {
            throw new APIRequestException('Verb undefined', 405);
        }
        return $this->verbList[$verb];
    }

    /**
     * Return jsonserializable array
     *
     * @return array
     */
    function jsonSerialize()
    {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'verbs' => $this->getVerbsSerialized(),
        ];
    }
} 