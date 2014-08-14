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

namespace RubedoAPI\Tools;


use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Exceptions\APIRequestException;
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
     * @param $verb
     * @param $function
     * @return $this
     * @internal param $array
     * @internal param array $verbList
     */
    public function editVerb($verb, $function)
    {
        $verb = strtoupper($verb);
        if(!array_key_exists($verb, $this->verbList)) {
            $this->verbList[$verb] = new VerbDefinitionEntity($verb) ;
        }
        $function($this->verbList[$verb]);
        return $this;
    }

    public function getVerb($verb)
    {
        $verb = strtoupper($verb);
        if(!array_key_exists($verb, $this->verbList)) {
            throw new APIRequestException('Verb undefined', 405);
        }
        return $this->verbList[$verb];
    }

    function jsonSerialize() {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'verbs' => $this->getVerbsSerialized(),
        ];
    }
} 